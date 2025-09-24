<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'components/_base.php';

$message = []; // Initialize $message as an array

// ----------------------------------------------------------------------------

if (is_post()) {
    $email = req('email');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    }
    else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }
    else if (!is_exists($email, 'user', 'email')) {
        $_err['email'] = 'Not exists';
    }

    // Send reset token (if valid)
    if (!$_err) {
        try {
            // Select user
            $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
            $stm->execute([$email]);
            $u = $stm->fetch();

            // Generate token id
            $id = sha1(uniqid() . rand());

            // Delete old and insert new token
            $stm = $_db->prepare('
                DELETE FROM token WHERE user_id = ?;

                INSERT INTO token (id, expire, user_id)
                VALUES (?, ADDTIME(NOW(), "00:05"),?);
            ');
            $stm->execute([$u->id, $id, $u->id]);

            // Generate token url
            $url = base("/Users/token.php?id=$id");

            // Send email
            $m = get_mail();
            $m->addAddress($u->email, htmlspecialchars($u->name));
            $m->addEmbeddedImage("user_img/$u->image",'photo');
            $m->isHTML(true);
            $m->Subject = 'Reset Password';
            $m->Body = "
                <img src='cid:photo'
                     style='width: 200px; height: 200px;
                            border: 1px solid #333'>
                <p>Dear " . htmlspecialchars($u->name) . ",</p>
                <h1 style='color: red'>Reset Password</h1>
                <p>
                    Please click <a href='" . htmlspecialchars($url) . "'>here</a>
                    to reset your password.
                </p>
                <p>From, 😺 Admin</p>
            ";

            if ($m->send()) {
                temp('info', 'Email sent');
                redirect('/login.php');
            } else {
                $_err['email'] = 'Failed to send email';
            }
        } catch (Exception $e) {
            $_err['email'] = 'An error occurred: ' . $e->getMessage();
        }
    }
}

// ----------------------------------------------------------------------------

$_title = 'User | Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form input[type="text"],
        .form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
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
</head>
<body>
    <form method="post" class="form">
        <label for="email">Email</label>
        <?= html_text('email', 'maxlength="100"') ?>
        <?= err('email') ?>

        <section>
            <button type="submit">Submit</button>
            <button type="reset">Reset</button>
        </section>
    </form>
</body>
</html>
