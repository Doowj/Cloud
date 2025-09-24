<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the base file for required functions
include_once '../components/_base.php';

// Check authentication for admin
auth('Admin');

// Handle form submissions for adding/editing categories
if (is_post()) {
    $action = post('action');

    // Validate the category name
    $name = trim(post('name')); // Trim whitespace from input

        
        // Validation
        $errors = [];

    // Check if the category name is empty
    if (empty($name)) {
        $_err['name'] = 'Category name cannot be empty!';
    }

    // Check if the category name exceeds 20 characters
    if (strlen($name) > 20) {
        $_err['name'] = 'Category name must be less than 20 characters!';
    }

    // Sanitize category name (optional)
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    if ($action === 'add' && empty($_err)) {
        // Add a new category
        $stmt = $_db->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->execute([$name]);
        temp('info', 'Category added successfully!');
        redirect('../admin/category.php');
    } elseif ($action === 'edit' && empty($_err)) {
        // Edit existing category
        $id = post('category_id');
        $stmt = $_db->prepare("UPDATE category SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        temp('info', 'Category updated successfully!');
        redirect('../admin/category.php');
    } elseif ($action === 'delete') {
        // Delete category
        $id = post('category_id');
        if (!empty($id)) {
            $stmt = $_db->prepare("DELETE FROM category WHERE id = ?");
            $stmt->execute([$id]);
            temp('info', 'Category deleted successfully!');
            redirect('../admin/category.php');
        } else {
            temp('info', 'No category ID provided for deletion!');
        }
    } else {
        // Display errors if validation fails
        foreach ($errors as $error) {
            temp('info', $error);
        }
    }
}

// Fetch all categories
$categories = $_db->query("SELECT * FROM category")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $_title = "Manage Categories";?>
    <title><?= $_title?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/admin_prod.css">
    
    <script src="../js/admin_prod.js"></script>
    <style>
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
        }.close {
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
    </style>
</head>
<body>
<?php include '../components/admin_header.php' ?>

    <section class="admin-categories">
        <h2>Add New Category</h2>
        <form action="category.php" method="post">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
             <label for="name">Category Name</label>
            <?= html_text('name', 'maxlength="100" placeholder="Category Name" ') ?>
            <?= err('name') ?>
</div>

            <button type="submit" class="btn">Add Category</button>
        </form>

        <h2>Existing Categories</h2>
        <div class="box-container">
    <?php if (count($categories) > 0): ?>
        <?php foreach ($categories as $category): ?>
            <form action="category.php" method="post" class="box">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" value="<?= $category['id']; ?>">
                <input type="text" name="name" value="<?= encode($category['name']); ?>" >
                <button type="submit" class="edit-btn">Update</button>
            
                <input type="hidden" name="category_id" value="<?= $category['id']; ?>">
                <button type="button" class="btn delete-btn" onclick="openDeleteModal(<?= $category['id']; ?>)">Delete</button>
                <a href="view_category.php?category_id=<?= $category['id']; ?>" class="btn">View Details</a> <!-- New Button -->
            </form>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="empty">No categories added yet!</p>
    <?php endif; ?>
</div>

    </section>

    

<!-- Modal for Delete Confirmation -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this category?</p>
        <form id="deleteForm" action="category.php" method="post">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="category_id" id="deleteCategoryId">
            <button type="submit" class="edit-btn">Yes, Delete</button>
            <button type="button" class="btn" onclick="closeDeleteModal()">Cancel</button>
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
function openDeleteModal(categoryId) {
    document.getElementById('deleteCategoryId').value = categoryId; // Set the category ID in the hidden input
    document.getElementById('deleteModal').style.display = 'block'; // Show the modal
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none'; // Hide the modal
}

                </script>

    <script src="../js/admin_script.js"></script>
</body>
</html>

