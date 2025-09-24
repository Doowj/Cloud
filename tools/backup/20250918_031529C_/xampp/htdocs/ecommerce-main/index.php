<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';
include 'components/add_cart.php';
$_title = 'Home';

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

</head>
<body>

<?php include 'components/user_header.php'; ?>

<section class="hero">
   <div class="slideshow-container">

      <div class="mySlides fade">
         <div class="numbertext">1 / 3</div>
            <img src="img/image3.png" style="width: 1000px;height:400px;">
         <div class="text">SPM Learning Book</div>
      </div>

      <div class="mySlides fade">
         <div class="numbertext">2 / 3</div>
         <img src="img/image1.png" style="width: 1000px;height:400px;">
         <div class="text">Novel Book</div>
      </div>

      <div class="mySlides fade">
         <div class="numbertext">3 / 3</div>
            <img src="img/image2.png" style="width: 1000px;height:400px;">
         <div class="text">Comic Book</div>
      </div>

      <a class="prev" onclick="plusSlides(-1)"> ❮ </a>
      <a class="next" onclick="plusSlides(1)"> ❯ </a>

   </div>

   <br>

   <div style="text-align:center">
   <span class="dot" onclick="currentSlide(1)"></span> 
   <span class="dot" onclick="currentSlide(2)"></span> 
   <span class="dot" onclick="currentSlide(3)"></span> 
   </div>

</section>

<?php
$select_products = $_db->prepare("
    SELECT p.id, p.name, p.price, p.image, p.category_id, c.name AS categoryName, 
           COALESCE(AVG(r.rating), 1) AS average_rating 
    FROM product p 
    INNER JOIN category c ON p.category_id = c.id 
    LEFT JOIN review r ON p.id = r.product_id 
    GROUP BY p.id 
    ORDER BY p.id DESC 
    LIMIT 6
");
$select_products->execute();
$result = $select_products->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="products">
<h1 class="title">latest dishes</h1>

<div class="box-container">

   <?php
   if (count($result) > 0) {
      foreach ($result as $fetch_products) {
         $product_id = $fetch_products['id'];
         $quantity = isset($cart_quantities[$product_id]) ? $cart_quantities[$product_id] : 0;
         $is_favorited = in_array($product_id, $favorite_items);  // Check if the product is in favorites
         $average_rating = round($fetch_products['average_rating'], 1); // Ensure one decimal place
   ?>
   
   <form action="" method="post" class="box">
      <?= html_hidden2("product_id", '', $fetch_products['id']); ?>
      <?= html_hidden2("name", '', $fetch_products['name']); ?>
      <?= html_hidden2("price", '', $fetch_products['price']); ?>
      <?= html_hidden2("image", '', $fetch_products['image']); ?>

      <a href="quick_view.php?product_id=<?= $fetch_products['id']; ?>" class="fas fa-eye"><img src="img/eye.png" style="height:43px;"></a>
      <button type="submit" class="fas fa-shopping-cart" name="add_to_cart"><img src="img/shopping-cart.png" style="height:43px;"></button>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="flex-category-like">
            <a href="menu.php?category=<?= $fetch_products['category_id']; ?>" class="cat"><?= $fetch_products['categoryName']; ?></a>
            
            <input type="checkbox" id="like_<?= $product_id; ?>" class="sr-only" <?= $is_favorited ? 'checked' : ''; ?> onchange="toggleFavorite(<?= $product_id; ?>, this.checked)">
            <label for="like_<?= $product_id; ?>" aria-hidden="true" class="like-label <?= $is_favorited ? 'liked' : ''; ?>">❤</label>
         </div>

      <div class="name"><?= $fetch_products['name']; ?></div>
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
         <div class="price"><span>RM</span><?= $fetch_products['price']; ?></div>
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

<div class="more-btn">
   <a href="menu.php" class="btn">view all</a>
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
                window.location.href = '/login.php';  // Redirect to login page
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
