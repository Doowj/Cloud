<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../components/_base.php';

// ----------------------------------------------------------------------------

// TODO: (1) Delete expired tokens
$_db->query('DELETE FROM token WHERE expire < NOW()');

$id = req('id');

// TODO: (2) Is token id valid?
if (!is_exists($id, 'token', 'id')) {
    temp('info', 'Invalid token. Try again');
    redirect('/login.php');
}

if (is_post()) {
    $password = req('password');
    $confirm  = req('confirm');

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }
    else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }

    // Validate: confirm
    if ($confirm == '') {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }

    // DB operation
    if (!$_err) {
        // TODO: Update user (password) based on token id + delete token
        $stm = $_db->prepare('
            UPDATE user
            SET password = SHA1(?)
            WHERE id = (SELECT user_id FROM token WHERE id = ?);

            DELETE FROM token WHERE id = ?;
        ');
        $stm->execute([$password, $id, $id]);

        temp('info', 'Record updated');
        redirect('/login.php');
    }
}

// ----------------------------------------------------------------------------

$_title = 'User | Reset Password';

?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #e9ecef;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .form {
        max-width: 400px;
        width: 100%;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .form:hover {
        transform: scale(1.02);
    }

    .form label {
        display: block;
        margin-bottom: 10px;
        font-weight: bold;
        color: #333;
    }

    .form input[type="password"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .form input[type="password"]:focus {
        border-color: #007bff;
        outline: none;
    }

    .form button {
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        background-color: #007bff;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        margin-right: 10px;
        transition: background-color 0.3s;
    }

    .form button[type="reset"] {
        background-color: #6c757d;
    }

    .form button:hover {
        background-color: #0056b3;
    }

    .form button[type="reset"]:hover {
        background-color: #5a6268;
    }

    .form p {
        font-size: 14px;
        color: #333;
    }

    .form a {
        color: #007bff;
        text-decoration: none;
    }

    .form a:hover {
        text-decoration: underline;
    }
</style>


<form method="post" class="form">
    <label for="password">Password</label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>

    <label for="confirm">Confirm</label>
    <?= html_password('confirm', 'maxlength="100"') ?>
    <?= err('confirm') ?>

    <section>
        <button>Submit</button>
        <button type="reset">Reset</button>
    </section>
</form>

<?php
