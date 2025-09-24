<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the base file for required functions
include_once '../components/_base.php';

define('LOW_STOCK_THRESHOLD', 10); // Set low stock threshold to 10

// Check authentication for admin
auth('Admin'); // Ensure only admin can access this page

// Get the product ID from the URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Handle form submissions for adding/editing products
if (is_post()) {
    $action = post('action');

    if ($action === 'edit') {
        // Edit existing product
        $id = post('product_id');
        $name = post('name');
        $author = post('author');
        $price = post('price');
        $description = post('description');
        $stock = post('stock');
        $category_id = post('category_id');

        // Handle image update
        if (!empty($_FILES['image']['name'])) {
            $image = $_FILES['image']['name'];
            $image_tmp = $_FILES['image']['tmp_name'];
            move_uploaded_file($image_tmp, "../uploaded_img/$image");
            $stmt = $_db->prepare("UPDATE product SET name = ?, author = ?, price = ?, description = ?, image = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$name, $author, $price, $description, $image, $category_id, $id]);
        } else {
            $stmt = $_db->prepare("UPDATE product SET name = ?, author = ?, price = ?, description = ?, category_id = ? WHERE id = ?");
            $stmt->execute([$name, $author, $price, $description, $category_id, $id]);
        }

        temp('info', 'Product updated successfully!');
        redirect('products.php');
    } elseif ($action === 'delete') {
        // Delete product
        $id = post('product_id');
        if (!empty($id)) {
            $stmt = $_db->prepare("DELETE FROM product WHERE id = ?");
            $stmt->execute([$id]);
            temp('info', 'Product deleted successfully!');
            redirect('products.php');
        } else {
            temp('info', 'No product ID provided for deletion!');
        }
    } elseif ($action === 'update_stock') {
        // Update stock
        $id = post('product_id');
        $new_stock = post('new_stock');

        if (!empty($new_stock) && is_numeric($new_stock)) {
            $stmt = $_db->prepare("UPDATE product SET stock = ? WHERE id = ?");
            $stmt->execute([$new_stock, $id]);
            temp('info', 'Stock updated successfully!');
            redirect('products.php');
        } else {
            temp('info', 'Invalid stock value!');
        }
    }
}

// Handle product search
if (isset($_GET['search_query'])) {
    $search_query = trim($_GET['search_query']);
    
    if (!empty($search_query)) {
        // Search for a product by name (case-insensitive)
        $stmt = $_db->prepare("SELECT id FROM product WHERE name LIKE ?");
        $stmt->execute(['%' . $search_query . '%']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Redirect to viewbook.php with the product ID
            $product_id = $result['id'];
            header("Location: viewbook.php?product_id=$product_id");
            exit;
        } else {
            temp('info', 'No book found with that name!');
        }
    } else {
        temp('info', 'Please enter a valid book name!');
    }
}

// Fetch product details if product_id is valid
if ($product_id > 0) {
    $stmt = $_db->prepare("
        SELECT p.id, p.name, p.author, p.price, p.description, p.image, p.stock, p.category_id, c.name as categoryName, COALESCE(AVG(r.rating), 1) as average_rating 
        FROM product p 
        INNER JOIN category c ON p.category_id = c.id 
        LEFT JOIN review r ON p.id = r.product_id 
        WHERE p.id = ?
    ");
    
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle case if no product was found
    if (!$product) {
        temp('info', 'No product found with that ID!');
    }
} else {
    temp('info', 'Invalid product ID!');
}

// Fetch all products for low stock alert
$all_products_stmt = $_db->prepare("SELECT * FROM product");
$all_products_stmt->execute();
$all_products = $all_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check for low stock products
$low_stock_products = array_filter($all_products, function($product) {
    return $product['stock'] < LOW_STOCK_THRESHOLD;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products: Book Author System</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_prod.css">
    <script src="../js/admin_prod.js"></script>
    <style>
        .drop-zone {
            width: 100%;
            padding: 30px;
            border: 2px dashed #cccccc;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            text-align: center;
            color: #cccccc;
            margin-bottom: 20px;
        }
        .drop-zone.dragover {
            border-color: #333333;
            color: #333333;
        }
        img#previewImage {
            display: none;
            max-width: 200px;
            height: auto;
            margin-bottom: 10px;
        }
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1; /* Sit on top */
            padding-top: 100px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0, 0, 0); /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .low-stock-alert {
            background-color: #ffcc00; /* Yellow background for alert */
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ff9900; /* Darker border */
            color: #000;
        }
    </style>
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<section class="admin-products">
    <h2>Search Book</h2>
    <form action="products.php" method="get">
        <input type="text" name="search_query" placeholder="Enter book name..." required>
        <button type="submit" class="btn">Search</button>
    </form>
</section>
<br>
<?php if ($product) { ?>
<section class="admin-products">
    <h2>Searched Product</h2>
    <button type="submit" onclick="window.location.href='products.php';">Go Back</button>
    <?php if (count($low_stock_products) > 0): ?>
        <div class="low-stock-alert">
            <strong>Low Stock Alert!</strong> The following products have low stock:
            <ul>
                <?php foreach ($low_stock_products as $low_stock_product): ?>
                    <li><?= encode($low_stock_product['name']); ?> (Current Stock: <?= $low_stock_product['stock']; ?>)</li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="box-container" style="display: flex;
  align-items: center;
  justify-content: center;">
        <form action="products.php" method="post" class="box" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            <input type="text" name="name" value="<?= encode($product['name']); ?>" required>
            <input type="text" name="author" value="<?= encode($product['author']); ?>" required>
            <img src="../uploaded_img/<?= $product['image']; ?>" alt="<?= encode($product['name']); ?>">
            <input type="file" name="image" accept="image/*">
            <input type="number" name="price" value="<?= $product['price']; ?>" required>
            <textarea name="description"><?= encode($product['description']); ?></textarea>
            <p>Current Stock: <?= $product['stock']; ?></p>

            <div class="rating">Rating:
                <?php
                $average_rating = round($product['average_rating'], 1);
                for ($i = 1; $i <= 5; $i++) {
                    echo ($average_rating >= $i) ? '★' : '☆';
                }
                ?>
                <span class="rating-value"><?= number_format($average_rating, 1); ?></span>
            </div>

            <select name="category_id" required>
                <?php
                // Fetch categories for the dropdown
                $select_categories = $_db->prepare("SELECT * FROM category");
                $select_categories->execute();
                while ($category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                }
                ?>
            </select>

            <div class="buttons">
                    <button type="button" class="detail-btn">
                        <a style="color: #fefefe;" href="view_review.php?product_id=<?= $product['id']; ?>"  >View Reviews</a>
                    </button>

                    <button type="submit" name="update_product" class="edit-btn" onclick="document.getElementById('form-action').value='update';">Update</button>
                    <button type="button" class="btn delete-btn" onclick="openDeleteModal(<?= $product['id']; ?>)">Delete</button>

                    </div>
                    <button type="button" class="edit-stock-btn" onclick="openModal(<?= $product['id']; ?>, <?= $product['stock']; ?>)">Update Stock</button>
                </form>
    </div>
</section>
<?php } else { ?>
    <p>Product not found!</p>
<?php } ?>

<!-- Modal for Delete Confirmation -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this product?</p>
        <form id="deleteForm" action="products.php" method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" id="deleteProductId">
            <button type="submit" class="edit-btn">Yes, Delete</button>
            <button type="button" class="btn" onclick="closeDeleteModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- Modal for Update Stock -->
<div id="stockModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Update Stock</h2>
        <form action="products.php" method="post">
            <input type="hidden" name="action" value="update_stock">
            <input type="hidden" name="product_id" id="modalProductId">
            <p>Current Stock: <span id="currentStock"></span></p>
            <label for="newStock">New Stock Quantity:</label>
            <input type="number" name="new_stock" id="newStock" required>
            <button type="submit" class="btn">Update Stock</button>
        </form>
    </div>
</div>

<script>
    function openModal(productId, currentStock) {
        document.getElementById('modalProductId').value = productId;
        document.getElementById('currentStock').innerText = currentStock;
        document.getElementById('stockModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('stockModal').style.display = 'none';
    }

    function openDeleteModal(productId) {
        document.getElementById('deleteProductId').value = productId;
        document.getElementById('deleteModal').style.display = 'block';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>
