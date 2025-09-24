<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);


if(isset($_POST['add_to_cart'])){
   auth("Member");

      $product_id = req('product_id');
      $qty = req('qty');

      $check_cart_numbers = $_db->prepare("SELECT * FROM `cart` WHERE product_id = ? AND member_id = ?");
      $check_cart_numbers->execute([$product_id, $_user->id]);

      if($check_cart_numbers->rowCount() > 0){
         temp('info', 'Already added to cart');

      }else{
         $insert_cart = $_db->prepare("INSERT INTO `cart`(member_id, product_id,quantity) VALUES(?,?,?)");
         $insert_cart->execute([$_user->id, $product_id,$qty]);
         temp('success', 'Added to cart successfully!');
         
      }

   }


?>