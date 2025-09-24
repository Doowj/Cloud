<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include 'components/_base.php';

auth("Member");

// Ensure database connection is available
global $_db;

// Fetch admin ID (assuming only 1 admin or based on your admin setup)
$admin_query = "SELECT id, name FROM user WHERE role = 'Admin' LIMIT 1";
$admin_stmt = $_db->prepare($admin_query);
$admin_stmt->execute();
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

// Ensure that admin data is retrieved
if (!$admin) {
    die('No admin found.');
}

// Fetch member ID from session (ensure member is logged in)
$member_id = $_SESSION['user']->id ?? null;

if (!$member_id) {
    die('No member ID found.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-Time Chat</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Style for the chat container and layout */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f9;
        margin: 0;
        padding: 0;
    }

    h2 {
        text-align: center;
        color: #333;
        padding-top: 20px;
    }

    #chatMessages {
        width: 60%;
        margin: 20px auto;
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
        width: 60%;
        margin: 20px auto;
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

    #sendMessageButton {
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

    #sendMessageButton:hover {
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
        #chatMessages, #chatForm {
            width: 90%;
        }
    }
    </style>
</head>
<body>
<?php include 'components/user_header.php'; ?>
<div class="heading">
    <h3>Real-Time-Chat</h3>
    <p><a href="index.php">Home</a> <span> / Real-Time-Chat </span></p>
</div>
    <!-- Chat Messages Display -->
    <div id="chatMessages">
        <!-- Messages will be displayed here via AJAX -->
    </div>

    <form id="chatForm">
        <input type="hidden" id="fromUserId" value="<?php echo $member_id; ?>">
        <input type="hidden" id="toUserId" value="<?php echo $admin['id']; ?>">
        <textarea id="chatMessage" placeholder="Type your message here..."></textarea>
        <button type="submit" id="sendMessageButton">Send</button>
    </form>

    <script>
    $(document).ready(function() {
        // Fetch and display chat messages in real-time
        function loadMessages() {
            $.ajax({
                url: "fetch_chat.php",
                method: "GET",
                data: {
                    fromUserId: "<?php echo $member_id; ?>",
                    toUserId: "<?php echo $admin['id']; ?>"
                },
                success: function(response) {
                    $('#chatMessages').html(response);
                    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight); // Auto scroll to the bottom
                },
                error: function(xhr, status, error) {
                    console.error("Error loading messages: ", error);
                }
            });
        }

        // Initial fetch and set interval for updating messages
        loadMessages();
        setInterval(loadMessages, 1000); // Refresh every 1 second

        // Send chat message via AJAX
        $("#chatForm").submit(function(e) {
            e.preventDefault();

            var fromUserId = $("#fromUserId").val();
            var toUserId = $("#toUserId").val();
            var chatMessage = $("#chatMessage").val().trim();

            if (chatMessage === "") {
                alert("Message cannot be empty.");
                return;
            }

            $.ajax({
                type: "POST",
                url: "send_chat.php",
                data: {
                    fromUserId: fromUserId,
                    toUserId: toUserId,
                    chatMessage: chatMessage
                },
                success: function(response) {
                    $("#chatMessage").val(''); // Clear the message input field
                    loadMessages(); // Reload messages after sending
                },
                error: function(xhr, status, error) {
                    alert("Failed to send the message: " + error);
                }
            });
        });
    });
    </script>
</body>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</html>
