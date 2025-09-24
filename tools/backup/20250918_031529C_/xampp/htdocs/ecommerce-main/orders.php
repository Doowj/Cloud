<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php'; 
auth("Member");
$_title = 'All Orders';

// Check if a cancel request is made
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = $_POST['cancel_order_id'];

    // Fetch the order details to check the status and ownership
    $order_stmt = $_db->prepare("
        SELECT o.id, o.status, p.status AS payment_status, u.email
        FROM orders o
        LEFT JOIN payment p ON o.id = p.orders_id
        INNER JOIN user u ON o.member_id = u.id
        WHERE o.id = ? AND o.member_id = ?
    ");
    $order_stmt->execute([$cancel_order_id, $_user->id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if ($order && $order['status'] !== 'shipping' && $order['status'] !== 'completed') {
        // Fetch order products to update stock
        $order_products_stmt = $_db->prepare("
            SELECT product_id, quantity
            FROM orderproduct
            WHERE ordersid = ?
        ");
        $order_products_stmt->execute([$cancel_order_id]);
        $order_products = $order_products_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Update stock levels
        foreach ($order_products as $product) {
            $update_stock_stmt = $_db->prepare("
                UPDATE product 
                SET stock = stock + ? 
                WHERE id = ?
            ");
            $update_stock_stmt->execute([$product['quantity'], $product['product_id']]);
        }

        // Update the order status to 'cancelled'
        $update_stmt = $_db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $update_stmt->execute([$cancel_order_id]);

        // Send email if payment status is 'paid'
        if ($order['payment_status'] === 'paid') {
            $to = $order['email'];
            $subject = "Order #{$order['id']} Cancellation and Refund";
            $message = "Your order #{$order['id']} has been successfully cancelled. 
            The refund will be processed within 7 working days. 
            If you do not receive the refund, please contact us at +016-5576856 or email: doowj-am22@student.tarc.edu.my.";

            // Use PHPMailer to send the email
            $mail = get_mail();
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $message;

            try {
                $mail->send();
            } catch (Exception $e) {
                // Handle email sending error
                $_SESSION['error_message'] = "Order cancelled but failed to send email: " . $mail->ErrorInfo;
            }
        }

        // Display success message
        $_SESSION['success_message'] = "Order #{$cancel_order_id} has been cancelled successfully.";
        temp("success","The order has been cancelled successfully.");
        redirect('orders.php');
    }
}

// Fetch all orders for the logged-in user
$orders_stmt = $_db->prepare("
    SELECT o.id, o.date, o.time, 
           o.total_product,
           o.total_price, o.status AS order_status, 
           p.payment_method, p.status AS payment_status
    FROM orders o
    LEFT JOIN payment p ON o.id = p.orders_id
    WHERE o.member_id = ?
    ORDER BY o.id DESC
");
$orders_stmt->execute([$_user->id]);
$orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="css/style.css">
   <title><?= $_title?></title>
   <script>
       function confirmCancel(orderId) {
           if (confirm('Are you sure you want to cancel this order?')) {
               // Submit the form to cancel the order
               document.getElementById('cancelForm-' + orderId).submit();
           }
       }
   </script>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Orders</h3>
   <p><a href="index.php">Home</a> <span> / Orders</span></p>
</div>

<div class="orders-container">
    <h2>My Orders</h2>
    <?php 
    // Display success message if set
    if (isset($_SESSION['success_message'])) {
        echo "<p style='color: green;'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
        unset($_SESSION['success_message']); // Clear the message after displaying
    }
    ?>
    <?php if (count($orders) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Item Count</th>
                    <th>Total (RM)</th>
                    <th>Order Status</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo date('m/d/Y', strtotime($order['date'])); ?></td>
                        <td><?php echo date('H:i A', strtotime($order['time'])); ?></td>
                        <td><?php echo htmlspecialchars($order['total_product']); ?></td>
                        <td><?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                        <td><?php echo ucfirst($order['payment_method']); ?></td>
                        <td><?php echo ucfirst($order['payment_status']); ?></td>
                        <td style="display: flex;">
                            <a href="order_detail.php?order_id=<?php echo urlencode($order['id']); ?>" class="btn-detail">Detail</a>
                            <!-- Display Cancel Button -->
                            <?php if ($order['order_status'] !== 'shipping' && $order['order_status'] !== 'completed'&& $order['order_status'] !== 'cancelled'): ?>
                                <form id="cancelForm-<?php echo htmlspecialchars($order['id']); ?>" method="POST" style="display: inline;">
                                <?php
                                         
                                                html_hidden2('cancel_order_id', '', $order['id']);
                                                ?>

                                    <button type="button" class="btn-cancel" onclick="confirmCancel(<?php echo htmlspecialchars($order['id']); ?>)">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>

<?php include 'components/footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>
