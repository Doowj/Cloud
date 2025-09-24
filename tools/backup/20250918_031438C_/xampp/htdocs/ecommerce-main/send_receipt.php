<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php'; 
auth("Member"); 
 
// Fetch the user's latest order with payment details 
$last_order = $_db->prepare(" 
    SELECT o.*, p.payment_method, p.bankAccount  
    FROM orders o 
    LEFT JOIN payment p ON o.id = p.orders_id 
    WHERE o.member_id = ? 
    ORDER BY o.id DESC LIMIT 1 
"); 
$last_order->execute([$_user->id]); 
$order = $last_order->fetch(PDO::FETCH_ASSOC); 
 
// Ensure order exists 
if (!$order) { 
    redirect('/index.php'); // Redirect to home if no order found 
} 
 
// Fetch order products 
$order_products = $_db->prepare(" 
    SELECT p.name, op.quantity, p.price, op.subtotal  
    FROM orderproduct op  
    INNER JOIN product p ON op.product_id = p.id  
    WHERE op.ordersid = ? 
"); 
$order_products->execute([$order['id']]); 
$products = $order_products->fetchAll(PDO::FETCH_ASSOC); 
 
// Determine payment method (show last 4 digits if credit card) 
$payment_method = $order['payment_method'] === 'credit card' ? "Credit Card ****" . substr($order['bankAccount'], -4) : ucfirst($order['payment_method']); 
 
// Determine if it's a download request
$is_download = isset($_GET['download']) && $_GET['download'] === 'true';

// Setup email receipt content 
$receipt_content = " 
<!DOCTYPE html> 
<html> 
<head> 
    <style> 
        .receipt-container { 
            max-width: 600px; 
            margin: 0 auto; 
            font-family: Arial, sans-serif; 
            border: 1px solid #ddd; 
            padding: 20px; 
            background-color: #f9f9f9; 
        } 
        .receipt-header { 
            text-align: center; 
            margin-bottom: 20px; 
        } 
        .receipt-header h2 { 
            margin: 0; 
        } 
        .order-info, .order-summary { 
            margin-bottom: 20px; 
        } 
        .order-info th, .order-info td { 
            padding: 5px; 
            text-align: left; 
        } 
        .order-summary th, .order-summary td { 
            padding: 8px; 
            border-bottom: 1px solid #ddd; 
        } 
        .total { 
            font-weight: bold; 
        } 
        .download-btn { 
            display: inline-block; 
            margin-top: 20px; 
            padding: 10px 20px; 
            background-color:#008CBA; 
            text-decoration: none; 
            border-radius: 5px; 
            color: #fff;
        } 
        .download-btn:hover { 
            background-color: #2196F3;
            color: #fff;
        } 
    </style> 
</head> 
<body> 
    <div class='receipt-container'> 
        <div class='receipt-header'> 
            <h2>Thank you for your order!</h2> 
            <p>Order #{$order['id']}</p> 
        </div> 
        <div class='order-info'> 
            <table> 
                <tr> 
                    <th>Order by:</th> 
                    <td>{$_user->name}</td> 
                </tr> 
                <tr> 
                    <th>Payment Method:</th> 
                    <td>{$payment_method}</td> 
                </tr> 
                <tr> 
                    <th>Order Date:</th> 
                    <td>" . date('m/d/Y, H:i A', strtotime($order['date'] . ' ' . $order['time'])) . "</td> 
                </tr> 
                <tr> 
                    <th>Shop Name:</th> 
                    <td>Baby Shark Book Shop</td> 
                </tr> 
            </table> 
        </div> 
        <div class='order-summary'> 
            <table width='100%'> 
                <thead> 
                    <tr> 
                        <th>Item</th> 
                        <th>Price</th> 
                        <th>Quantity</th> 
                        <th>Totals</th> 
                    </tr> 
                </thead> 
                <tbody>"; 
 
// Add products to receipt content 
foreach ($products as $product) { 
    $receipt_content .= " 
                    <tr> 
                        <td>{$product['name']}</td> 
                        <td>RM " . number_format($product['price'], 2) . "</td> 
                        <td>{$product['quantity']}</td> 
                        <td>RM " . number_format($product['subtotal'], 2) . "</td> 
                    </tr>"; 
} 
 
// Add totals to receipt content 
$receipt_content .= " 
                </tbody> 
                <tfoot> 
                    <tr> 
                        <td colspan='3' class='total'>Total</td> 
                        <td class='total'>RM " . number_format($order['total_price'], 2) . "</td> 
                    </tr> 
                </tfoot> 
            </table> 
        </div>";

// Add the download button only if not downloading
if (!$is_download) {
    $receipt_content .= "
        <a style='color: #fff;' href='" . base("send_receipt.php?order_id={$order['id']}&download=true") . "' class='download-btn'>Download/Print Receipt</a>";
}

$receipt_content .= "
    </div> 
</body> 
</html>"; 
 
// Send the receipt email to the user 
$mail = get_mail(); 
$mail->addAddress($_user->email, $_user->name); 
$mail->Subject = 'Order Receipt'; 
$mail->isHTML(true); 
$mail->Body = $receipt_content; 
 
try { 
    $mail->send(); 
    $message[] = 'Receipt sent to your email.'; 
} catch (Exception $e) { 
    $message[] = 'Failed to send receipt. Please try again later.'; 
} 
 
// Check if the user is downloading the receipt 
if ($is_download) { 
    // Serve the receipt for download 
    header('Content-Type: text/html'); 
    header('Content-Disposition: attachment; filename="receipt.html"'); 
    echo $receipt_content; 
    exit(); 
} 
 
temp("info","The receipt already send to email");
// Redirect back to thank you page if not downloading 
redirect('/thank_you.php?status=paid'); 
