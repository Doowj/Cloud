<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>

    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>
    <div id="error"><?= temp('error') ?></div>
    <div id="success"><?= temp('success') ?></div>


<header class="header">

   <section class="flex">

      <a href="admin_dashboard.php" class="logo">Admin<span>Panel</span></a>

      <nav class="navbar">
         <a href="admin_dashboard.php">Home</a>
         <a href="products.php">products</a>
         <a href="category.php">Category</a>
         <a href="admin_orders.php">Orders</a>
         <a href="admin_member_accounts.php">Member</a>
         <a href="admin-chat.php">Real-Time-Chat</a>
         <div class="dropdown">
            <a class="dropbtn"  href="sales_report.php">Reports</a>
            <div class="dropdown-content">
               <a href="sales_report.php">Sales Report</a>
               <a href="top10_products.php">Top 10 Products Report</a>

            </div>
         </div>

      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"><img src="../img/menu.png"></div>
         <div id="user-btn" class="fas fa-user" ><img src="../img/user.png"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $_db->prepare("SELECT * FROM `user` WHERE id = ?");
            $select_profile->execute([$_user->id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= $fetch_profile['name']; ?></p>
         <a href="../admin/admin_profile.php" class="btn">profile</a>
         <a href="../components/admin_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
      </div>

   </section>

</header>