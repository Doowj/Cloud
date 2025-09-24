<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'components/_base.php'; // Ensure DB connection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromUserId = isset($_POST['fromUserId']) ? intval($_POST['fromUserId']) : 0;
    $toUserId = isset($_POST['toUserId']) ? intval($_POST['toUserId']) : 0;
    $chatMessage = trim($_POST['chatMessage']);

    if ($fromUserId > 0 && $toUserId > 0 && !empty($chatMessage)) {
        try {
            // Insert the message into the chat table
            $stmt = $_db->prepare("INSERT INTO chat (from_user_id, to_user_id, message, timestamp) 
                                   VALUES (:fromUserId, :toUserId, :message, NOW())");
            $stmt->bindParam(':fromUserId', $fromUserId);
            $stmt->bindParam(':toUserId', $toUserId);
            $stmt->bindParam(':message', $chatMessage);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error sending message: " . $e->getMessage());
            echo "Error: Could not send message.";
        }
    } else {
        echo "Error: Invalid input.";
    }
}
