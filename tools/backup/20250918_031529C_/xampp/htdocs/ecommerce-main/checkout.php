<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'components/_base.php';
$_title="Chckout";
auth("Member");

// Fetch user data
$user = $_db->prepare("SELECT * FROM user WHERE id = ?");
$user->execute([$_user->id]);
$fetch_profile = $user->fetch(PDO::FETCH_ASSOC);

$total_product = 0;
$grand_total = 0;
$_err = []; // Initialize the error array
$method = ''; // Initialize method
$show_credit_card_info = false;
$stock_errors = []; // Initialize stock errors array

// Check stock availability function
function check_stock($product_id, $quantity) {
    global $_db;
    $stmt = $_db->prepare("SELECT stock FROM product WHERE id = ?");
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();
    return $stock >= $quantity;
}
function get_stock($product_id) {
    global $_db;
    $stmt = $_db->prepare("SELECT stock FROM product WHERE id = ?");
    $stmt->execute([$product_id]);
    $stock = $stmt->fetchColumn();
    return $stock;
}

// Validate and process the order
if (is_post()) {
    // Get POST data
    $address = post('address');
    $method = post('method');
    $account_number = post('account_number', '');
    $cvv = post('cvv', '');
    $expiry_date = post('expiry_date', '');

    // Validate address
    if ($address == '') {
        $_err['address'] = 'Please provide an address.';
    }

    // Validate payment method
    if ($method == 'credit card') {
        $show_credit_card_info = true;

        if (strlen($account_number) != 16 || !ctype_digit($account_number)) {
            $_err['account_number'] = 'Invalid card number. It must be a 16-digit number.';
        }
        if (strlen($cvv) != 3 || !ctype_digit($cvv)) {
            $_err['cvv'] = 'Invalid CVV. It must be a 3-digit number.';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $expiry_date)) {
            $_err['expiry_date'] = 'Invalid expiry date. Use MM/YY format.';
        }
    }

    if (empty($_err)) {
        // Fetch cart items
        $select_cart = $_db->prepare("SELECT c.id, c.quantity, p.id AS product_id, p.name, p.price FROM `cart` c INNER JOIN product p ON c.product_id = p.id WHERE member_id = ?");
        $select_cart->execute([$_user->id]);
        $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

        // Check stock availability
        foreach ($cart_items as $item) {
            if (!check_stock($item['product_id'], $item['quantity'])) {
                $stock_errors[] = "Not enough stock for product: " . $item['name']." (Available Stock: ".get_stock($item['product_id']) ." )";
                temp("error","Not enough stock for product(s)");
            } else {
                $grand_total += $item['price'] * $item['quantity'];
                $total_product += $item['quantity'];
            }
        }

        if (count($stock_errors) > 0) {
            $_err['stock'] = implode(', ', $stock_errors);
        } else {
            // Insert order into `orders` table
            $payment_status = $method == 'cash on delivery' ? 'unpaid' : 'paid';
            $current_date = date('Y-m-d');
            $current_time = date('H:i:s');
            $insert_order = $_db->prepare("INSERT INTO `orders` (member_id, date, time, total_product, total_price, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_order->execute([$_user->id, $current_date, $current_time, $total_product, $grand_total, $address, 'pending']);
            $order_id = $_db->lastInsertId();

            // Insert each product in cart into `orderproduct` table
            foreach ($cart_items as $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $insert_order_product = $_db->prepare("INSERT INTO `orderproduct` (ordersid, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
                $insert_order_product->execute([$order_id, $item['product_id'], $item['quantity'], $subtotal]);

                // Reduce stock quantity
                $update_stock = $_db->prepare("UPDATE product SET stock = stock - ? WHERE id = ?");
                $update_stock->execute([$item['quantity'], $item['product_id']]);
            }

            // Prepare data for payment table
            $bank_account = $method == 'credit card' ? $account_number : '';
            $cvv_code = $method == 'credit card' ? $cvv : '';
            $expiry_date_formatted = $method == 'credit card' ? date('Y-m-d', strtotime('01-' . $expiry_date)) : '0000-00-00';

            // Insert payment into `payment` table
            $insert_payment = $_db->prepare("INSERT INTO `payment` (orders_id, amount, payment_method, bankAccount, CVV, expired_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert_payment->execute([$order_id, $grand_total, $method, $bank_account, $cvv_code, $expiry_date_formatted, $payment_status]);

            // Clear cart
            $delete_cart = $_db->prepare("DELETE FROM `cart` WHERE member_id = ?");
            $delete_cart->execute([$_user->id]);

            // Send email if payment is successful
            if ($payment_status == 'paid') {
                try {
                    $mail = get_mail();
                    $mail->addAddress($_user->email, $_user->name);
                    $mail->Subject = 'Order Payment Successful';
                    $mail->Body = 'Thank you for your payment. Your order has been placed successfully.';

                    // Add the download receipt link
                    $receipt_link = base("send_receipt.php?order_id=" . $order_id);
                    $mail->Body .= "\n\nYou can download your receipt here: " . $receipt_link;

                    $mail->send();
                } catch (Exception $e) {
                    $_err['email'] = 'Failed to send email.';
                }
            }

            // Redirect to thank you page
            redirect('thank_you.php?status=' . $payment_status);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Checkout</h3>
   <p><a href="index.php">Home</a> <span> / Checkout</span></p>
</div>

<section class="checkout">
   <h1 class="title">Order Summary</h1>
   <form action="" method="post">

   <div class="user-info">
     <!-- Display Stock Errors -->
     <?php if (!empty($_err['stock'])): ?>
            <div class="error">
                <h3>Stock Errors</h3>
                <p><?= htmlspecialchars($_err['stock']); ?></p>
            </div>
         <?php endif; ?>
   </div>
   <br>
   <br>
   
      <div class="cart-items">
         <h3>Cart Items</h3>
         <?php
            $grand_total = 0;
            $select_cart = $_db->prepare("SELECT c.id, c.quantity, p.id AS product_id, p.name, p.image, p.price FROM `cart` c INNER JOIN product p ON c.product_id = p.id WHERE member_id = ?");
            $select_cart->execute([$_user->id]);
            $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

            if (count($cart_items) > 0) {
               foreach ($cart_items as $fetch_cart) {
                  $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                  $grand_total += $sub_total;
                  echo "<p><span class='name'>{$fetch_cart['name']}</span><span class='price'>RM {$fetch_cart['price']} x {$fetch_cart['quantity']} = RM {$sub_total}</span></p>";
               }
            } else {
               echo '<p class="empty">Your cart is empty</p>';
            }
         ?>
         <p class="grand-total"><span class="name">Grand Total :</span><span class="price">RM <?= $grand_total; ?></span></p>
      </div>

      <div class="user-info">
         <h3>Your Info</h3>
         <p><i class="fas fa-user"><img src="img/user.png"></i><span><?= encode($_user->name); ?></span></p>
         <p><i class="fas fa-phone"><img src="img/phone.png" style="height:23.2px;width:16px;"></i><span><?= encode($_user->telephone); ?></span></p>
         <p><i class="fas fa-envelope"><img src="img/email.png" style="height:23.2px;width:16px;"></i><span><?= encode($_user->email); ?></span></p>

         <!-- Address Input -->
         <h3>Delivery Address</h3>
         <?= html_textarea2('address', '', $fetch_profile['address']); ?>
         <?= err('address'); ?>

         <!-- Payment Method -->
         <h3>Payment Method</h3>
         <select id="payment-method" name="method" class="box" required>
            <option value="" disabled <?= $method ? '' : 'selected'; ?>>Select payment method</option>
            <option value="cash on delivery" <?= $method == 'cash on delivery' ? 'selected' : ''; ?>>Cash on Delivery</option>
            <option value="credit card" <?= $method == 'credit card' ? 'selected' : ''; ?>>Credit Card</option>
         </select>
         <?= err('method'); ?>

         <!-- Credit Card Information -->
         <div id="credit-card-info" style="display: none;">
            <h3>Credit Card Information</h3>
            <label for="account-number">Account Number:</label>
            <?php html_text2('account_number', ''); ?>
            <?= err('account_number'); ?><br>

            <label for="cvv">CVV:</label>
            <?php html_text2('cvv', ''); ?>
            <?= err('cvv'); ?><br>

            <label for="expiry-date">Expiry Date (MM/YY):</label>
            <?php html_text2('expiry_date', ''); ?>
            <?= err('expiry_date'); ?>
         </div>

        

         <input type="submit" value="Place Order" class="btn" name="submit"><br>
         <a style="background-color:yellowgreen;" href="cart.php" class="btn" >Back to Cart</a>
      </div>
   </form>
</section>

<script>
document.getElementById('payment-method').addEventListener('change', function() {
    var creditCardInfo = document.getElementById('credit-card-info');
    if (this.value === 'credit card') {
        creditCardInfo.style.display = 'block';
    } else {
        creditCardInfo.style.display = 'none';
    }
});

// On page load, check if we need to show the credit card info
<?php if ($show_credit_card_info): ?>
    document.getElementById('credit-card-info').style.display = 'block';
<?php endif; ?>
</script>

<?php include 'components/footer.php'; ?>
</body>
</html>
