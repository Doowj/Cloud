<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the base file for required functions
include_once '../components/_base.php';

// Check authentication for admin
auth('Admin');

// Get category ID from the query string
$category_id = get('category_id');

// Fetch category details
$stmt = $_db->prepare("SELECT * FROM category WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    temp('info', 'Category not found!');
    redirect('../admin/category.php');
}

// Fetch products under the selected category
$stmt = $_db->prepare("SELECT * FROM product WHERE category_id = ?");
$stmt->execute([$category_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php $_title = "Products in  ".encode($category['name']) ;?>
    <title><?= $_title?></title>
    <title>Products in <?= encode($category['name']); ?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_prod.css">

</head>
<body>
<?php include '../components/admin_header.php' ?>


    <section class="admin-products">
    <button type="submit" onclick="window.location.href='category.php';">Go Back</button>

        <?php if (count($products) > 0): ?>
            <div class="box-container">
                <?php foreach ($products as $product): ?>
                    <div class="box">
                        <h3><?= encode($product['name']); ?></h3>
                        <img src="../uploaded_img/<?= $product['image']; ?>" alt="<?= encode($product['name']); ?>">
                        <p>Price: $<?= encode($product['price']); ?></p>
                        <p>Description: <?= encode($product['description']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty">No products in this category!</p>
        <?php endif; ?>
    </section>

    <script src="../js/admin_script.js"></script>
</body>
</html>
