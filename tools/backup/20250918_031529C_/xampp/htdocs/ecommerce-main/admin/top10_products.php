<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once '../components/_base.php';
auth('Admin');

// Initialize filter variables with default values
$reportType = post('reportType', 'overall');
$year = post('year', date('Y'));
$month = post('month', date('m'));
$season = post('season', '');
$startDate = post('startDate', date('Y-m-d'));
$endDate = post('endDate', date('Y-m-d'));
$category = post('category', 'all');

// Initialize $params as an empty array
$params = [];

// Define your base query for fetching top products
$filterQuery = "WHERE o.status = 'completed'";

// Map for seasons
$seasonMap = [
    'Q1' => '1,2,3',
    'Q2' => '4,5,6',
    'Q3' => '7,8,9',
    'Q4' => '10,11,12'
];

if($year == null){
    $_err['year'] = 'Required';
}
if($month == null){
    $_err['month'] = 'Required';
}
if($startDate == null){
    $_err['startDate'] = 'Required';
}
if($endDate == $startDate){
    $_err['endDate'] = 'Start Date and end Date must be different';
}
if($endDate == null){
    $_err['endDate'] = 'Required';
}
if($endDate < $startDate){
    $_err['endDate'] = 'End Date must be bigger than start date';
}

// Initialize the chart title
$chartTitle = 'Top 10 Products Report';

// Set the chart title based on the report type and filters
if ($reportType == 'yearly') {
    $chartTitle .= " for the Year $year";
} elseif ($reportType == 'monthly') {
    $chartTitle .= " for $year - Month $month";
} elseif ($reportType == 'seasonal' && isset($seasonMap[$season])) {
    $chartTitle .= " for $year - Season $season";
} elseif ($reportType == 'daily') {
    $chartTitle .= " for " . date('F j, Y', strtotime($startDate));
} elseif ($reportType == 'custom') {
    $chartTitle .= " from " . date('F j, Y', strtotime($startDate)) . " to " . date('F j, Y', strtotime($endDate));
}



// Add additional filters based on report type
if ($reportType == 'yearly') {
    $filterQuery .= " AND YEAR(o.date) = ?";
    $params[] = $year;
} elseif ($reportType == 'monthly') {
    $filterQuery .= " AND YEAR(o.date) = ? AND MONTH(o.date) = ?";
    $params[] = $year;
    $params[] = $month;
} elseif ($reportType == 'seasonal' && isset($seasonMap[$season])) {
    $filterQuery .= " AND YEAR(o.date) = ? AND MONTH(o.date) IN ({$seasonMap[$season]})";
    $params[] = $year;
} elseif ($reportType == 'daily' ) {
    $filterQuery .= "AND o.date = ?";
    $params[] = $startDate;
}elseif ($reportType == 'custom') {
    $filterQuery .= " AND o.date BETWEEN ? AND ?";
    $params[] = $startDate;

    // Automatically set endDate to one day after startDate for daily reports
    if ($reportType == 'daily' && empty($endDate)) {
        $endDate = date('Y-m-d', strtotime($startDate . ' +1 day'));
    }
    $params[] = $endDate; // Add end date to params
}

// Add category filter
if ($category != 'all') {
    $filterQuery .= " AND p.category_id = ?";
    $params[] = $category; // Add category ID to params
}

// Fetch the top 10 best-selling products by quantity
$query = "
    SELECT p.name, c.name as category, SUM(op.quantity) as total_quantity
    FROM orderproduct op
    JOIN product p ON op.product_id = p.id
    JOIN category c ON p.category_id = c.id
    JOIN orders o ON op.ordersid = o.id
    $filterQuery
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 10
";
$stm = $_db->prepare($query);
$stm->execute($params);
$topProducts = $stm->fetchAll(PDO::FETCH_ASSOC);

// If there are no top products, fetch other products
if (empty($topProducts)) {
    $fallbackQuery = "
        SELECT p.name, c.name as category
        FROM product p
        JOIN category c ON p.category_id = c.id
        LIMIT 10
    ";
    $stmFallback = $_db->prepare($fallbackQuery);
    $stmFallback->execute([]);
    $fallbackProducts = $stmFallback->fetchAll(PDO::FETCH_ASSOC);
    
    // Format fallback products to match expected structure
    $topProducts = array_map(function($row) {
        return [
            'name' => $row['name'],
            'category' => $row['category'],
            'total_quantity' => 0 // Set total quantity to 0 for fallback
        ];
    }, $fallbackProducts);
}

// Ensure topProducts has exactly 10 entries
//$topProducts = array_pad($topProducts, 10, ['name' => 'Other Products', 'category' => '', 'total_quantity' => 0]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php $_title = "Top 10 Products Report";?>
    <title><?= $_title?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/report_table.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>
