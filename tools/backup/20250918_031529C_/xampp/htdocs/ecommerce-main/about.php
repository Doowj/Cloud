<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'components/_base.php';

$_title="About Us";

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>

    <!--  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
      -->
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<!-- header section starts  -->
<?php 


include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>about us</h3>
   <p><a href="index.php">Home</a> <span> / about</span></p>
</div>

<!-- about section starts  -->

<section class="about">

   <div class="row">

      <div class="image">
         <img src="img/shop.jpeg" alt="">
      </div>

      <div class="content" >
         <h3>Welcome Message from the Branch Head</h3>
         
         <p style="text-align:justify">
         Since opening our doors in 2007, BookXcess has reinvigorated and redefined bookselling in Malaysia and beyond, offering an unparalleled range of affordably priced books - from classic novels to children’s pop-ups to bestselling self-help titles. 

         </p>
         <p style="text-align:justify">
Our mission is to create, inspire and empower readers and to inculcate the reading habit by making books accessible to and affordable for all. We are industry icons, set on reinventing the book industry. 
</p>

<p style="text-align:justify">
Dynamic, creative and innovative, and with a rapidly growing network of ground-breaking and inspirational stores, we deliver millions of books to readers globally through our seamless digital and retail experience.     
      </p>
         
         

         <p style="text-align:left">
         Owner<br>
         Baby Shark Book Shop <br>
         Wei Jie<br>

         </p>
         

         <a href="menu.php" class="btn">our menu</a>
      </div>

   </div>

</section>

<!-- about section ends -->

<!-- steps section starts  -->

<section class="steps">

   <h1 class="title">Why purchase graduate products from us?</h1>

   <div class="box-container">

      <div class="box">
         <img src="img/step-1.png" alt="">
         <h3>Product Diversification</h3>
         <p>We support many type of Graduate products.</p>
      </div>

      <div class="box">
         <img src="img/quality.png" alt="">
         <h3>Quality Product</h3>
         <p>We offer our customers high-quality, effective product support .</p>
      </div>

      <div class="box">
         <img src="img/bestprice.png" alt="">
         <h3>Best Price</h3>
         <p>The lowest price that you can buy something for.</p>
      </div>

   </div>

</section>





<!-- footer section starts  -->
<?php 

include 'components/footer.php'; ?>
<!-- footer section ends -->=






<script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>