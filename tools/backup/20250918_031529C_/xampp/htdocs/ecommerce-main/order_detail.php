<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php'; 
auth("Member");
$_title="Order_detail.php";

// Check if `order_id` is provided
if (!isset($_GET['order_id'])) {
    redirect('/orders.php'); // Redirect to the orders page if `order_id` is not present
}

$order_id = $_GET['order_id'];

// Fetch the order details and ensure it belongs to the logged-in user
$order_stmt = $_db->prepare("
    SELECT o.*, p.payment_method, p.status AS payment_status, p.bankAccount 
    FROM orders o
    LEFT JOIN payment p ON o.id = p.orders_id
    WHERE o.id = ? AND o.member_id = ?
");
$order_stmt->execute([$order_id, $_user->id]);
$order = $order_stmt->fetch(PDO::FETCH_ASSOC);

// If the order does not exist or does not belong to the user, redirect to orders page
if (!$order) {
    redirect('/orders.php'); 
}

// Fetch the products in this order
$order_products_stmt = $_db->prepare("
    SELECT p.name, op.quantity, p.price, op.subtotal 
    FROM orderproduct op
    INNER JOIN product p ON op.product_id = p.id
    WHERE op.ordersid = ?
");
$order_products_stmt->execute([$order['id']]);
$products = $order_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine payment method (show last 4 digits if credit card)
$payment_method = $order['payment_method'] === 'credit card' ? "Credit Card ****" . substr($order['bankAccount'], -4) : ucfirst($order['payment_method']);
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

<section>
<div class="order-detail-container">
        <h2>Order Details (Order #<?php echo htmlspecialchars($order['id']); ?>)</h2>
        
        <!-- Order Information -->
        <div class="order-info">
            <h3>Order Information</h3>
            <table>
                <tr>
                    <th>Order Date:</th>
                    <td><?php echo date('m/d/Y, H:i A', strtotime($order['date'] . ' ' . $order['time'])); ?></td>
                </tr>
                <tr>
                    <th>Order Status:</th>
                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                </tr>
                <tr>
                    <th>Payment Method:</th>
                    <td><?php echo htmlspecialchars($payment_method); ?></td>
                </tr>
                <tr>
                    <th>Payment Status:</th>
                    <td><?php echo htmlspecialchars($order['payment_status']); ?></td>
                </tr>
                <tr>
                    <th>Total Amount:</th>
                    <td>RM <?php echo number_format($order['total_price'], 2); ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Product Information -->
        <h3>Product Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price (RM)</th>
                    <th>Quantity</th>
                    <th>Subtotal (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo number_format($product['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Back to Orders Link -->
        <div style="margin-top: 20px;">
             <a href="orders.php" class="btn">Back to Orders</a>
        </div>
    </div>


</section>
   


    <?php include 'components/footer.php'; ?>


<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>

