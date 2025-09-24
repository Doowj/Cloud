<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';

// Ensure the user is authenticated
auth("Member");
$_title="Delete Review";
// Get review ID from the URL
$review_id = req('review_id');

// Fetch the review details and the product details to verify ownership
$review_query = $_db->prepare("
    SELECT r.*, p.name AS product_name, p.image AS product_image, r.rating
    FROM review r 
    INNER JOIN product p ON r.product_id = p.id 
    WHERE r.id = ? AND r.member_id = ?
");
$review_query->execute([$review_id, $_user->id]);
$review = $review_query->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    temp('info', 'Review not found or you do not have permission to delete this review.');
    redirect('index.php');
}

// Handle the deletion confirmation
if (is_post()) {
    // If confirmed, delete the review
    if (post('confirm') == 'yes') {
        $delete_review = $_db->prepare("DELETE FROM review WHERE id = ?");
        $delete_review->execute([$review_id]);

        temp('success', 'Your review has been successfully deleted.');
        redirect("quick_view.php?product_id=" . $review['product_id']);
    } else {
        // If not confirmed, redirect back to the product view page
        temp('info', 'Review deletion canceled.');
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
</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="delete-confirmation">
    <h1>Delete Review</h1>

    <div class="review-info">
        <div class="product-image">
            <img src="uploaded_img/<?= htmlspecialchars($review['product_image']); ?>" alt="<?= htmlspecialchars($review['product_name']); ?>">
        </div>
        <div class="review-details">
            <h3>Product: <?= htmlspecialchars($review['product_name']); ?></h3>
            <p><strong>Review Title:</strong> <?= htmlspecialchars($review['title']); ?></p>
            <p><strong>Your Review:</strong> <?= htmlspecialchars($review['description']); ?></p>
            <p class="rating"><strong>Rating:</strong> <?= str_repeat('★', $review['rating']); ?> (<?= $review['rating'] ?>)</p>
        </div>
    </div>

    <p class="confirmation-message">Are you sure you want to delete this review?</p>

    <div class="btn-group">
        <form action="delete_review.php?review_id=<?= htmlspecialchars($review_id) ?>" method="post">
            <button type="submit" name="confirm" value="yes" class="btn delete-btn">Yes, Delete</button>
        </form>
        <form action="delete_review.php?review_id=<?= htmlspecialchars($review_id) ?>" method="post">
            <button type="submit" name="confirm" value="no" class="btn cancel-btn">No, Cancel</button>
        </form>
    </div>
</section>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>
