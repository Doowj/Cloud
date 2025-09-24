<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/_base.php';

// Ensure the user is logged in
if (!isset($_user->id)) {
    temp('info','Please log in first');
    echo "User not logged in";
    exit;
}

// Get the product ID and favorite status from the POST request
$product_id = post('product_id');
$is_favorite = post('favorite');

// Validate product ID
if (!$product_id || !ctype_digit($product_id)) {
    echo "Invalid product ID";
    exit;
}

// Check if the product exists in the database
$product_check_stmt = $_db->prepare("SELECT COUNT(*) FROM product WHERE id = ?");
$product_check_stmt->execute([$product_id]);
if ($product_check_stmt->fetchColumn() == 0) {
    echo "Product does not exist";
    exit;
}

// Add or remove the product from favorites based on the checkbox state
if ($is_favorite == 1) {
    // Add to favorites (ensure it's unique)
    $add_favorite_stmt = $_db->prepare("
        INSERT INTO favorites (member_id, product_id)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE product_id = VALUES(product_id)
    ");
    $add_favorite_stmt->execute([$_user->id, $product_id]);
    echo "Added to favorites";
} else {
    // Remove from favorites
    $remove_favorite_stmt = $_db->prepare("DELETE FROM favorites WHERE member_id = ? AND product_id = ?");
    $remove_favorite_stmt->execute([$_user->id, $product_id]);
    echo "Removed from favorites";
}
?>
