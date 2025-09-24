<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


    
// Error handling for database operations
if($_user){
// Count cart items
$count_cart_items = 0;
$count_cart_items = $_db->prepare("SELECT * FROM `cart` WHERE member_id = ?");
$count_cart_items->execute([$_user->id]);
$total_cart_items = $count_cart_items->rowCount();

$count_favorites_items = 0;
$count_favorites_items = $_db->prepare("SELECT * FROM `favorites` WHERE member_id = ?");
$count_favorites_items->execute([$_user->id]);
$total_favorites_items = $count_favorites_items->rowCount();
}else{
    $total_cart_items=0; 
    $total_favorites_items=0;
}
    


?>

    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>
    <div id="error"><?= temp('error') ?></div>
    <div id="success"><?= temp('success') ?></div>

<header class="header">
    <section class="flex">
        <a href="index.php" class="logo">Baby Shark Book Shop</a>
        <nav class="navbar">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="menu.php">Menu</a>
            <a href="orders.php">Orders</a>
            <a href="contact.php">Contact</a>
            <a href="real-time-chat.php">Real-Time-Chat</a> <!-- admin_id=1 for example -->

        </nav>
        <div class="icons" style="display: inline;">
         <!--   <a href="search.php"><i class="fas fa-search"><img src="img/search.png" style="width:16px;"></i></a> -->
            <a href="favorites.php"><i class="fas fa-search"><img src="img/heart.png" style="width:16px;"></i><span>( <?= $total_favorites_items ?> )</span></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"><img src="img/shopping-cart.png" style="width:16px;"></i><span>( <?= $total_cart_items ?> )</span></a>
            <div id="user-btn" class="fas fa-user"style="display: inline-block;"><img src="img/user.png" style="width:16px;"></div>
            <div id="menu-btn" class="fas fa-bars"><img src="img/menu.png" style="width:16px;"></div>
        </div>
        <div class="profile">
            <?php if($_user) : ?>
                <?php $_user->name ?>
                <p class="name"><?= $_user->name ?></p>
                <div class="flex">
                    <a href="profile.php" class="btn">Profile</a>
                    <a href="components/user_logout.php" onclick="return confirm('Logout from this website?');" class="delete-btn">Logout</a>
                </div>
            <?php else : ?>
                <p class="name">Please login first!</p>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </div>
    </section>
</header>
