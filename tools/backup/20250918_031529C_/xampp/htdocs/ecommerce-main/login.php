<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/_base.php';
$_title = "Login";

$email = ''; // Initialize email variable
if (isset($_COOKIE['user_login'])) {
    $email = $_COOKIE['user_login']; // Get the email from the cookie
}

if (is_post()) {
    $email = req('email');
    $password = req('password');
    $remember_me = post('remember');

    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Required';
    } else if (!is_email($email)) {
        $_err['email'] = 'Invalid email';
    }

    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }

    // Login user
    if (!$_err) {
        $stm = $_db->prepare('SELECT * FROM user WHERE email = ?');
        $stm->execute([$email]);
        $u = $stm->fetch();

        // Check for user existence
        if ($u) {
            $current_time = new DateTime();
            $block_until = $u->block_until ? new DateTime($u->block_until) : null;
            // Check if the account is blocked
            if ($u->login_attempts >= 3 && $block_until && $current_time < $block_until) {
                $wait_time = $block_until->diff($current_time);
                temp("error", "Your account is temporarily blocked. Please try again in " . $wait_time->i . " minutes.");
            } else {
                // Check if the account is active
                if ($u->status != "active") {
                    temp("error", "Your account is blocked.");
                } else {
                    // Check password
                    if ($u->password === sha1($password)) {
                        // Reset attempts on successful login
                        $stm = $_db->prepare('UPDATE user SET login_attempts = 0, block_until = NULL WHERE email = ?');
                        $stm->execute([$email]);

                        // Set cookie if "Remember Me" is checked
                        if ($remember_me) {
                            setcookie('user_login', $email, time() + (86400 * 30), "/");
                        } else {
                            setcookie('user_login', '', time() - 3600, "/");
                        }

                        temp('info', 'Login successfully');
                        if ($u->role == "Member") {
                            temp('success', 'Member Login successfully');
                            loginMember($u);
                        } else {
                            temp('success', 'Admin Login successfully');
                            loginAdmin($u);
                        }
                    } else {
                        // Increment login attempts
                        $new_attempts = $u->login_attempts + 1;
                        $stm = $_db->prepare('UPDATE user SET login_attempts = ?, last_attempt = NOW() WHERE email = ?');
                        $stm->execute([$new_attempts, $email]);

                        // Set block time if attempts reach 3
                        if ($new_attempts >= 3) {
                            $block_time = new DateTime();
                            $block_time->modify('+10 minutes');  // 10分钟后解锁
                            $stm = $_db->prepare('UPDATE user SET block_until = ? WHERE email = ?');
                            $stm->execute([$block_time->format('Y-m-d H:i:s'), $email]);
                            temp("error", "Too many failed attempts. Your account is blocked for 10 minutes.");
                        } else {
                            $_err['password'] = 'Not matched';
                            temp("error", "You can make 3 attempts. You have used ".$new_attempts."attempt(s)");
                        }
                    }
                }
            }
        } else {
            $_err['email'] = 'User not found';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title ?></title>
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/form.css">
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
   <script src="js/script.js"></script>
   <style>
   body {
       background-image: url("images/background.png");
       background-size: cover;
       background-position: center;
   }
   </style>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    <div id="error"><?= temp('error') ?></div>
    <div id="success"><?= temp('success') ?></div>

    <div class="container">
      <div class="wrapper">
        <div class="title"><span>Login Form</span></div>
        <form method="post">
          <div class="row">
            <i class="fas fa-user"><img src="img/user.png" alt="User"></i>
            <?= html_text2("email", 'placeholder="Email"', $email) ?>
            <?= err('email') ?>
          </div>
          <div class="row">
            <i class="fas fa-lock"><img src="img/password.png" alt="Password"></i>
            <?= html_password2("password", 'placeholder="Password"') ?>
            <?= err('password') ?>
          </div>
          <br>
          <div class="pass"><a href="forget_password.php"><u> Forgot password?</u></a></div>

          <div class="form-check">
            <label class="form-check-label text-muted">
            <?php
            $rememberAttr = (isset($_COOKIE["user_login"])) ? 'checked' : '';
            html_checkbox2('remember', 'Keep me signed in', $rememberAttr);
            ?>
            </label>
          </div>

          <div class="row button">
            <input type="submit" value="Login">
          </div>

          <div class="mb-2">
            <a href="index.php" class="btn btn-block btn-facebook auth-form-btn">
              <i class="icon-social-home mr-2"></i>Back Home
            </a>
          </div>

          <div class="registration-prompt">
            <p>Don't have an account yet? <a href="register.php" class="register-link">Create your account here</a></p>
          </div>
        </form>
      </div>
    </div>
</body>
</html>
