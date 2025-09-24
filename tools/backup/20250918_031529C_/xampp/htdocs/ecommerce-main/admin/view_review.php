<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/_base.php';

// Get product_id from the URL
$product_id = req('product_id');
$filter_rating = req('rating', ''); // Filter reviews by rating
$page = req('page', 1); // For pagination

// Validate product_id
if (!$product_id) {
    temp('error', 'Product not found.');
    redirect('products.php');
}

// Fetch product details
$product_query = $_db->prepare("SELECT * FROM product WHERE id = ?");
$product_query->execute([$product_id]);
$product = $product_query->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    temp('error', 'Product not found.');
    redirect('products.php');
}

// Fetch product reviews based on rating filter
$rating_condition = '';
$rating_params = [$product_id];

// If a rating filter is applied
if (!empty($filter_rating)) {
    $rating_condition = 'AND r.rating = ?';
    $rating_params[] = $filter_rating;
}

// Pagination setup
$limit = 5; // Number of reviews per page
$offset = ($page - 1) * $limit;

// Get total reviews for pagination
$total_reviews_query = $_db->prepare("
    SELECT COUNT(*) 
    FROM review r 
    WHERE r.product_id = ? $rating_condition
");
$total_reviews_query->execute($rating_params);
$total_reviews = $total_reviews_query->fetchColumn();
$total_pages = ceil($total_reviews / $limit);

// Fetch reviews with pagination
$reviews_query = $_db->prepare("
    SELECT r.*, u.name as user_name, u.image 
    FROM review r 
    INNER JOIN user u ON r.member_id = u.id 
    WHERE r.product_id = ? $rating_condition 
    ORDER BY r.date DESC 
    LIMIT $limit OFFSET $offset
");
$reviews_query->execute($rating_params);
$reviews = $reviews_query->fetchAll(PDO::FETCH_ASSOC);

// Calculate star distribution for the filter
$star_distribution_query = $_db->prepare("
    SELECT rating, COUNT(*) as count 
    FROM review 
    WHERE product_id = ? 
    GROUP BY rating
");
$star_distribution_query->execute([$product_id]);
$star_distribution = $star_distribution_query->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $_title = "Products in  ".htmlspecialchars($product['name']);?>
    <title><?= $_title?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="product-reviews">
    <h1>Reviews for <?= htmlspecialchars($product['name']); ?></h1>
    
    <!-- Review Filters: All, 5-star, 4-star, etc. -->
    <div class="review-filters">
        <a href="view_review.php?product_id=<?= $product_id ?>" class="<?= empty($filter_rating) ? 'active' : '' ?>">All (<?= $total_reviews ?>)</a>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <?php $count = $star_distribution[$i] ?? 0; ?>
            <a href="view_review.php?product_id=<?= $product_id ?>&rating=<?= $i ?>" class="<?= $filter_rating == $i ? 'active' : '' ?>">
                <?= $i ?> Star (<?= $count ?>)
            </a>
        <?php endfor; ?>
    </div>
    <button type="submit" onclick="window.location.href='products.php';">Go Back</button>

    <!-- Reviews Section -->
    <div class="reviews-container">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <div class="review-left">
                        <img src="/user_img/<?= htmlspecialchars($review['image']); ?>" alt="User Image" class="user-avatar">
                        <div class="user-details">
                            <h4><?= htmlspecialchars($review['user_name']); ?></h4>
                            <p><?= htmlspecialchars($review['date']); ?></p>
                        </div>
                    </div>
                    <div class="review-content">
                        <h4><?= htmlspecialchars($review['title']); ?></h4>
                        <p><?= htmlspecialchars($review['description']); ?></p>
                    </div>
                    <div class="review-right">
                        <div class="user-rating"><?= str_repeat('★', $review['rating']); ?> (<?= $review['rating']; ?>)</div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews found for this product.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <ul>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li><a href="view_review.php?product_id=<?= $product_id ?>&rating=<?= $filter_rating ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul>
        <?php endif; ?>
    </div>
    <br>
</section>


<script src="../js/script.js"></script>
</body>
</html>
