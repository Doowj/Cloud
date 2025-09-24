

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../components/_base.php';
auth('Admin');
$_title="Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
<?php include '../components/admin_header.php' ?>

<!-- admin dashboard section starts  -->

<section class="dashboard">

   <h1 class="heading">dashboard</h1>

   <div class="box-container">

   <div class="box">
      <h3>welcome!</h3>
      <p><?= $_user-> name ?></p>
      <a href="admin_profile.php" class="btn">Profile</a>
   </div>

   <div class="box">
      <?php
         $total_pendings = 0;
         $select_pendings = $_db->prepare("SELECT * FROM `orders` WHERE status = ?");
         $status_pending = 'pending'; // Set the status value
         $select_pendings->bindParam(1, $status_pending, PDO::PARAM_STR); // Bind the parameter
         $select_pendings->execute();
         while($fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC)){
            $total_pendings += $fetch_pendings['total_price'];
         }
      ?>
      <h3><span>RM</span><?= $total_pendings; ?><span>/-</span></h3>
      <p>total pendings</p>
      <a href="admin_orders.php" class="btn">see orders</a>
   </div>


   <div class="box">
      <?php
         $select_products = $_db->prepare("SELECT * FROM `product`");
         $select_products->execute();
         $numbers_of_products = $select_products->rowCount();
      ?>
      <h3><?= $numbers_of_products; ?></h3>
      <p>products added</p>
      <a href="products.php" class="btn">see products</a>
   </div>

   <div class="box">
      <?php
         $select_users = $_db->prepare("SELECT * FROM `user` WHERE role=?");
         $select_users->execute(["Member"]);
         $numbers_of_users = $select_users->rowCount();
      ?>
      <h3><?= $numbers_of_users; ?></h3>
      <p>users accounts</p>
      <a href="admin_member_accounts.php" class="btn">see users</a>
   </div>



   

   </div>

</section>

<!-- admin dashboard section ends -->









<!-- custom js file link  -->
<script src="../js/admin_script.js"></script>

</body>
</html>