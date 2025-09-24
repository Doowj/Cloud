<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include base.php for database connection
include '../components/_base.php';

// Get the selected category ID from the request
$categoryId = $_GET['category_id'] ?? 'all';

if ($categoryId === 'all') {
    echo json_encode([['id' => 'all', 'name' => 'All Products']]);
    exit;
}

// Fetch products for the selected category
$sql = "SELECT id, name FROM product WHERE category_id = :category_id";
$stmt = $_db->prepare($sql);
$stmt->execute([':category_id' => $categoryId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add 'All Products' option at the start
array_unshift($products, ['id' => 'all', 'name' => 'All Products']);

// Return the products as a JSON response
echo json_encode($products);
?>
