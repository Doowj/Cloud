<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';

// Ensure the user is logged in
auth("Member");

// Fetch user's favorites
$favorites_stmt = $_db->prepare("
    SELECT f.product_id, p.name, p.price, p.image
    FROM favorites f
    INNER JOIN product p ON f.product_id = p.id
    WHERE f.member_id = ?
");
$favorites_stmt->execute([$_user->id]);
$favorites = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch user's cart items
$cart_stmt = $_db->prepare("SELECT product_id, quantity FROM cart WHERE member_id = ?");
$cart_stmt->execute([$_user->id]);
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
$cart_product_ids = array_column($cart_items, 'product_id');

// Fetch user's purchased products
$purchased_stmt = $_db->prepare("
    SELECT op.product_id
    FROM orders o
    INNER JOIN orderproduct op ON o.id = op.ordersid
    WHERE o.member_id = ? AND o.status = 'completed'
");
$purchased_stmt->execute([$_user->id]);
$purchased_product_ids = $purchased_stmt->fetchAll(PDO::FETCH_COLUMN);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Favorites</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'components/user_header.php'; ?>
<section class="favorites">
    <h1 class="title">Your Favorites</h1>

    <table class="favorites-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($favorites) > 0): ?>
            <?php foreach ($favorites as $favorite): ?>
                <?php
                $product_id = $favorite['product_id'];

                // Determine the status of the product
                if (in_array($product_id, $cart_product_ids)) {
                    $status = 'Already in Cart';
                } elseif (in_array($product_id, $purchased_product_ids)) {
                    $status = 'Already Purchased';
                } else {
                    $status = 'Not yet purchased or added to cart';
                }
                ?>
                
                <tr data-product-id="<?= $product_id; ?>">
                    <td>
                        <a href="quick_view.php?product_id=<?= $product_id; ?>" class="fas fa-eye">
                            <img src="uploaded_img/<?= htmlspecialchars($favorite['image']); ?>" alt="<?= htmlspecialchars($favorite['name']); ?>">
                            <?= htmlspecialchars($favorite['name']); ?>
                        </a>
                    </td>
                    <td>$<?= htmlspecialchars($favorite['price']); ?></td>
                    <td><strong><?= $status; ?></strong></td>
                    <td>
                        <!-- Toggle Favorite (Heart Icon) -->
                        <?php
// Assuming $product_id is defined and you have a way to determine if it should be checked
html_checkbox2("like_$product_id", '', 'class="sr-only" onchange="toggleFavorite(' . $product_id . ', false)"');
?>
<label for="like_<?= $product_id; ?>" aria-hidden="true" class="like-label liked">❤</label>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="empty">You have no favorites yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<script>
function toggleFavorite(productId, isFavorite) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/favorite_toggle.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = xhr.responseText;
            if (response === 'User not logged in') {
                window.location.href = 'login.php';
            } else {
                alert(response);

                // Remove the product row if it's no longer a favorite
                if (!isFavorite) {
                    var productRow = document.querySelector(`tr[data-product-id="${productId}"]`);
                    if (productRow) {
                        productRow.remove();
                    }

                    // Check if the table is now empty
                    var tableBody = document.querySelector('.favorites-table tbody');
                    if (tableBody.rows.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="empty">You have no favorites yet.</td></tr>';
                    }
                }
            }
        }
    };

    xhr.send("product_id=" + productId + "&favorite=" + (isFavorite ? 1 : 0));
}
</script>

<?php include 'components/footer.php'; ?>

</body>
</html>