<h1 class="title">Top 10 reports Report</h1>

<!-- Filter Section -->
<section class="filtering">
    <form method="POST">
    <section class="reportType">
        <label for="reportType">Report Type:</label>
        <select id="reportType" name="reportType" onchange="this.form.submit()">
            <option value="overall" <?= $reportType == 'overall' ? 'selected' : '' ?>>Overall</option>
            <option value="yearly" <?= $reportType == 'yearly' ? 'selected' : '' ?>>Yearly</option>
            <option value="seasonal" <?= $reportType == 'seasonal' ? 'selected' : '' ?>>Seasonal</option>
            <option value="monthly" <?= $reportType == 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="daily" <?= $reportType == 'daily' ? 'selected' : '' ?>>Daily</option>
            <option value="custom" <?= $reportType == 'custom' ? 'selected' : '' ?>>Custom</option>
        </select>
    </section>
     

        <section class="CategorySelection">
        <!-- Conditional Filters Based on Report Type -->
        <?php if ($reportType == 'yearly' || $reportType == 'seasonal' || $reportType == 'monthly'): ?>
            <label for="year">Year:</label>
            <br>
            <?=html_number('year', 2000, date('Y'), '', 'placeholder="Year"');?>
            <br><?=err("year");?>
            <br>
        <?php endif; ?>
        
        <?php if ($reportType == 'seasonal'): ?>
            <br>
            <label for="season">Season:</label>
            <br>
            <?= html_select2('season', ['Q1' => 'Q1', 'Q2' => 'Q2', 'Q3' => 'Q3', 'Q4' => 'Q4'],null,'',''); ?>
            <br>
        <?php endif; ?>
      

        <?php if ($reportType == 'monthly'): ?>
            <br>
            <label for="month">Month:</label>
            <br>
            <?= html_number('month', 1, 12, '', 'placeholder="Month"'); ?>
            <br><?=err("month");?>
        <?php endif; ?>
       
        <?php if ($reportType == 'daily' || $reportType == 'custom'): ?>
            <label for="startDate">Start Date:</label>
           <?= html_date('startDate'); ?>
           <br><?=err("startDate");?>
           <br>
        <?php endif; ?>
      
        <?php if ($reportType == 'custom'): ?>
            <br>
            <label for="endDate">End Date:</label>
            <?= html_date('endDate'); ?>
            <br><?=err("endDate");?>
        <?php endif; ?>
     

        <!-- Category Filter -->
         <br>
         <br>
        <label for="category">Category:</label>
        <br>
        <select id="category" name="category" onchange="this.form.submit()">
            <option value="all" <?= $category == 'all' ? 'selected' : '' ?>>All Categories</option>
            <?php
                // Fetch all categories
                $stm = $_db->prepare('SELECT * FROM `category`');
                $stm->execute([]);
                $arr = $stm->fetchAll(PDO::FETCH_ASSOC);
                foreach ($arr as $c):
            ?>
                <option value="<?= htmlspecialchars($c['id']) ?>" <?= $category == $c["id"] ? 'selected' : '' ?>><?= htmlspecialchars($c["name"]) ?></option>
            <?php endforeach; ?>
        </select>
        </section>
   
        <button type="submit">Generate Report</button>
    </form>
</section>



<section class="chart">

<h2 class="chartTitle"><?= htmlspecialchars($chartTitle) ?></h2>

<!-- Products Table -->
<section class="products-table">
    <h2>Top Products Table</h2>
    <table>
        <thead>
            <tr>
                <th>Product Name (Category)</th>
                <th>Quantity Sold</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalQuantity = 0; // Initialize total quantity
            foreach ($topProducts as $product):
                $totalQuantity += $product['total_quantity']; // Sum quantities for total
            ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) . " (" . htmlspecialchars($product['category']) . ")" ?></td>
                    <td><?= htmlspecialchars($product['total_quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total</strong></td>
                <td><strong><?= $totalQuantity ?></strong></td>
            </tr>
        </tfoot>
    </table>
</section>

<!-- Chart Container -->
<canvas id="topProductsChart"></canvas>
</section>


<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data for Top Products Chart
const topProductsLabels = <?= json_encode(array_map(function($row) {
    return $row['name'] . " (" . $row['category'] . ")";
}, $topProducts)) ?>;
const topProductsData = <?= json_encode(array_column($topProducts, 'total_quantity')) ?>;

// Top Products Bar Chart
const ctx1 = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: topProductsLabels,
        datasets: [{
            label: 'Quantity Sold',
            data: topProductsData,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: { title: { display: true, text: 'Product (Category)' }},
            y: { beginAtZero: true, title: { display: true, text: 'Quantity Sold' }}
        }
    }
});
</script>


<script src="../js/admin_script.js"></script>
</body>
</html>
