<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the base file for required functions
include_once '../components/_base.php';

define('LOW_STOCK_THRESHOLD', 10); // Set low stock threshold to 10

// Check authentication for admin
auth('Admin'); // Ensure only admin can access this page

// Handle form submissions for adding/editing products
if (is_post()) {
    $action = post('action');

    if ($action === 'add') {
        // Add a new product
        $name = post('name');
        $author = post('author'); 
        $price = post('price');
        $description = post('description');
        $stock = post('stock');
        $category_id = post('category_id');
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
    
        // Validation
        $errors = [];
    
        // Validate name
        if (empty($name)) {
            $_err['name'] = 'Product name is required.';
        }
    
        // Validate author
        if (empty($author)) {
            $_err['author'] = 'Author is required.';
        }
    
        // Validate price
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $_err['price'] = 'Valid product price is required.';
        }
    
        // Validate description
        if (empty($description)) {
            $_err['description'] = 'Product description is required.';
        }
    
        // Validate stock
        if (empty($stock) || !is_numeric($stock) || $stock < 0) {
            $_err['stock'] = 'Valid stock quantity is required.';
        }
    
        // Validate category ID
        if (empty($category_id) || !is_numeric($category_id)) {
            $_err['category_id'] = 'Valid category ID is required.';
        }
    
        // Validate image upload
        if (empty($image)) {
            $_err['image']= 'Image upload is required.';
        } elseif (!in_array(pathinfo($image, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
            $_err['image'] = 'Only image files (jpg, jpeg, png, gif) are allowed.';
        }
    
        // Check if there are any validation errors
        if (empty($_err)) {
            move_uploaded_file($image_tmp, "../uploaded_img/$image");
            $stmt = $_db->prepare("INSERT INTO product (name, author, price, description, image, stock, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $author, $price, $description, $image, $stock, $category_id]);
            temp('info', 'Product added successfully!');

        } else {
            // Handle errors
            foreach ($errors as $error) {
                temp('info', $error);
            }
        }
    
    } elseif ($action === 'edit') {
        // Edit existing product
        $id = post('product_id');
        $name = post('name');
        $author = post('author'); // New field for editing
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
            //redirect('products.php');
        } else {
            temp('info', 'No product ID provided for deletion!');
        }
    }
    elseif ($action === 'update_stock') {
        // Update stock by adding to the current stock
        $id = post('product_id');
        $new_stock = post('new_stock');
    
        // Validate new stock to ensure it's numeric and positive
        if (!empty($new_stock) && is_numeric($new_stock) && $new_stock > 0) {
            // Get the current stock
            $stmt = $_db->prepare("SELECT stock FROM product WHERE id = ?");
            $stmt->execute([$id]);
            $current_stock = $stmt->fetchColumn();
    
            // Add the new stock to the current stock
            $updated_stock = $current_stock + $new_stock;
    
            // Update the stock in the database
            $stmt = $_db->prepare("UPDATE product SET stock = ? WHERE id = ?");
            $stmt->execute([$updated_stock, $id]);
    
            temp('info', 'Stock updated successfully!');
            // redirect('products.php');
        } else {
            temp('info', 'Invalid stock value! Stock must be a positive number.');
        }
    }
    
}


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



// Add this section to handle CSV file upload
if (is_post() && post('action') === 'batch_csv_upload') {
    $csvFile = $_FILES['csv_file']['tmp_name'];

    // Check if the file is uploaded
    if (is_uploaded_file($csvFile)) {
        // Open the file for reading
        if (($handle = fopen($csvFile, 'r')) !== FALSE) {
            // Read the header row
            $header = fgetcsv($handle, 1000, ',');

            // Check if the header matches expected columns
            if ($header === ['name', 'author', 'price', 'description', 'stock', 'category_id', 'image']) {
                // Prepare SQL statement for inserting products
                $stmt = $_db->prepare("INSERT INTO product (name, author, price, description, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");

                // Loop through each row in the CSV
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    // Check if data row has the expected number of columns
                    if (count($data) === 7) {
                        // Move the image to the desired directory
                        $image_name = $data[6]; // This assumes the image name is provided in the CSV
                        $image_tmp_path = $_FILES['csv_file']['tmp_name']; // Get the temp path of the uploaded CSV

                        // Check if the image file exists
                        if (file_exists("../uploaded_img/$image_name")) {
                            // Insert the product into the database
                            $stmt->execute([$data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $image_name]);
                        } else {
                            error_log("Image file does not exist: " . $image_name); // Log for debugging
                        }
                    } else {
                        // Log or handle the error for the row
                        error_log("Invalid row: " . implode(',', $data)); // For debugging
                    }
                }

                fclose($handle);
                temp('success', 'Products uploaded successfully!');
            } else {
                temp('error', 'CSV header does not match expected format!');
            }
        } else {
            temp('error', 'Error opening the CSV file!');
        }
    } else {
        temp('error', 'No file uploaded!');
    }

    //redirect('../admin/products.php');
}





