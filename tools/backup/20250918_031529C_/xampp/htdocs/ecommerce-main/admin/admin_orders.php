<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../components/_base.php';
auth('Admin');
$_title = "Order List";

// Define fields for sorting
$fields = [
    'id'            => 'Order ID',
    'customer_name' => 'Customer',
    'total_product' => 'Total Products',
    'total_price'   => 'Total Amount',
    'status'        => 'Order Status',
    'payment_method' => 'Payment Method',
    'payment_status' => 'Payment Status'
];

// Sorting parameters
$sort = req('sort', 'id');
key_exists($sort, $fields) || $sort = 'id';

$dir = req('dir', 'asc');
in_array($dir, ['asc', 'desc']) || $dir = 'asc';

// Paging parameters
$page = req('page', 1);

// Search parameters
$search_query = req('search', '');

// Records per page selection
$limit = req('records', 10);
$limit = in_array($limit, [10, 20, 50, 100]) ? $limit : 10;

// Modify the SQL query to include search conditions
$search_condition = '';
$search_params = [];
if (!empty($search_query)) {
    $search_condition = "WHERE o.id LIKE :search_query OR u.name LIKE :search_query";
    $search_params['search_query'] = '%' . $search_query . '%';
}

// Use SimplePager for paging and fetching sorted results
require_once '../lib/SimplePager.php';
$p = new SimplePager("
    SELECT o.id, o.date, o.time, o.total_product, o.total_price, o.status, 
           u.name AS customer_name,
           p.payment_method, p.status AS payment_status
    FROM orders o
    INNER JOIN user u ON o.member_id = u.id
    LEFT JOIN payment p ON o.id = p.orders_id
    $search_condition
    ORDER BY $sort $dir", $search_params, $limit, $page);

$orders = $p->result;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
          .order-listing h1 {
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


/* Table Head Styling */
table thead th {
    background-color: #3498db;
    color: #ffffff;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 10px;
}

/* Row Hover Effect */
table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Align text in Action column */
table tbody tr td:last-child {
    text-align: center;
}

/* Table Headers with Sorting Links */
table thead th a {
    color: inherit;
    text-decoration: none;
    display: block;
    position: relative;
    padding-right: 20px; /* Space for sort arrows */
    background-color: gray;
}

/* Sort Arrows */
table thead th a.asc::after {
    content: ' ▴';  /* Upward arrow */
    position: absolute;
    font-size: 1.5em; /* Adjust size as needed */
}

table thead th a.desc::after {
    content: ' ▾';  /* Downward arrow */
    position: absolute;
  
    font-size: 1.5em; /* Adjust size as needed */
}


    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="order-listing">
    <h1>Orders</h1>

    <!-- Search Form -->
    <form method="GET" id="search-form">
        <label for="search">Search by Order ID or Customer Name:</label>
        <br>
        <?php $searchValue = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
                 echo html_search2('search', 'class="search-input"', $searchValue);  ?>

        <button type="submit">Search</button>
    </form>
    <br>
    <br>
    <br>

    <!-- Records Per Page Form -->
    <form method="GET" id="records-per-page-form">
        <label for="records">Show:</label>
        <select name="records" id="records" onchange="document.getElementById('records-per-page-form').submit();">
            <option value="10" <?= ($p->limit == 10) ? 'selected' : '' ?>>10 Records</option>
            <option value="20" <?= ($p->limit == 20) ? 'selected' : '' ?>>20 Records</option>
            <option value="50" <?= ($p->limit == 50) ? 'selected' : '' ?>>50 Records</option>
            <option value="100" <?= ($p->limit == 100) ? 'selected' : '' ?>>100 Records</option>
        </select>
    </form>
    <br>
    <p>
        <?= $p->count ?> of <?= $p->item_count ?> record(s) |
        Page <?= $p->page ?> of <?= $p->page_count ?>
    </p>

    <table>
        <thead>
            <tr>
                <?= table_headers($fields, $sort, $dir, "page=$page&records=$limit&search=" . urlencode($search_query)) ?>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= encode($order->id) ?></td>
                    <td><?= encode($order->customer_name) ?></td>
                    <td><?= encode($order->total_product) ?></td>
                    <td><?= number_format($order->total_price, 2) ?></td>
                    <td><?= encode($order->status) ?></td>
                    <td><?= encode(ucfirst($order->payment_method)) ?></td>
                    <td><?= encode(ucfirst($order->payment_status)) ?></td>
                    <td><a href="admin_order_detail.php?id=<?= encode($order->id) ?>">View & Update</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
         <?= $p->html() ?>  <!-- Display pagination links -->
    </div>
</section>

<script src="../js/admin_script.js"></script>
</body>
</html>
