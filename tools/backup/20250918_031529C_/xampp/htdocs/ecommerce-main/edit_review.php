<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';

$_title="Edit Review";
// Ensure the user is authenticated
auth("Member");

// Get the review_id from the URL
$review_id = req('review_id');

// Fetch the review details to ensure the review belongs to the current user
$review_query = $_db->prepare("SELECT * FROM review WHERE id = ? AND member_id = ?");
$review_query->execute([$review_id, $_user->id]);
$review = $review_query->fetch(PDO::FETCH_ASSOC);



if (!$review) {
    temp('error', 'Review not found or you do not have permission to edit this review.');
    redirect('index.php');
}

// Fetch the product details for the review
$product_query = $_db->prepare("SELECT * FROM product WHERE id = ?");
$product_query->execute([$review['product_id']]);
$product = $product_query->fetch(PDO::FETCH_ASSOC);


if (!$product) {
    temp('error', 'Product not found.');
    redirect('index.php');
}

// Handle form submission for updating the review
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

    // If no validation errors, update the review in the database
    if (empty($_err)) {
        $update_review = $_db->prepare("UPDATE review SET title = ?, description = ?, rating = ? WHERE id = ? AND member_id = ?");
        $update_review->execute([$title, $description, $rating, $review_id, $_user->id]);

        temp('success', 'Your review has been updated.');
        redirect("quick_view.php?product_id=" . $review['product_id']);
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
    <h1>Edit Review for <?= htmlspecialchars($product['name']) ?></h1>

    <form action="edit_review.php?review_id=<?= htmlspecialchars($review_id) ?>" method="post" class="edit-review-form" novalidate>
        <div class="form-group">
            <label for="rating">Rating (1 to 5):</label>
            <?= html_text2("rating", '', htmlspecialchars($review['rating'])) ?>
            <?= err('rating') ?>  <!-- Display rating error -->
        </div>

        <div class="form-group">
            <label for="title">Title:</label>
            <?= html_text2("title", '', htmlspecialchars($review['title'])) ?>
            <?= err('title') ?>  <!-- Display title error -->
        </div>

        <div class="form-group">
            <label for="description">Review:</label>
            <?= html_textarea2("description", '', htmlspecialchars($review['description'])) ?>
            <?= err('description') ?>  <!-- Display description error -->
        </div>

        <button type="submit" class="btn">Update Review</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
