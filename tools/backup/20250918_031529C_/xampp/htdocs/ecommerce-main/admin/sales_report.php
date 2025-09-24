<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include base.php for helper functions and database connection
include '../components/_base.php';
auth('Admin');

// Initialize report data variables
$reportType = post('reportType', 'overall');
$category = post('category', 'all');
$product = post('product', 'all');
$status = post('status', 'all');
$year = post('year', date('Y'));
$month = post('month', date('m'));
$season = post('season', 'Q1');
$startDate = post('startDate', date('Y-m-d'));
$endDate = post('endDate', date('Y-m-d'));

// Placeholder for chart data
$chartData = [];
$chartLabels = [];
$chartTitle = '';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = '';
    $params = [];

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

    // Define the query based on report type
    if ($reportType === 'overall') {
        // Get sales for the last 5 years
        $currentYear = date('Y');
        for ($i = 4; $i >= 0; $i--) {
            $year = $currentYear - $i;
            $chartLabels[] = $year; // Set x-axis to display actual years
            $chartData[$year] = 0;  // Initialize sales data for each year
        }

        // Fetch actual sales data for those years
        $sql = "SELECT YEAR(o.date) as year, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY YEAR(o.date)";
        $params = [':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with the actual sales
        foreach ($results as $row) {
            $chartData[$row['year']] = $row['total_sales'];
        }
        // Initialize category and product names
            $categoryName = 'all';
            $productName = 'all';

            // Fetch category and product for title
            if ($category != 'all') {
                // Fetch category
                $Categorystm = $_db->prepare('SELECT * FROM `category` WHERE id = ?');
                $Categorystm->execute([$category]);
                $arrCategory = $Categorystm->fetch(PDO::FETCH_ASSOC);
                
                // Check if category exists
                if ($arrCategory) {
                    $categoryName = $arrCategory['name'];
                }
            }

            if ($product != 'all') {
                // Fetch product
                $productstm = $_db->prepare('SELECT * FROM `product` WHERE id = ?');
                $productstm->execute([$product]);
                $arrproduct = $productstm->fetch(PDO::FETCH_ASSOC);
                
                // Check if product exists
                if ($arrproduct) {
                    $productName = $arrproduct['name'];
                }
            }

            // Construct the chart title
            $chartTitle = "Overall Total Sales Report for " . ucfirst($categoryName) . " ($productName)";


       
    } elseif ($reportType === 'yearly' && $year != null) {
        // Generate 12 months (1-12) and set sales to 0 initially
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = date('F', mktime(0, 0, 0, $i, 10)); // Get month name (January, February, etc.)
            $chartData[$i] = 0;
        }

        // Fetch sales data for each month of the selected year
        $sql = "SELECT MONTH(o.date) as month, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE YEAR(o.date) = :year
                AND (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY MONTH(o.date)";
        $params = [':year' => $year, ':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with actual sales
        foreach ($results as $row) {
            $chartData[$row['month']] = $row['total_sales'];
        }

              // Initialize category and product names
$categoryName = 'all';
$productName = 'all';

// Fetch category and product for title
if ($category != 'all') {
    // Fetch category
    $Categorystm = $_db->prepare('SELECT * FROM `category` WHERE id = ?');
    $Categorystm->execute([$category]);
    $arrCategory = $Categorystm->fetch(PDO::FETCH_ASSOC);
    
    // Check if category exists
    if ($arrCategory) {
        $categoryName = $arrCategory['name'];
    }
}

if ($product != 'all') {
    // Fetch product
    $productstm = $_db->prepare('SELECT * FROM `product` WHERE id = ?');
    $productstm->execute([$product]);
    $arrproduct = $productstm->fetch(PDO::FETCH_ASSOC);
    
    // Check if product exists
    if ($arrproduct) {
        $productName = $arrproduct['name'];
    }
}


        $chartTitle = "Yearly Total Sales Report for " . ucfirst($categoryName) . " ($productName) in Year $year";
    }
    /* elseif ($reportType === 'seasonal') {
        // Define month ranges for each quarter (Q1-Q4)
        $seasonal_ranges = [
            'Q1' => ['Jan', 'Feb', 'Mar'],
            'Q2' => ['Apr', 'May', 'Jun'],
            'Q3' => ['Jul', 'Aug', 'Sep'],
            'Q4' => ['Oct', 'Nov', 'Dec']
        ];

        // Set x-axis labels to the three months of the selected season
        $chartLabels = $seasonal_ranges[$season];
        foreach ($chartLabels as $month) {
            $chartData[$month] = 0; // Default sales value to 0 for each month
        }

        // Fetch sales data for the selected quarter
      
      $sql = "SELECT MONTHNAME(o.date) as month, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE YEAR(o.date) = :year
                AND MONTH(o.date) BETWEEN :startMonth AND :endMonth
                AND (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY MONTH(o.date)";
        $quarterMapping = ['Q1' => [1, 3], 'Q2' => [4, 6], 'Q3' => [7, 9], 'Q4' => [10, 12]];
        [$startMonth, $endMonth] = $quarterMapping[$season];
        $params = [':year' => $year, ':startMonth' => $startMonth, ':endMonth' => $endMonth, ':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with actual sales
        foreach ($results as $row) {
            $chartData[$row['month']] = $row['total_sales'];
        }


        $chartTitle = "Seasonal Sales Report for " . ucfirst($category) . " ($product) in $season $year";
    } */
        elseif ($reportType === 'monthly' && $year != null && $month!= null) {
        // Get the correct number of days in the selected month and year
        $year = post('year', date('Y'));
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabels[] = $i;
            $chartData[$i] = 0;
        }

        // Fetch sales data for each day of the selected month
        $sql = "SELECT DAY(o.date) as day, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE YEAR(o.date) = :year AND MONTH(o.date) = :month
                AND (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY DAY(o.date)";
        $params = [':year' => $year, ':month' => $month, ':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with actual sales
        foreach ($results as $row) {
            $chartData[$row['day']] = $row['total_sales'];
        }

        $categoryName = 'all';
$productName = 'all';

// Fetch category and product for title
if ($category != 'all') {
    // Fetch category
    $Categorystm = $_db->prepare('SELECT * FROM `category` WHERE id = ?');
    $Categorystm->execute([$category]);
    $arrCategory = $Categorystm->fetch(PDO::FETCH_ASSOC);
    
    // Check if category exists
    if ($arrCategory) {
        $categoryName = $arrCategory['name'];
    }
}

if ($product != 'all') {
    // Fetch product
    $productstm = $_db->prepare('SELECT * FROM `product` WHERE id = ?');
    $productstm->execute([$product]);
    $arrproduct = $productstm->fetch(PDO::FETCH_ASSOC);
    
    // Check if product exists
    if ($arrproduct) {
        $productName = $arrproduct['name'];
    }
}

        $chartTitle = "Monthly Sales Report for " . ucfirst($categoryName) . " ($productName) in $month/$year";
    } elseif ($reportType === 'daily' && $startDate!=null) {
        // Generate 24 hours (0-23) and set sales to 0 initially
        for ($i = 0; $i <= 23; $i++) {
            $chartLabels[] = $i . ":00";
            $chartData[$i] = 0;
        }

        // Fetch sales data for each hour of the selected day
        $sql = "SELECT HOUR(o.time) as hour, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE o.date = :date
                AND (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY HOUR(o.time)";
        $params = [':date' => $startDate, ':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with actual sales
        foreach ($results as $row) {
            $chartData[$row['hour']] = $row['total_sales'];
        }

        
        $categoryName = 'all';
$productName = 'all';

// Fetch category and product for title
if ($category != 'all') {
    // Fetch category
    $Categorystm = $_db->prepare('SELECT * FROM `category` WHERE id = ?');
    $Categorystm->execute([$category]);
    $arrCategory = $Categorystm->fetch(PDO::FETCH_ASSOC);
    
    // Check if category exists
    if ($arrCategory) {
        $categoryName = $arrCategory['name'];
    }
}

if ($product != 'all') {
    // Fetch product
    $productstm = $_db->prepare('SELECT * FROM `product` WHERE id = ?');
    $productstm->execute([$product]);
    $arrproduct = $productstm->fetch(PDO::FETCH_ASSOC);
    
    // Check if product exists
    if ($arrproduct) {
        $productName = $arrproduct['name'];
    }
}


        $chartTitle = "Daily Sales Report for " . ucfirst($categoryName) . " ($productName) on $startDate";
    } elseif ($reportType === 'custom' && $startDate!=null && $endDate!=null &&$startDate <=$endDate) {
        // Generate date range with 0 sales initially
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );
        foreach ($period as $date) {
            $chartLabels[] = $date->format('Y-m-d');
            $chartData[$date->format('Y-m-d')] = 0;
        }

        // Fetch sales data for the custom date range
        $sql = "SELECT o.date as date, SUM(o.total_price) as total_sales
                FROM orders o
                WHERE o.date BETWEEN :startDate AND :endDate
                AND (:category = 'all' OR o.id IN (SELECT op.ordersid FROM orderproduct op JOIN product p ON op.product_id = p.id WHERE p.category_id = :category))
                AND (:status = 'all' OR o.status = :status)
                GROUP BY o.date";
        $params = [':startDate' => $startDate, ':endDate' => $endDate, ':category' => $category, ':status' => $status];
        $stmt = $_db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update chartData with actual sales
        foreach ($results as $row) {
            $chartData[$row['date']] = $row['total_sales'];
        }

        
        
        // Initialize category and product names
$categoryName = 'all';
$productName = 'all';

// Fetch category and product for title
if ($category != 'all') {
    // Fetch category
    $Categorystm = $_db->prepare('SELECT * FROM `category` WHERE id = ?');
    $Categorystm->execute([$category]);
    $arrCategory = $Categorystm->fetch(PDO::FETCH_ASSOC);
    
    // Check if category exists
    if ($arrCategory) {
        $categoryName = $arrCategory['name'];
    }
}

if ($product != 'all') {
    // Fetch product
    $productstm = $_db->prepare('SELECT * FROM `product` WHERE id = ?');
    $productstm->execute([$product]);
    $arrproduct = $productstm->fetch(PDO::FETCH_ASSOC);
    
    // Check if product exists
    if ($arrproduct) {
        $productName = $arrproduct['name'];
    }
}

// Construct the chart title
$chartTitle = "Custom Sales Report for " . ucfirst($categoryName) . " ($productName) from $startDate to $endDate";

    }

    // Prepare data for the chart
    $chartLabels = array_values($chartLabels); // Ensure the order is correct
    $chartData = array_values($chartData);     // Convert associative array to indexed array


}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php $_title = "Sales Report";?>
    <title><?= $_title?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/report_table.css">
    <style>
     .title  {
   color: #2c3e50;
   text-align: center;
   margin: 40px 0;
   font-size: 2.5em;
   text-transform: uppercase;
   letter-spacing: 2px;
   border-bottom: 3px solid #3498db;
   padding-bottom: 10px;
   max-width: 600px;
   margin-left: auto;
   margin-right: auto;
}

    </style>


</head>
<body>
<?php include '../components/admin_header.php'; ?>
<h1 class="title">Sales Report</h1>

<section class="filtering">
<form method="POST">
    <section class="reportType">
    <label for="reportType">Report Type:</label>
    <?php
        // Define the report types as an associative array
        $reportTypes = [
            'overall' => 'Overall',
            'yearly' => 'Yearly',
            //'seasonal' => 'Seasonal', // Uncomment if you need this option
            'monthly' => 'Monthly',
            'daily' => 'Daily',
            'custom' => 'Custom'
        ];

        // Call the html_select2 function to generate the select dropdown
        echo html_select2('reportType', $reportTypes, null, 'onchange="this.form.submit()"', $reportType);
        ?>

    </section>

    <section class="CategorySelection">
   <!-- Category selection with dynamic product loading -->
        <label for="category">Category:</label>
        <select id="category" name="category" onchange="fetchProducts(this.value)">
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


    <!-- Product selection, initially only 'All Products' -->
    <label for="product">Product:</label>
    <?php
        // Define the product options as an associative array
        $productOptions = [
            'all' => 'All Products'
        ];

        // Call the html_select2 function to generate the select dropdown
        echo html_select2('product', $productOptions, null, '', $product);
        ?>

    <!-- Additional filters (status, year, etc.) -->
    <?php
    echo html_select2('status', ['all' => 'All Statuses', 'pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],null,'','');
    if ($reportType == 'yearly' || $reportType == 'seasonal' || $reportType == 'monthly') {
        echo "</br>"."</br>".'Year'."</br>";
        html_number('year', 2000, date('Y'), '', 'placeholder="Year"');
        echo "</br>";
        err("year");
        echo "</br>";
    }
    if ($reportType == 'seasonal') {
        echo   html_select2('season', ['Q1' => 'Q1', 'Q2' => 'Q2', 'Q3' => 'Q3', 'Q4' => 'Q4'],null,'','');
    }
    if ($reportType == 'monthly') {
        echo "</br>".'Month'."</br>";
        html_number('month', 1, 12, '', 'placeholder="Month"');
        echo "</br>";
        err("month");
        echo "</br>";
    }
    if ($reportType == 'daily' || $reportType == 'custom') {
        echo "</br>"."</br>".'Date';
        html_date('startDate');
        echo "</br>";
        err("startDate");
        echo "</br>";
        if ($reportType == 'custom') {
            echo "</br>".'End Date';
            
            html_date('endDate');
            echo "</br>";
            err("endDate");
            echo "</br>";
        }
    }
    ?>
    </section>

    <button type="submit">Generate Report</button>
</form>
</section>



<section class="chart">


<section class="salesTable">
    <h2 class="tableTitle">Sales Data</h2>
    <table>
        <thead>
            <tr>
                <th><?= $reportType === 'daily' ? 'Hour' : ($reportType === 'monthly' ? 'Day' : ($reportType === 'seasonal' ? 'Month' : ($reportType === 'custom' ? 'Date' : 'Year'))) ?></th>
                <th>Total Sales (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totalSales = 0; // Initialize total sales variable
            foreach ($chartLabels as $index => $label): 
                $sales = $chartData[$index] ?? 0; // Get sales data for current label
                $totalSales += $sales; // Accumulate total sales
            ?>
                <tr>
                    <td><?= htmlspecialchars($label) ?></td>
                    <td><?= number_format($sales, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><strong>Total</strong></td>
                <td><strong><?= number_format($totalSales, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>
</section>


<h2 class="chartTitle"><?= $chartTitle ?></h2>

<!-- Chart Container -->
<canvas id="revenueChart"></canvas>
</section>





<!-- Chart.js Logic -->
<script>
   
    var ctx = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,  // This will pass the correct year labels like 2020, 2021, etc.
        datasets: [{
            label: 'Total Revenue',
            data: <?= json_encode(array_values($chartData)) ?>,  // Make sure chartData is properly indexed
            borderColor: 'rgba(75, 192, 192, 1)',
            fill: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Years'
                },
                ticks: {
                    autoSkip: false  // Ensure all labels are displayed
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Revenue (in RM)'
                }
            }
        }
    }
});


    // Fetch products based on category selection
    function fetchProducts(categoryId) {
        fetch(`../ajax/fetch_products.php?category_id=${categoryId}`)
            .then(response => response.json())
            .then(data => {
                let productSelect = document.getElementById('product');
                productSelect.innerHTML = '';

                // Add each product to the select dropdown
                data.forEach(product => {
                    let option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name;
                    productSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching products:', error));
    }
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>
