<?php
require '../components/_base.php'; // Include base file for DB connection and other utilities
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authorization check for 'Admin'
auth('Admin');

$id = req('id');
$status = req('status');

if ($id && in_array($status, ['active', 'blocked'])) {
    // Update the status in the database
    $stm = $_db->prepare('UPDATE user SET status = ? WHERE id = ?');
    $stm->execute([$status, $id]);
    
    temp("success","Update the status successfully");
   redirect("admin_member_accounts.php");
    exit;
} else {
    temp("error","Oh! Somethings wrong.");
    redirect("admin_member_accounts.php");
    exit;
}
