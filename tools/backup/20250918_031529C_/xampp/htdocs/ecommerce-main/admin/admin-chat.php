<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require '../components/_base.php'; // Include base file for DB connection and other utilities

// Authorization check for 'Admin'
auth('Admin');

// Fetch all members using PDO
$members_query = "SELECT id, name FROM user WHERE role = 'Member'";
$stm = $_db->query($members_query);
$members = $stm->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Chat</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/admin_style.css">
    <style>
        /* Style for the chat container and layout */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        h2 {
            color: #2c3e50;
    text-align: center;
    margin: 40px 0;
    font-size: 2.5em;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-bottom: 3px solid #3498db;
    padding-bottom: 10px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
        }

        select#memberSelect {
            display: block;
            margin: 20px auto;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            width: 50%;
            border: 1px solid #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        #chatArea {
            width: 60%;
            margin: 20px auto;
            display: none;
        }

        #chatMessages {
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: scroll;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Chat form styling */
        #chatForm {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
        }

        #chatMessage {
            resize: none;
            width: 100%;
            height: 80px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            font-size: 16px;
        }

        #buttons {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            align-self: flex-end;
            transition: background-color 0.3s ease;
        }

        #buttons:hover {
            background-color: #45a049;
        }

        /* Style for chat messages */
        .chat-message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 10px;
            background-color: #f1f1f1;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.from-admin {
            background-color: #e0f7fa;
            align-self: flex-start;
        }

        .chat-message.from-member {
            background-color: #d1c4e9;
            align-self: flex-end;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            select#memberSelect, #chatArea {
                width: 90%;
            }
        }
    </style>
</head>
<body>
<?php include '../components/admin_header.php' ?>
    <h2>Select Member to Chat With</h2>
    
    <select id="memberSelect">
        <option value="">-- Select Member --</option>
        <?php foreach ($members as $row): ?>
            <option value="<?php echo $row->id; ?>"><?php echo $row->name; ?></option>
        <?php endforeach; ?>
    </select>
    
    <div id="chatArea" style="display:none;">
        <!-- Chat Messages Display -->
        <div id="chatMessages" style="border: 1px solid #ccc; padding: 10px; height: 300px; overflow-y: scroll;">
            <!-- Messages will be displayed here via AJAX -->
        </div>

        <form id="chatForm">
            <input type="hidden" id="fromUserId" value="<?php echo $_SESSION['user']->id; ?>"> <!-- Admin ID -->
            <input type="hidden" id="toUserId">
            <textarea id="chatMessage" placeholder="Type your message here..."></textarea>
            <button type="submit" id="buttons">Send</button>
        </form>
    </div>

    <script>
    $(document).ready(function() {
    // Function to load chat messages
    function loadMessages(memberId) {
        $("#chatMessages").load("../fetch_chat.php?fromUserId=<?php echo $_SESSION['user']->id; ?>&toUserId=" + memberId);
    }

    // When a member is selected, start loading chat messages in real-time
    $("#memberSelect").change(function() {
        var memberId = $(this).val();
        if (memberId) {
            $("#toUserId").val(memberId); // Set member ID for chatting
            $("#chatArea").show();

            // Clear any previous intervals to avoid multiple polling intervals
            if (window.chatInterval) {
                clearInterval(window.chatInterval);
            }

            // Fetch chat messages between admin and selected member
            window.chatInterval = setInterval(function() {
                loadMessages(memberId);
            }, 1000);
        } else {
            $("#chatArea").hide();
            clearInterval(window.chatInterval); // Stop polling if no member is selected
        }
    });

    // Handle form submission to send a message
    $("#chatForm").submit(function(e) {
        e.preventDefault();
        var fromUserId = $("#fromUserId").val();  // Admin ID
        var toUserId = $("#toUserId").val();      // Selected Member ID
        var chatMessage = $("#chatMessage").val(); 

        if (chatMessage.trim() === '') {
            alert('Please type a message');
            return;
        }

        $.ajax({
            type: "POST",
            url: "../send_chat.php",  
            data: { fromUserId: fromUserId, toUserId: toUserId, chatMessage: chatMessage },
            success: function(response) {
                $("#chatMessage").val('');  // Clear message input on success
                loadMessages(toUserId);  // Reload messages after sending
            },
            error: function(xhr, status, error) {
                alert("Failed to send the message: " + error);
            }
        });
    });
});

    </script>
</body>
<script src="../js/script.js"></script>
</html>
