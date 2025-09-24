<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../components/_base.php';

// ----------------------------------------------------------------------------
$_title="Admin reset Password";
// Ensure the user is authenticated and an admin
auth('Admin');

if (is_post()) {
    $password     = req('password');
    $new_password = req('new_password');
    $confirm      = req('confirm');

    // Validate: current password
    if ($password == '') {
        $_err['password'] = 'Current password is required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Password must be between 5-100 characters';
    }
    else {
        // Ensure the current password matches the one in the database
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE password = SHA1(?) AND id = ?
        ');
        $stm->execute([$password, $_user->id]); // Use admin's session ID
        
        if ($stm->fetchColumn() == 0) {
            $_err['password'] = 'Current password is incorrect';
        }
    }

    // Validate: new password
    if ($new_password == '') {
        $_err['new_password'] = 'New password is required';
    }
    else if (strlen($new_password) < 5 || strlen($new_password) > 100) {
        $_err['new_password'] = 'Password must be between 5-100 characters';
    }

    // Validate: password confirmation
    if (!$confirm) {
        $_err['confirm'] = 'Please confirm your new password';
    }
    else if ($confirm != $new_password) {
        $_err['confirm'] = 'New password and confirmation do not match';
    }

    // Database operation if no errors
    if (!$_err) {
        // Update the admin's password
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE id = ?
        ');
        $stm->execute([$new_password, $_user->id]);

        // Notify the admin of successful update
        temp('info', 'Password updated successfully');
        redirect('../admin/admin_profile.php'); // Redirect to admin profile page
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/member_profile_update.css">
    <style>
        /* Similar styling to the user form */
        .form {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .form button:hover {
            opacity: 0.8;
        }
        .form section {
            margin-top: 20px;
        }
        /* Error messages */
        .err {
            color: #f44336;
            font-size: 14px;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        /* Success message */
        .info {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<?php include '../components/admin_header.php'; ?>

<!-- Admin Change Password Form -->
<!-- Admin Change Password Form -->
<form method="post" class="form">
    <label for="password">Current Password</label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password', 'class="error"') ?>

    <label for="new_password">New Password</label>
    <?= html_password('new_password', 'maxlength="100"') ?>
    <?= err('new_password', 'class="error"') ?>

    <label for="confirm">Confirm New Password</label>
    <?= html_password('confirm', 'maxlength="100"') ?>
    <?= err('confirm', 'class="error"') ?>

    <section>
    <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/admin/admin_profile.php'">Back</button>
    </section>
</form>


</body>
<script src="../js/script.js"></script>
</html>
