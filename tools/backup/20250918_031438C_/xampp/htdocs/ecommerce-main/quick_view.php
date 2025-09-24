<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';
include 'components/add_cart.php';

$_title="Quick View for Product";
// Ensure the user is authenticated
if (isset($_user->id)) {
    // Fetch products in the user's cart
    $cart_stmt = $_db->prepare("SELECT product_id, quantity FROM cart WHERE member_id = ?");
    $cart_stmt->execute([$_user->id]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Create an associative array of cart items with their quantities
    $cart_quantities = [];
    foreach ($cart_items as $item) {
        $cart_quantities[$item['product_id']] = $item['quantity'];
    }
    
    // Fetch products in the user's favorites
    $favorite_stmt = $_db->prepare("SELECT product_id FROM favorites WHERE member_id = ?");
    $favorite_stmt->execute([$_user->id]);
    $favorite_items = $favorite_stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $cart_quantities = [];
    $favorite_items = [];
}

// Get the product_id from the URL
$product_id = req('product_id');


$is_favorited = in_array($product_id, $favorite_items);  // Check if the product is in favorites


// Fetch product details
$product_query = $_db->prepare("SELECT p.*, c.name as categoryName FROM product p INNER JOIN category c ON p.category_id = c.id WHERE p.id = ?");
$product_query->execute([$product_id]);
$product = $product_query->fetch(PDO::FETCH_ASSOC);

// Fetch product reviews
$review_query = $_db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews, SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star, SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star, SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star, SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star, SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star FROM review WHERE product_id = ?");
$review_query->execute([$product_id]);
$review_stats = $review_query->fetch(PDO::FETCH_ASSOC);

// Check if the user has added the product to their cart
$is_in_cart = false;
$cart_quantity = 0;
if (isset($_user)) {
    $cart_query = $_db->prepare("SELECT quantity FROM cart WHERE member_id = ? AND product_id = ?");
    $cart_query->execute([$_user->id, $product_id]);
    $cart_item = $cart_query->fetch(PDO::FETCH_ASSOC);
    if ($cart_item) {
        $is_in_cart = true;
        $cart_quantity = $cart_item['quantity'];
    }
}


// Fetch all reviews for the product
$reviews_query = $_db->prepare("
    SELECT r.*, u.name AS user_name ,u.image
    FROM review r 
    INNER JOIN user u ON r.member_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.date DESC
");
$reviews_query->execute([$product_id]);
$reviews = $reviews_query->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is eligible to add a review (completed order)
$can_add_review = false;
if (isset($_user->id)) {
    $order_check_query = $_db->prepare("
        SELECT COUNT(*) 
        FROM orders o 
        INNER JOIN orderproduct op ON o.id = op.ordersid 
        WHERE o.member_id = ? AND op.product_id = ? AND o.status = 'completed'
    ");
    $order_check_query->execute([$_user->id, $product_id]);
    $can_add_review = $order_check_query->fetchColumn() > 0;
}

// Check if the user has already reviewed this product
$existing_review = null;
if (isset($_user->id)) {
    $user_review_query = $_db->prepare("SELECT * FROM review WHERE member_id = ? AND product_id = ?");
    $user_review_query->execute([$_user->id, $product_id]);
    $existing_review = $user_review_query->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="css/style.css">
    <style>

    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<form action="" method="post">
<section class="quick-view">
    <div class="product-image">
        <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
    </div>
    <div class="product-info">
        <h1><?= htmlspecialchars($product['name']); ?></h1>
        <p><strong>Author:</strong> <?= htmlspecialchars($product['author']); ?></p>
        <p><strong>Price:</strong> RM<?= htmlspecialchars($product['price']); ?></p>
        <p><strong>Description:</strong> <?= htmlspecialchars($product['description']); ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($product['categoryName']); ?></p>

        <div class="cart-options">
           <input type="number" name="qty" min="1" max="99" maxlength="2" value="<?= $cart_quantity ?>" <?= $cart_quantity > 0 ? 'readonly' : '' ?> />
        
            <?php if ($is_in_cart): ?>
                <span class="checkmark">✅</span>
            <?php endif; ?>
            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
            
            <div class="flex-category-like">
            <input type="checkbox" id="like_<?= $product_id; ?>" class="sr-only" <?= $is_favorited ? 'checked' : ''; ?> onchange="toggleFavorite(<?= $product_id; ?>, this.checked)">
            <label for="like_<?= $product_id; ?>" aria-hidden="true" class="like-label <?= $is_favorited ? 'liked' : ''; ?>">❤</label>
         </div>
        
        </div>
    </div>

    <!-- Rating Section -->
    <div class="rating">
        <div class="rating-score">
            <?= number_format($review_stats['avg_rating'] ?? 0, 1); ?> <span class="star">★</span>
            <p>Total <?= $review_stats['total_reviews'] ?? 0; ?> review(s)</p>
        </div>
        <div class="star-ratings">
            <div>★ ★ ★ ★ ★ <span><?= $review_stats['five_star'] ?? 0; ?></span></div>
            <div>★ ★ ★ ★ <span><?= $review_stats['four_star'] ?? 0; ?></span></div>
            <div>★ ★ ★ <span><?= $review_stats['three_star'] ?? 0; ?></span></div>
            <div>★ ★ <span><?= $review_stats['two_star'] ?? 0; ?></span></div>
            <div>★ <span><?= $review_stats['one_star'] ?? 0; ?></span></div>
        </div>
    </div>
</section>
</form>


<?php
// Filtering by rating
$rating_filter = req('rating', 'all');  // Fetch the 'rating' from the URL, default to 'all'
$show_my_reviews = req('my_reviews', 'no'); // Fetch the 'my_reviews' parameter from the URL, default to 'no'

// Pagination setup
$page = req('page', 1);
$limit = 5; // Limit the number of reviews per page
$offset = ($page - 1) * $limit;

// Adjust SQL query to filter by rating if necessary
$filter_sql = '';
$params = [$product_id];

// Add filtering based on the selected rating
if ($rating_filter !== 'all') {
    $filter_sql .= " AND r.rating = ?";
    $params[] = $rating_filter;
}

// Add filtering to show only user's reviews
if ($show_my_reviews === 'yes' && isset($_user->id)) {
    $filter_sql .= " AND r.member_id = ?";
    $params[] = $_user->id;
}

// Fetch total count of filtered reviews for pagination
$reviews_count_query = $_db->prepare("
    SELECT COUNT(*) as total_reviews
    FROM review r
    WHERE r.product_id = ? $filter_sql
");
$reviews_count_query->execute($params);
$total_reviews = $reviews_count_query->fetchColumn();

// Fetch filtered reviews for the current page
$reviews_query = $_db->prepare("
    SELECT r.*, u.name AS user_name, u.image
    FROM review r 
    INNER JOIN user u ON r.member_id = u.id 
    WHERE r.product_id = ? $filter_sql
    ORDER BY r.date DESC
    LIMIT $limit OFFSET $offset
");
$reviews_query->execute($params);
$reviews = $reviews_query->fetchAll(PDO::FETCH_ASSOC);

// Calculate total pages
$total_pages = ceil($total_reviews / $limit);
?>

<!-- HTML for filtering and pagination -->
<section class="reviews-container">
    <!-- If the user has a completed order, show the Add Review button -->
    <?php if ($can_add_review): ?>
      
      <a href="add_review.php?product_id=<?= htmlspecialchars($product_id); ?>" class="btn add-review-btn">Add Review</a>

  <?php else: ?>
      <p><b>You can only leave a review if you have completed an order for this product.</b></p><br><br>
  <?php endif; ?>


    <h2>User's Reviews</h2>

    <!-- Review Filters -->
    <div class="review-filters">
        <a href="?product_id=<?= $product_id ?>&rating=all" class="<?= ($rating_filter == 'all') ? 'active' : '' ?>">All Reviews</a>
        <a href="?product_id=<?= $product_id ?>&rating=5" class="<?= ($rating_filter == '5') ? 'active' : '' ?>">5 Stars</a>
        <a href="?product_id=<?= $product_id ?>&rating=4" class="<?= ($rating_filter == '4') ? 'active' : '' ?>">4 Stars</a>
        <a href="?product_id=<?= $product_id ?>&rating=3" class="<?= ($rating_filter == '3') ? 'active' : '' ?>">3 Stars</a>
        <a href="?product_id=<?= $product_id ?>&rating=2" class="<?= ($rating_filter == '2') ? 'active' : '' ?>">2 Stars</a>
        <a href="?product_id=<?= $product_id ?>&rating=1" class="<?= ($rating_filter == '1') ? 'active' : '' ?>">1 Star</a>

        <!-- Show My Reviews Filter (only for logged-in users) -->
        <?php if (isset($_user->id)): ?>
            <a href="?product_id=<?= $product_id ?>&rating=<?= $rating_filter ?>&my_reviews=<?= ($show_my_reviews === 'yes') ? 'no' : 'yes' ?>" 
               class="<?= ($show_my_reviews === 'yes') ? 'active' : '' ?>">
               <?= ($show_my_reviews === 'yes') ? 'Show All Reviews' : 'Show My Reviews' ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- List Reviews -->
    <?php if (count($reviews) > 0): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-item">
                <div class="review-left">
                    <img src="/user_img/<?= $review['image'] ?>" alt="User Image" class="user-avatar">
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
                    <div class="user-rating"><?= str_repeat('★', $review['rating']); ?> (<?= htmlspecialchars($review['rating']); ?>)</div>
                    
                    <!-- Show Edit/Delete buttons only for the logged-in user's reviews -->
                    <?php if (isset($_user->id) && $_user->id == $review['member_id']): ?>
                        <a href="edit_review.php?review_id=<?= htmlspecialchars($review['id']); ?>" class="edit-btn">Edit</a>
                        <a href="delete_review.php?review_id=<?= htmlspecialchars($review['id']); ?>" class="delete-btn">Delete</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews found.</p>
    <?php endif; ?>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($total_pages > 1): ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?product_id=<?= $product_id ?>&rating=<?= $rating_filter ?>&my_reviews=<?= $show_my_reviews ?>&page=<?= $i ?>" class="<?= ($page == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'components/footer.php'; ?>


<script>
document.querySelectorAll('.flex-category-like input[type="checkbox"]').forEach(checkbox => {
   checkbox.addEventListener('change', function() {
       const label = this.nextElementSibling;
       const productId = this.id.split('_')[1]; // Extract product ID from checkbox ID

       // Temporarily disable the checkbox while the request is processed
       this.disabled = true;

       var isFavorite = this.checked;
       toggleFavorite(productId, isFavorite, this, label);
   });
});

function toggleFavorite(productId, isFavorite, checkbox, label) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/favorite_toggle.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText;

            if (response === 'User not logged in') {
                checkbox.checked = false;  // Uncheck the box
                window.location.href = 'login.php';  // Redirect to login page
            } else {
                label.classList.toggle('liked', checkbox.checked);
                updateFavoritesCount();  // Call the function to update the favorite count
            }

            checkbox.disabled = false;  // Re-enable the checkbox after the request
        }
    };

    xhr.send("product_id=" + productId + "&favorite=" + (isFavorite ? 1 : 0));
}

// Function to update the favorites count
function updateFavoritesCount() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "ajax/get_favorites_count.php", true);

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Update the favorite count in the header
            document.querySelector('.header .icons a[href="favorites.php"] span').innerText = `(${xhr.responseText})`;
        }
    };

    xhr.send();
}
</script>


<!-- custom js file link  -->
<script src="js/script.js"></script>



</body>
</html>
