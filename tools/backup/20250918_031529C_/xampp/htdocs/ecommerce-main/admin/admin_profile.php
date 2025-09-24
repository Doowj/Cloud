<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include '../components/_base.php';
$_title="Admin Profile";
// Ensure the user is authenticated
auth('Admin');

// Fetch the current user's data from the database
$stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
$stm->execute([$_user->id]);
$user = $stm->fetch();

// If user not found, redirect to homepage
if (!$user) {
    redirect('/');
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
    <link rel="stylesheet" href="../css/member_profile.css">
</head>
<body>
<?php include '../components/admin_header.php'; ?>

    <section class="profile-container">
        <div class="profile-card">
            <img src="/user_img/<?= htmlspecialchars($user->image) ?>" alt="Profile Picture" class="profile-picture">
            <h1><?= htmlspecialchars($user->name) ?></h1>
            <p><strong>ID:</strong> <?= htmlspecialchars($user->id) ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($user->gender) ?></p>
            <p><strong>Date of Birth:</strong> <?= htmlspecialchars($user->dob) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user->email) ?></p>
            <p><strong>Telephone:</strong> <?= htmlspecialchars($user->telephone) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($user->address) ?></p>
            <a href="admin_update_profile.php" class="edit-button">Edit Profile</a>
            <a href="admin_reset_password.php" class="edit-button" style="background-color: green;">Reset Password</a>
        </div>
    </section>
</body>
<script src="../js/script.js"></script>
</html>