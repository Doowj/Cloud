<?php
require '../components/_base.php'; // Include base file for DB connection and other utilities
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authorization check for 'Admin'
auth('Admin');

// Check if the request is POST
if (is_post()) {
    $id = req('id'); // Get the member ID from the POST request

    // Fetch and delete the member's image
    $stm = $_db->prepare('SELECT image FROM user WHERE id = ? AND role = ?');
    $stm->execute([$id, 'Member']);
    $image = $stm->fetchColumn();
    if ($image) {
        unlink("../user_img/$image"); // Delete the image from the server
    }

    // Delete the member from the database
    $stm = $_db->prepare('DELETE FROM user WHERE id = ? AND role = ?');
    $stm->execute([$id, 'Member']);

    // Set success message and redirect
    temp('info', 'Member deleted successfully');
    redirect('admin_member_accounts.php');
} else {
    // Redirect if the request method is not POST
    redirect('admin_member_accounts.php');
}
?>