// Fetch all products
$products = $_db->query("SELECT p.id, p.name, p.author, p.price, p.description, p.image, p.stock, p.category_id, c.name as categoryName, COALESCE(AVG(r.rating), 1) as average_rating 
FROM product p 
INNER JOIN category c ON p.category_id = c.id 
LEFT JOIN review r ON p.id = r.product_id 
GROUP BY p.id")->fetchAll(PDO::FETCH_ASSOC);

// Check for low stock products
$low_stock_products = array_filter($products, function($product) {
    return $product['stock'] < LOW_STOCK_THRESHOLD;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php $_title = "Manage Products: Book Author System";?>
    <title><?= $_title?></title>
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
<?php include '../components/admin_header.php' ?>

<section class="admin-products">
<h2>Search Book</h2>
    <form action="products.php" method="get">
        <input type="text" name="search_query" placeholder="Enter book name..." required>
        <button type="submit" class="btn">Search</button>
    </form>
</section>
<br>

    <section class="admin-products">
        <h2>Add New Product</h2>
        <form action="products.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
    <div > 
    <table>
        <tr>
            <td><label for="name">Product Name</label></td>
            <td>
                <?= html_text('name', 'maxlength="100" placeholder="Product Name" ') ?>
                <?= err('name') ?>
            </td>
        </tr>
        <tr>
            <td><label for="author">Author</label></td>
            <td>
                <?= html_text('author', 'maxlength="100" placeholder="Author" ') ?>
                <?= err('author') ?>
            </td>
        </tr>
        <tr>
            <td><label for="price">Product Price</label></td>
            <td>
                <?= html_text('price', 'maxlength="10" placeholder="Product Price" ') ?>
                <?= err('price') ?>
            </td>
        </tr>
        <tr>
            <td><label for="description">Product Description</label></td>
            <td>
                <?= html_textarea('description', 'placeholder="Product Description"') ?>
                <?= err('description') ?>
            </td>
        </tr>
        <tr>
            <td><label for="stock">Stock Quantity</label></td>
            <td>
                <?= html_number('stock', 'min="0" placeholder="Stock Quantity" ') ?>
                <?= err('stock') ?>
            </td>
        </tr>
    </table>
    </div>
    <br>
            <!-- Drag-and-Drop Area for Photo Upload -->
            <div class="drop-zone" id="dropZone">
                Drag & drop your image here or click to upload
                <input type="file" name="image" id="fileInput" accept="image/*" style="display:none;">
            </div>
            <img id="previewImage" alt="Image Preview">
            <?= err('image') ?>

            <br>
            <br>
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
            <button type="submit" class="btn">Add Product</button>
        </form>
        </section>

        <br>
        <section class="admin-products">
        <h2>Batch Upload Products (CSV)</h2>
        <form action="products.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="batch_csv_upload">>
        <label for="csv_file">Upload CSV File</label>
        <?= html_file('csv_file', 'accept=".csv" required') ?>
        <?= err('csv_file') ?>

    <button type="submit" class="btn">Upload CSV</button>
</form>
        </section>
<br>

<section class="admin-products">
        <h2>Existing Products</h2>
        <?php if (count($low_stock_products) > 0): ?>
            <div class="low-stock-alert">
                <strong>Low Stock Alert!</strong> The following products have low stock:
                <ul>
                    <?php foreach ($low_stock_products as $product): ?>
                        <li><?= encode($product['name']); ?> (Current Stock: <?= $product['stock']; ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="box-container">
            <?php
            if (count($products) > 0) {
                foreach ($products as $product) {
                    // Check if stock is low
                    $low_stock_alert = $product['stock'] <= LOW_STOCK_THRESHOLD ? '<span class="low-stock-alert">Low Stock!</span>' : '';
                    $average_rating = round($product['average_rating'], 1); // Ensure one decimal place
            ?>
                <form action="products.php" method="post" class="box" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                    <input type="text" name="name" value="<?= encode($product['name']); ?>" required>
                    <input type="text" name="author" value="<?= encode($product['author']); ?>" required>
                    <img src="../uploaded_img/<?= $product['image']; ?>" alt="<?= encode($product['name']); ?>">
                    <input type="file" name="image" accept="image/*">
                    <input type="" name="price" value="<?= $product['price']; ?>" required>
                    <textarea name="description"><?= encode($product['description']); ?></textarea>
                    
                    <p>Current Stock: <?= $product['stock']; ?> <?= $low_stock_alert; ?></p>

                    <div class="rating" style=" display: inline-block; margin-left: 5px;">Rating
         <?php
         for ($i = 1; $i <= 5; $i++) {
            if ($average_rating >= $i) {
               echo '★'; // Full star
            } elseif ($average_rating >= $i - 0.5) {
               echo '☆'; // Half star
            } else {
               echo '☆'; // Empty star
            }
         }
         ?>
         <span class="rating-value"><?= number_format($average_rating, 1); ?></span>
      </div>

                    <select name="category_id" required>
                        <?php
                        // Fetch categories for the dropdown
                        $select_categories->execute();
                        while ($category = $select_categories->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $product['category_id'] == $category['id'] ? 'selected' : '';
                            echo '<option value="' . $category['id'] . '" ' . $selected . '>' . $category['name'] . '</option>';
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
            <?php
                }
            } else {
                echo '<p class="empty">No products added yet!</p>';
            }
            ?>
        </div>
    </section>


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


   <!-- Modal for View Details (Update Stock) -->
<div id="stockModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Update Stock</h2>
        <form action="products.php" method="post">
            <input type="hidden" name="action" value="update_stock">
            <input type="hidden" name="product_id" id="modalProductId">

            <div class="form-group">
                <p>Current Stock: <span id="currentStock"></span></p>
            </div>

            <div class="form-group">
                <label for="newStock">Add Stock Quantity:</label>
                <?= html_number('new_stock', 'id="newStock" required') ?>
                <?= err('new_stock') ?>
            </div>

            <button type="submit" class="btn">Update Stock</button>
        </form>
    </div>
</div>



            <script>
                function confirmDelete() {
                    if (confirm('Are you sure you want to delete this product?')) {
                        document.getElementById('form-action').value = 'delete';
                        return true; // Proceed with the form submission
                    }
                    return false; // Cancel the form submission
                }
                </script>

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
