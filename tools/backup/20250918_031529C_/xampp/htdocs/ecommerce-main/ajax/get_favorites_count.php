<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/_base.php';

// Ensure the user is logged in
if (!isset($_user->id)) {
    echo 0;
    exit;
}

// Count the number of favorite items
$count_favorites_items = $_db->prepare("SELECT COUNT(*) FROM `favorites` WHERE member_id = ?");
$count_favorites_items->execute([$_user->id]);
$total_favorites_items = $count_favorites_items->fetchColumn();

echo $total_favorites_items;
?>
