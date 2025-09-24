<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


include 'components/_base.php';

// Ensure the user is authenticated
auth("Member");
$_title="Add Review";
// Get the product_id from the URL
$product_id = req('product_id');

// Check if the product exists
$product_query = $_db->prepare("SELECT * FROM product WHERE id = ?");
$product_query->execute([$product_id]);
$product = $product_query->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    temp('error', 'Product not found.');
    redirect('index.php');
}

// Check if the user is eligible to add a review (order with status completed)
$order_check_query = $_db->prepare("
    SELECT COUNT(*) 
    FROM orders o 
    INNER JOIN orderproduct op ON o.id = op.ordersid 
    WHERE o.member_id = ? AND op.product_id = ? AND o.status = 'completed'
");
$order_check_query->execute([$_user->id, $product_id]);
$can_add_review = $order_check_query->fetchColumn() > 0;

if (!$can_add_review) {
    temp('error', 'You can only leave a review if you have completed an order for this product.');
    redirect("quick_view.php?product_id=$product_id");
}

// Handle form submission
if (is_post()) {
    $rating = post('rating');
    $title = post('title');
    $description = post('description');

    // Validation flags
    $_err = [];

    // Validate the rating field (required, number, and range)
    if (empty($rating)) {
        $_err['rating'] = 'Rating is required';
    } elseif (!is_numeric($rating)) {
        $_err['rating'] = 'Rating must be a number';
    } elseif ($rating < 1 || $rating > 5) {
        $_err['rating'] = 'Rating must be between 1 and 5';
    }

    // Validate the title field (required)
    if (empty($title)) {
        $_err['title'] = 'Title is required';
    }

    // Validate the description field (required)
    if (empty($description)) {
        $_err['description'] = 'Description is required';
    }

    // If no validation errors, insert the review into the database
    if (empty($_err)) {
        $insert_review = $_db->prepare("
            INSERT INTO review (member_id, product_id, title, description, rating, date) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $insert_review->execute([$_user->id, $product_id, $title, $description, $rating]);

        temp('success', 'Your review has been submitted.');
        redirect("quick_view.php?product_id=$product_id");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/form.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="add-review-section">
    <h1>Add Review for <?= htmlspecialchars($product['name']) ?></h1>

    <form action="add_review.php?product_id=<?= htmlspecialchars($product_id) ?>" method="post" class="add-review-form" novalidate>
        <div class="form-group">
            <label for="rating">Rating (1 to 5):</label>
            <?= html_text("rating", '') ?>
            <?= err('rating') ?>  <!-- Display rating error -->
        </div>

        <div class="form-group">
            <label for="title">Title:</label>
            <?= html_text("title", '') ?>
            <?= err('title') ?>  <!-- Display title error -->
        </div>

        <div class="form-group">
            <label for="description">Review:</label>
            <?= html_textarea("description", "") ?>
            <?= err('description') ?>  <!-- Display description error -->
        </div>

        <button type="submit" class="btn">Submit Review</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
