<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';
include 'components/add_cart.php';
$_title = 'Menu';

require_once 'lib/menuPager.php'; // Include the pager library

// Check if the user is logged in
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

$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
$selected_price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$selected_rating = isset($_GET['rating']) ? $_GET['rating'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'default';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';  // Get search term

// Determine price range based on selection
switch ($selected_price_range) {
    case '25':
        $min_price = 0;
        $max_price = 25;
        break;
    case '50':
        $min_price = 0;
        $max_price = 50;
        break;
    case '100':
        $min_price = 0;
        $max_price = 100;
        break;
    case '200':
        $min_price = 0;
        $max_price = 200;
        break;
    default:
        $min_price = 0;
        $max_price = 10000;  // Default to a high max price when "All Prices" is selected
        break;
}

// SQL query to filter by category, price, rating, and search
$query = "SELECT p.id, p.name, p.price, p.image, p.category_id, c.name as categoryName, 
COALESCE(AVG(r.rating), 1) as average_rating 
FROM product p 
INNER JOIN category c ON p.category_id = c.id 
LEFT JOIN review r ON p.id = r.product_id 
WHERE p.price BETWEEN ? AND ?";

$params = [$min_price, $max_price];
if ($category_filter !== 'all') {
    $query .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

// Add search condition if search term is provided
if (!empty($search_term)) {
    $query .= " AND p.name LIKE ?";
    $params[] = '%' . $search_term . '%';
}

// Add rating filter only if the user selected a rating
if (!empty($selected_rating)) {
    $query .= " GROUP BY p.id HAVING ROUND(average_rating) = ?";
    $params[] = $selected_rating;  // Ensure we're selecting the exact rating
} else {
    $query .= " GROUP BY p.id";
}

// Apply sorting by price, rating, or default sorting
switch ($sort_by) {
    case 'price_asc':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'rating_asc':
        $query .= " ORDER BY average_rating ASC";  // Sort by rating (low to high)
        break;
    case 'rating_desc':
        $query .= " ORDER BY average_rating DESC";  // Sort by rating (high to low)
        break;
    default:
        $query .= " ORDER BY p.id DESC";
        break;
}

// Instantiate SimplePager for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$p = new SimplePager($query, $params, 6, $page); // 6 items per page
$result = $p->result;

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="/js/member.js"></script>

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>our menu</h3>
   <p><a href="index.php">Home</a> <span> / menu</span></p>
</div>


<div class="container">
  <!-- Filter Panel -->
  <aside class="filter-panel">
      <h3>Filter by:</h3>
      
      <!-- Combined Filter Form -->
      <form action="menu.php" method="GET">
        
        <!-- Categories Filter -->
        <div class="filter-group">
            <h2>Category</h2>
            <label for="category"><h4>Filter by category:</h4></label>
            <select name="category" id="category">
                <option value="all" <?= $category_filter == 'all' ? 'selected' : ''; ?>>All</option>
                <?php
                // Fetch all categories from the database
                $categories_stmt = $_db->query("SELECT * FROM category");
                $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($categories as $category) {
                    $category_id = $category['id'];
                    $category_name = $category['name'];
                    // Check if the category is the currently selected one
                    $selected = $category_filter == $category_id ? 'selected' : '';
                    echo "<option value='$category_id' $selected>$category_name</option>";
                }
                ?>
            </select>
        </div>

         <!-- Price Filter -->
        <div class="filter-group">
            <h2>Price</h2>
            <label for="price-range"><h4>Your budget is?</h4></label>
            <select name="price_range" id="price-range">
                <option value="" <?= empty($selected_price_range) ? 'selected' : ''; ?>>All Prices</option> <!-- Add an "All Prices" option -->
                <option value="25" <?= $selected_price_range == '25' ? 'selected' : ''; ?>>Below RM25</option>
                <option value="50" <?= $selected_price_range == '50' ? 'selected' : ''; ?>>Below RM50</option>
                <option value="100" <?= $selected_price_range == '100' ? 'selected' : ''; ?>>Below RM100</option>
                <option value="200" <?= $selected_price_range == '200' ? 'selected' : ''; ?>>Below RM200</option>
            </select>
        </div>

            <!-- Rating Filter -->
            <div class="filter-group">
                <h2>Rating</h2>
                <label for="rating-range"><h4>Select a Rating:</h4></label>
                <select name="rating" id="rating-range">
                    <option value="">All Ratings</option> <!-- Add an option for all ratings -->
                    <option value="1" <?= $selected_rating == '1' ? 'selected' : ''; ?>>1 star</option>
                    <option value="2" <?= $selected_rating == '2' ? 'selected' : ''; ?>>2 stars</option>
                    <option value="3" <?= $selected_rating == '3' ? 'selected' : ''; ?>>3 stars</option>
                    <option value="4" <?= $selected_rating == '4' ? 'selected' : ''; ?>>4 stars</option>
                    <option value="5" <?= $selected_rating == '5' ? 'selected' : ''; ?>>5 stars</option>
                </select>
            </div>

     <!-- Search Filter -->
     <div class="filter-group">
            <h2>Search</h2>
            <label for="search"><h4>Enter a product name:</h4></label>
            <?php $searchValue = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
                 echo html_search2('search', 'class="search-input"', $searchValue);  ?>
        </div>

    <!-- Apply Button -->
    <button class="btn-submit" type="submit">Apply</button>
     <!-- Sorting Dropdown -->
</form>
<br>
<br>
<br>

    <div class="filter-group">
        <div class="sorting-container">
            <form action="menu.php" method="GET">
                <input type="hidden" name="category" value="<?= $category_filter; ?>">
                <input type="hidden" name="price_range" value="<?= $selected_price_range; ?>">
                <input type="hidden" name="rating" value="<?= $selected_rating; ?>">
                <input type="hidden" name="search" value="<?= $searchValue; ?>">

                <label for="sort_by"><h3>Sort by:</h3></label>
                <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                    <option value="default" <?= $sort_by == 'default' ? 'selected' : ''; ?>>Default</option>
                    <option value="price_asc" <?= $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <!-- Add sorting by rating -->
                    <option value="rating_asc" <?= $sort_by == 'rating_asc' ? 'selected' : ''; ?>>Rating: Low to High</option>
                    <option value="rating_desc" <?= $sort_by == 'rating_desc' ? 'selected' : ''; ?>>Rating: High to Low</option>
                </select>
            </form>
        </div>
    </div>
   </aside>

  


   
<section class="products">
<h1 class="title">All Books</h1>

<div class="box-container">

   <?php
   if (count($result) > 0) {
    foreach ($result as $fetch_products) {
        $product_id = $fetch_products->id;
        $quantity = isset($cart_quantities[$product_id]) ? $cart_quantities[$product_id] : 0;
        $is_favorited = in_array($product_id, $favorite_items);  // Check if the product is in favorites
        $average_rating = round($fetch_products->average_rating, 1); // Ensure one decimal place
    ?>
        <form action="" method="post" class="box">
            <?= html_hidden2("product_id", '', $fetch_products->id); ?>
            <?= html_hidden2("name", '', $fetch_products->name); ?>
            <?= html_hidden2("price", '', $fetch_products->price); ?>
            <?= html_hidden2("image", '', $fetch_products->image); ?>
    
            <a href="quick_view.php?product_id=<?= $fetch_products->id; ?>" class="fas fa-eye"><img src="img/eye.png" style="height:43px;"></a>
            <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"><img src="img/shopping-cart.png" style="height:43px;"></button>
            <img src="uploaded_img/<?= $fetch_products->image; ?>" alt="">
            <div class="flex-category-like">
                <a href="menu.php?category=<?= $fetch_products->category_id; ?>" class="cat"><?= $fetch_products->categoryName; ?></a>
                <input type="checkbox" id="like_<?= $product_id; ?>" class="sr-only" <?= $is_favorited ? 'checked' : ''; ?> onchange="toggleFavorite(<?= $product_id; ?>, this.checked)">
                <label for="like_<?= $product_id; ?>" aria-hidden="true" class="like-label <?= $is_favorited ? 'liked' : ''; ?>">❤</label>
            </div>
    
            <div class="name"><?= $fetch_products->name; ?></div>
            <div class="rating" style=" display: inline-block; margin-left: 5px;">
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
            <div class="flex">
                <div class="price"><span>RM</span><?= $fetch_products->price; ?></div>
                <input type="number" name="qty" min="1" max="99" maxlength="2" value="<?= $quantity ?>" <?= $quantity > 0 ? 'readonly' : '' ?> />
                <?= $quantity > 0 ? '<span class="checkmark">✅</span>' : '' ?>
            </div>
            <button type="submit" name="add_to_cart" class="cart-btn">add to cart</button>
        </form>
   
   <?php
      }
   } else {
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

</div>
<?php
// Build the query string for filters and sorting
$filterParams = http_build_query([
    'category' => $category_filter,
    'price_range' => $selected_price_range,
    'rating' => $selected_rating,
    'search' => $searchValue,
    'sort_by' => $sort_by, // Include sorting
]);

// Instantiate SimplePager for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$p = new SimplePager($query, $params, 6, $page); // 6 items per page

// Pass the filter and sorting parameters to the html method
?>

<div class="pagination">
    <?= $p->html($filterParams) ?>  <!-- Pass filter parameters here -->
</div>


</section>
</div>

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

//function for filter the price
document.addEventListener('DOMContentLoaded', function() {
    var minPrice = document.getElementById('min-price');
    var maxPrice = document.getElementById('max-price');

    function validatePrices() {
        if (parseFloat(minPrice.value) > parseFloat(maxPrice.value)) {
            maxPrice.value = minPrice.value;
        }
    }

    minPrice.addEventListener('change', validatePrices);
    maxPrice.addEventListener('change', validatePrices);
});
</script>


<!-- custom js file link  -->
<script src="js/script.js"></script>



</body>
</html>
