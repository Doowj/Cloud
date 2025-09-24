<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';

// ----------------------------------------------------------------------------
$_title="Member Reset Password";
// Authenticated users
auth();

if (is_post()) {
    $password     = req('password');
    $new_password = req('new_password');
    $confirm      = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }
    else {
        $stm = $_db->prepare('
            SELECT COUNT(*) FROM user
            WHERE password = SHA1(?) AND id = ?
        ');
        $stm->execute([$password, $_user->id]);
        
        if ($stm->fetchColumn() == 0) {
            $_err['password'] = 'Not matched';
        }
    }

    // Validate: new_password
    if ($new_password == '') {
        $_err['new_password'] = 'Required';
    }
    else if (strlen($new_password) < 5 || strlen($new_password) > 100) {
        $_err['new_password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $new_password) {
        $_err['confirm'] = 'Not matched';
    }

    // DB operation
    if (!$_err) {
        // Update user (password)
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE id = ?
        ');
        $stm->execute([$new_password, $_user->id]);

        temp('info', 'Record updated');
        redirect('/profile.php');
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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/member_profile_update.css">
    <style>

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    color: #333;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

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

.form button {
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    margin-right: 10px;
}

.form button[type="reset"] {
    background-color: #f44336;
}

.form button:hover {
    opacity: 0.8;
}

.form section {
    margin-top: 20px;
}

/* Error messages */
.error {
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
}</style>
</head>
<body>
<?php include 'components/user_header.php'; ?>

<form method="post" class="form">
    <label for="password"><h2>Password</h2></label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>

    <label for="new_password"><h2>New Password</h2></label>
    <?= html_password('new_password', 'maxlength="100"') ?>
    <?= err('new_password') ?>

    <label for="confirm"><h2>Confirm</h2></label>
    <?= html_password('confirm', 'maxlength="100"') ?>
    <?= err('confirm') ?>

    <section>
    <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/profile.php'">Back</button>
    </section>
</form>
</body>
<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</html>