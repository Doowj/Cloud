<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../components/_base.php';
auth('Admin');
$_title="Order detail";
// Get order ID from the URL
$order_id = get('id');

// Fetch the order details along with user and payment information
global $_db;
$order_stmt = $_db->prepare("
    SELECT o.*, u.name AS customer_name, u.email, p.payment_method, p.bankAccount, p.status AS payment_status, p.amount
    FROM orders o
    INNER JOIN user u ON o.member_id = u.id
    LEFT JOIN payment p ON o.id = p.orders_id
    WHERE o.id = ?
");
$order_stmt->execute([$order_id]);
$order = $order_stmt->fetch();

// If the order is not found, redirect to the order listing
if (!$order) {
    temp('error', 'Order not found.');
    redirect('admin_orders.php');
}

// Fetch order products
$order_products_stmt = $_db->prepare("
    SELECT p.name, p.price, op.quantity, op.subtotal
    FROM orderproduct op
    INNER JOIN product p ON op.product_id = p.id
    WHERE op.ordersid = ?
");
$order_products_stmt->execute([$order_id]);
$order_products = $order_products_stmt->fetchAll();
$email_reason = post('email_reason'," ");

// Handle form submission to update order and payment status
if (is_post()) {
    $new_status = post('status'); // New order status from the form
    $new_payment_status = post('payment_status'); // New payment status from the form
    //$email_reason = post('email_reason'," "); // Reason for cancellation, if applicable

    // Get the current status from the order (fetched from the DB earlier)
    $current_status = $order->status;
    $current_payment_status = $order->payment_status;

    // Only update the database and send an email if the order status has changed
    if ($new_status !== $current_status) {
        // Update order status in the database
        $update_order = $_db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update_order->execute([$new_status, $order_id]);

        // Prepare the email content based on the status
        $email_content = "";
        $mail_subject = "";

        if ($new_status === 'cancelled') {
            // Customize email content based on the reason for cancellation
            if ($email_reason === 'stock') {
                $email_content = "Dear {$order->customer_name},\n\nWe regret to inform you that your order #{$order->id} has been cancelled due to stock issues. We sincerely apologize for the inconvenience caused.\n\nBest regards,\nBaby Shark Book Shop";
            } else if ($email_reason === 'user_request') {
                $email_content = "Dear {$order->customer_name},\n\nYour order #{$order->id} has been successfully cancelled as per your request. If you have any further questions, feel free to contact us.\n\nBest regards,\nBaby Shark Book Shop";
            }
            $mail_subject = 'Order Cancelled - #' . $order->id;
        } elseif ($new_status === 'shipping') {
            $email_content = "Dear {$order->customer_name},\n\nGood news! Your order #{$order->id} is now being shipped. You can expect to receive it soon.\n\nBest regards,\nBaby Shark Book Shop";
            $mail_subject = 'Order Shipped - #' . $order->id;
        } elseif ($new_status === 'completed') {
            $email_content = "Dear {$order->customer_name},\n\nThank you for your purchase! Your order #{$order->id} has been completed successfully. We hope you enjoy your items.\n\nBest regards,\nBaby Shark Book Shop";
            $mail_subject = 'Order Completed - #' . $order->id;
        }

        // Send the email if content is set
        if ($email_content) {
            $mail = get_mail();
            $mail->addAddress($order->email, $order->customer_name);
            $mail->Subject = $mail_subject;
            $mail->Body = nl2br($email_content);
            $mail->isHTML(true);
            try {
                $mail->send();
                temp('success', 'Order status updated and email sent.');
            } catch (Exception $e) {
                temp('error', 'Failed to send order status email.');
            }
        }
        temp('success', 'Order status updated.');
    }

    // Only update the payment status and send an email if the payment status has changed
    if ($new_payment_status !== $current_payment_status) {
        // Update payment status in the database
        $update_payment = $_db->prepare("UPDATE payment SET status = ? WHERE orders_id = ?");
        $update_payment->execute([$new_payment_status, $order_id]);

        if ($new_payment_status === 'paid') {
            // Prepare the receipt email content
            $payment_method = $order->payment_method === 'credit card' ? "Credit Card ****" . substr($order->bankAccount, -4) : ucfirst($order->payment_method);

            // Determine if it's a download request
            $is_download = isset($_GET['download']) && $_GET['download'] === 'true';



            $receipt_content = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    .receipt-container { max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; border: 1px solid #ddd; padding: 20px; background-color: #f9f9f9; }
                    .receipt-header { text-align: center; margin-bottom: 20px; }
                    .receipt-header h2 { margin: 0; }
                    .order-info, .order-summary { margin-bottom: 20px; }
                    .order-info th, .order-info td { padding: 5px; text-align: left; }
                    .order-summary th, .order-summary td { padding: 8px; border-bottom: 1px solid #ddd; }
                    .total { font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='receipt-container'>
                    <div class='receipt-header'>
                        <h2>Thank you for your order!</h2>
                        <p>Order #{$order->id}</p>
                    </div>
                    <div class='order-info'>
                        <table>
                            <tr><th>Order by:</th><td>{$_user->name}</td></tr>
                            <tr><th>Payment Method:</th><td>{$payment_method}</td></tr>
                            <tr><th>Order Date:</th><td>" . date('m/d/Y, H:i A', strtotime($order->date . ' ' . $order->time)) . "</td></tr>
                            <tr><th>Shop Name:</th><td>Baby Shark Book Shop</td></tr>
                        </table>
                    </div>
                    <div class='order-summary'>
                        <table width='100%'>
                            <thead><tr><th>Item</th><th>Price</th><th>Quantity</th><th>Totals</th></tr></thead>
                            <tbody>";
            foreach ($order_products as $product) {
                $receipt_content .= "<tr><td>{$product->name}</td><td>RM " . number_format($product->price, 2) . "</td><td>{$product->quantity}</td><td>RM " . number_format($product->subtotal, 2) . "</td></tr>";
            }
            $receipt_content .= "</tbody>
                            <tfoot><tr><td colspan='3' class='total'>Total</td><td class='total'>RM " . number_format($order->total_price, 2) . "</td></tr></tfoot>
                        </table> 
        </div>";

            // Add the download button only if not downloading
            if (!$is_download) {
                $receipt_content .= "
                <a href='" . base("/admin/download_receipt.php?order_id={$order->id}&type=html") . "' class='download-btn'>Download/Print Receipt</a>
                
                ";
            
            }

            $receipt_content .= "
                </div> 
            </body> 
            </html>"; 

            // Send receipt email
            $mail = get_mail();
            $mail->addAddress($order->email, $order->customer_name);
            $mail->Subject = 'Order Receipt - #' . $order->id;
            $mail->isHTML(true);
            $mail->Body = $receipt_content;
            try {
                $mail->send();
                temp('success', 'Payment status updated and receipt email sent.');
            } catch (Exception $e) {
                temp('error', 'Failed to send receipt email.');
            }
            
            // Check if the user is downloading the receipt 
            if ($is_download) { 
                // Serve the receipt for download 
                header('Content-Type: text/html'); 
                header('Content-Disposition: attachment; filename="receipt.html"'); 
                echo $receipt_content; 
                exit(); 
            } 
           


        } elseif ($new_payment_status === 'refunded') {
            // Send refund notification email
            $email_content = "Dear {$order->customer_name},\n\nWe are writing to inform you that the payment for your order #{$order->id} has been refunded to your bank account.\n\nBest regards,\nBaby Shark Book Shop";
            $mail_subject = 'Order Refunded - #' . $order->id;

            // Send refund email
            $mail = get_mail();
            $mail->addAddress($order->email, $order->customer_name);
            $mail->Subject = $mail_subject;
            $mail->Body = nl2br($email_content);
            $mail->isHTML(true);
            try {
                $mail->send();
                temp('success', 'Refund email sent to customer.');
            } catch (Exception $e) {
                temp('error', 'Failed to send refund email.');
            }
        }
        temp('success', 'Payment status updated and email sent.');
    }

    // Redirect after processing
    redirect("/admin/admin_order_detail.php?id=$order_id");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="order-detail">
    <h1>Order Details (ID: <?= encode($order->id) ?>)</h1>
    
    <div class="order-info">
        <p><strong>Customer Name:</strong> <?= encode($order->customer_name) ?></p>
        <p><strong>Email:</strong> <?= encode($order->email) ?></p>
        <p><strong>Total Products:</strong> <?= encode($order->total_product) ?></p>
        <p><strong>Total Price:</strong> RM <?= number_format($order->total_price, 2) ?></p>
        <p><strong>Payment Method:</strong> <?= ucfirst($order->payment_method) ?></p>
        <p><strong>Payment Status:</strong> <?= ucfirst($order->payment_status) ?></p>
        <p><strong>Order Status:</strong> <?= encode($order->status) ?></p>
        <p><strong>Order Date:</strong> <?= date('m/d/Y', strtotime($order->date)) ?> <?= date('H:i A', strtotime($order->time)) ?></p>
        <p><strong>Shipping Address:</strong> <?= encode($order->address) ?></p>
    </div>

    <div class="order-products">
        <h2>Order Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Subtotal (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_products as $product): ?>
                    <tr>
                        <td><?= encode($product->name) ?></td>
                        <td><?= encode($product->quantity) ?></td>
                        <td><?= number_format($product->subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <form action="admin_order_detail.php?id=<?= encode($order->id) ?>" method="post" class="order-status-form">
        <h2>Update Order & Payment Status</h2>
        <div class="form-group">
            <label for="status">Order Status:</label>
            <?= html_select2('status', [
                'pending' => 'Pending',
                'shipping' => 'Shipping',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ], null, '', $order->status); ?>
        </div>

        <!-- Hidden Email Content Field (appears when order status is set to 'cancelled') -->
        <div class="form-group" id="emailContentDiv">
            <label for="email_reason">Reason for Cancellation:</label>
            <?= html_select2('email_reason', [
                'stock' => 'Due to Stock Issues',
                'user_request' => 'User Requested Cancellation'
            ], null, '', $email_reason); ?>
        </div>

        <div class="form-group">
            <label for="payment_status">Payment Status:</label>
            <?= html_select2('payment_status', [
                'unpaid' => 'Unpaid',
                'paid' => 'Paid',
                'refunded' => 'Refunded'
            ], null, '', $order->payment_status); ?>
        </div>
        <button type="submit">Update</button>
    </form>
    
    <button type="submit" onclick="window.location.href='admin_orders.php';">Go Back</button>
</section>

<script>
// Function to toggle email content visibility based on order status
document.querySelector('select[name="status"]').addEventListener('change', function() {
    var status = this.value;
    var emailContentDiv = document.getElementById('emailContentDiv');
    if (status === 'cancelled') {
        emailContentDiv.style.display = 'block'; // Show email content field
    } else {
        emailContentDiv.style.display = 'none';  // Hide email content field
    }
});

// Set initial state based on the current order status
document.addEventListener('DOMContentLoaded', function() {
    var status = document.querySelector('select[name="status"]').value;
    var emailContentDiv = document.getElementById('emailContentDiv');
    if (status === 'cancelled') {
        emailContentDiv.style.display = 'block';
    } else {
        emailContentDiv.style.display = 'none';
    }
});
</script>

<script src="../js/admin_script.js"></script>
</body>
</html>
