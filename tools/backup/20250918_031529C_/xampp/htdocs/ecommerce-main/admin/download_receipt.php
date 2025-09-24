<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/_base.php';

$order_id = get('order_id');
$type = get('type');

if ($type === 'html') {
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

    $order_products_stmt = $_db->prepare("
        SELECT p.name, p.price, op.quantity, op.subtotal
        FROM orderproduct op
        INNER JOIN product p ON op.product_id = p.id
        WHERE op.ordersid = ?
    ");
    $order_products_stmt->execute([$order_id]);
    $order_products = $order_products_stmt->fetchAll();

    // Generate HTML content
    $receipt_content = "<!DOCTYPE html>
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
            color: #fff; 
            background-color: #4CAF50; 
            text-decoration: none; 
            border-radius: 5px; 
        } 
        .download-btn:hover { 
            background-color: #45a049; 
        } 
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
                    <tr><th>Order by:</th><td>{$order->customer_name}</td></tr>
                    <tr><th>Payment Method:</th><td>{$order->payment_method}</td></tr>
                    <tr><th>Order Date:</th><td>" . date('m/d/Y, H:i A', strtotime($order->date . ' ' . $order->time)) . "</td></tr>
                    <tr><th>Shop Name:</th><td>Baby Shark Book Shop</td></tr>
                </table>
            </div>
            <div class='order-summary'>
                <table width='100%'>
                    <thead>
                        <tr><th>Item</th><th>Price</th><th>Quantity</th><th>Totals</th></tr>
                    </thead>
                    <tbody>";
    foreach ($order_products as $product) {
        $receipt_content .= "<tr>
            <td>{$product->name}</td>
            <td>RM " . number_format($product->price, 2) . "</td>
            <td>{$product->quantity}</td>
            <td>RM " . number_format($product->subtotal, 2) . "</td>
        </tr>";
    }
    $receipt_content .= "</tbody>
                <tfoot>
                    <tr><td colspan='3' class='total'>Total</td><td class='total'>RM " . number_format($order->total_price, 2) . "</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
    </body>
    </html>";

    // Serve the file for download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="receipt.html"');
    echo $receipt_content;
    exit();
}
?>
