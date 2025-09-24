<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'components/_base.php';

auth("Member");

// Handle delete request
if (isset($_POST['delete'])) {
    $cart_id = req('cart_id');
    $delete_cart_item = $_db->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$cart_id]);
    temp('success', 'Deleted the item from cart! successfully');
}

// Handle delete all request
if (isset($_POST['delete_all'])) {
    $delete_cart_item = $_db->prepare("DELETE FROM `cart` WHERE member_id = ?");
    $delete_cart_item->execute([$_user->id]);
    temp('success', 'Deleted all items from cart! successfully');
}

// Handle update quantity request
if (isset($_POST['update_qty'])) {
    $cart_id = req('cart_id');
    $qty = req('qty');
    if ($qty > 0) {
        $update_qty = $_db->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
        $update_qty->execute([$qty, $cart_id]);
        temp('success', 'Update quantity successfully');
    } else {
        temp('error', 'Quantity must be a positive integer!');
    }
}

// Search functionality
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

$grand_total = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>cart</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Shopping Cart</h3>
   <p><a href="index.php">Home</a> <span> / Cart</span></p>
</div>

<!-- shopping cart section starts  -->

<section class="products">
   <h1 class="title">Your Cart</h1>

   <!-- Search section -->
   <section class="search">
       <form action="" method="get">
       <?php
// Assuming $search_query is defined somewhere in your code
html_text2('search_query', 'placeholder="Search in cart..."', $search_query);
?>
<button type="submit" class="search-btn">Search</button>
       </form>
   </section>

   <div class="box-container">
      <?php
         $select_cart_query = "
             SELECT c.id, c.quantity, p.id as product_id, p.name, p.image, p.price
             FROM `cart` c
             INNER JOIN product p ON c.product_id = p.id
             WHERE c.member_id = ?
             AND p.name LIKE ?
         ";
         $select_cart = $_db->prepare($select_cart_query);
         $select_cart->execute([$_user->id, "%$search_query%"]);
         $result = $select_cart->fetchAll(PDO::FETCH_ASSOC);

         if (count($result) > 0) {
            foreach ($result as $fetch_cart) {
                $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                $grand_total += $sub_total;
      ?>

      <form action="" method="post" class="box">
         <?= html_hidden2('cart_id', '', $fetch_cart['id']); ?>
         <?= html_hidden2("product_id", '', $fetch_cart['product_id']); ?>

         <a href="quick_view.php?product_id=<?= $fetch_cart['product_id']; ?>" class="fas fa-eye"><img src="img/eye.png" style="height:43px;"></a>
         <button type="submit" class="fas fa-times" name="delete" onclick="return confirm('Delete this item?');"><img src="img/delete.png" style="height:43px;"></button>
         <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
         <div class="name"><?= $fetch_cart['name']; ?></div>
         <div class="flex">
            <div class="price"><span>RM</span><?= $fetch_cart['price']; ?></div>
            <?= html_number2("qty",1,99,1,'maxlength="2"',$fetch_cart['quantity']); ?>
            <button type="submit" class="fas fa-edit" name="update_qty"><img src="img/edit.png" style="height:45px;"></button>
         </div>
         <div class="sub-total"> Sub Total : <span>$<?= $sub_total; ?>/-</span> </div>
      </form>
      <?php
            }
         } else {
            echo '<p class="empty">Your cart is empty</p>';
         }
      ?>
   </div>

   <div class="cart-total">
      <p>Cart Total : <span>RM<?= $grand_total; ?></span></p>
      <a href="checkout.php" class="btn <?= ($grand_total > 0)?'':'disabled'; ?>">Proceed to Checkout</a>
   </div>

   <div class="more-btn">
      <form action="" method="post">
         <button type="submit" class="delete-btn <?= ($grand_total > 0)?'':'disabled'; ?>" name="delete_all" onclick="return confirm('Delete all from cart?');">Delete All</button>
      </form>
      <a href="menu.php" class="btn">Continue Shopping</a>
   </div>
</section>

<!-- shopping cart section ends -->

<!-- footer section starts  -->
<?php include 'components/footer.php'; ?>
<!-- footer section ends -->

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
