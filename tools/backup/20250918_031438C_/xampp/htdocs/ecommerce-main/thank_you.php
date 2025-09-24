<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';

auth("Member");
$_title="Thank you card";
// Get payment status
$payment_status = get('status', 'unpaid');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>

<div class="heading">
   <h3>Thank You</h3>
   <p><a href="index.php">Home</a> <span> / Thank You</span></p>
</div>

<section class="thank-you">
    <div class="thank-you-card">
        <h1>Thank You for Your Purchase!</h1>
        <p>We appreciate your business and hope you enjoy your new items.</p>
        <p>Your payment status is: <strong><?= encode($payment_status); ?></strong></p>

        <?php if ($payment_status == 'paid'): ?>
            <p>An email with the receipt has been sent to you.</p>
            <a href="send_receipt.php" class="btn btn-secondary">Download/Print Receipt</a>
        <?php endif; ?>

        <a href="index.php" class="btn">Back to Home</a>
    </div>
</section>

<?php include 'components/footer.php'; ?>

</body>
</html>
