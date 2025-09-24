<?php
include __DIR__ . '/../components/_base.php'; // Include base file for DB connection and other utilities
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Authorization check for 'Admin'
auth('Admin');
$_title="Admin- Add Member";

// Handle POST request: Add new member
if (is_post()) {
    // Get input fields
    $name      = req('name');
    $gender    = req('gender');
    $dob       = req('dob');
    $email     = req('email');
    $telephone = req('telephone');
    $address   = req('address');
    $password     = req('password');
    $confirm = req('confirm');
    $f         = get_file('image'); // Get the image file


    // Validate: name
    if ($name == '') {
        $_err['name'] = 'Name is required';
    } else if (strlen($name) > 100) {
        $_err['name'] = 'Maximum length is 100 characters';
    }


    // Validate: gender
    if ($gender == '') {
        $_err['gender'] = 'Gender is required';
    } else if (!in_array($gender, ['M', 'F'])) {
        $_err['gender'] = 'Invalid gender selection';
    }


    // Validate: date of birth
    if ($dob == '') {
        $_err['dob'] = 'Date of birth is required';
    }


    // Validate: email
    if ($email == '') {
        $_err['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format';
    } else {
        // Check if the email already exists in the database
        $stm = $_db->prepare('SELECT COUNT(*) FROM user WHERE email = ?');
        $stm->execute([$email]);
        $emailExists = $stm->fetchColumn();

        if ($emailExists > 0) {
            $_err['email'] = 'Email already exists. Please use a different email.';
        }
    }


    if ($telephone == '') {
        $_err['telephone'] = 'Telephone is required';
    }elseif (!preg_match('/^\d{3}-\d{8}$/', $telephone)) {
        $_err['telephone'] = 'Telephone number must be a number and in the format xxx-xxxxxxxx';
    }


    // Validate: address
    if ($address == '') {
        $_err['address'] = 'Address is required';
    }


    // Validate: password
    if ($password == '') {
        $_err['password'] = 'Required';
    }else if (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Between 5-100 characters';
    }


   
    // Validate: confirm
    if (!$confirm) {
        $_err['confirm'] = 'Required';
    }
    else if (strlen($confirm) < 5 || strlen($confirm) > 100) {
        $_err['confirm'] = 'Between 5-100 characters';
    }
    else if ($confirm != $password) {
        $_err['confirm'] = 'Not matched';
    }


    // Validate: image (file)
    if (!$f) {
        $_err['image'] = 'Image is required';
    } else if (!str_starts_with($f->type, 'image/')) {
        $_err['image'] = 'File must be an image';
    } else if ($f->size > 1 * 1024 * 1024) { // Max 1MB
        $_err['image'] = 'Maximum file size is 1MB';
    }


    // If no errors, insert into the database
    if (!$_err) {
    // Save the uploaded image (use __DIR__ for a stable filesystem path)
    $image = save_photo($f, $_SERVER['DOCUMENT_ROOT'] . '/user_img'); // Ensure this function saves the image in the correct folder

        // Insert member data into the database
        $stm = $_db->prepare('
            INSERT INTO user (name, gender, dob, email, telephone, address, image, password, role,status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)
        ');
        $stm->execute([$name, $gender, $dob, $email, $telephone, $address, $image, sha1($password), 'Member','active']);


        // Set a success message and redirect
        temp('info', 'New member added successfully');
        redirect('admin_member_accounts.php');
    }
}


// Page title
$_title = 'Add Member';
include __DIR__ . '/../components/admin_header.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>


   <!-- Custom CSS file link -->
    <link rel="stylesheet" href="/css/admin_style.css">
    <link rel="stylesheet" href="/css/admin_member_update.css">
   <style>
    h1{
        text-align: center; 
        color: #333; 
        margin-bottom: 20px; 
        font-size: 24px; 
        font-weight: bold; 
        text-transform: uppercase; 
        padding-bottom: 10px; 
        border-bottom: 2px solid #333; 
    }
   </style>
</head>
<body>





<!-- Form for adding a new member -->
<form method="post" class="form" enctype="multipart/form-data" novalidate>
<div class="form-group">
<h1> Add Member Form </h1>
<label for="image"><h2>Profile Image</h2></label>
<label class="upload" tabindex="0">
    <?= html_file('image', 'image/*', 'hidden') ?> <!-- Keep the file input element -->
    <img id="imagePreview" src="/user_img/photo.jpg" alt="Upload Image">
</label>
<?= err('image') ?>
</div>


<div class="form-group">
    <label for="name"><h2>Name</h2></label>
    <?= html_text('name', 'maxlength="100"') ?>
    <?= err('name') ?>
</div>

<div class="form-group">
    <label for="gender"><h2>Gender</h2></label>
    <?= html_radios('gender', ['M' => 'M', 'F' => 'F']) ?>
    <?= err('gender') ?>
</div>

<div class="form-group">
    <label for="dob"><h2>Date of Birth</h2></label>
    <input type="date" name="dob" value="<?= htmlspecialchars(req('dob')) ?>">
    <?= err('dob') ?>
</div>

<div class="form-group">
    <label for="email"><h2>Email</h2></label>
    <?= html_text('email', '', '') ?>
    <?= err('email') ?>
</div>

<div class="form-group">
    <label for="telephone"><h2>Telephone</h2></label>
    <?= html_text('telephone', '', '') ?>
    <?= err('telephone') ?>
</div>

<div class="form-group">
    <label for="address"><h2>Address</h2></label>
    <?= html_textarea('address', '' ) ?>
    <?= err('address') ?>
</div>

<div class="form-group">
    <label for="password"><h2>Password</h2></label>
    <?= html_password('password', 'maxlength="100"') ?>
    <?= err('password') ?>
</div>


<div class="form-group">  
    <label for="confirm"><h2>Confirm</h2></label>
    <?= html_password('confirm', 'maxlength="100"') ?>
    <?= err('confirm') ?>
</div>

    <section>
        <button type="submit">Submit</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/admin/admin_member_accounts.php'">Back</button>

    </section>
</form>
<script src="/js/admin_script.js"></script>
<script>
document.querySelector('input[name="image"]').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('imagePreview').src = URL.createObjectURL(file);
    }
});
</script>


</body>
</html>



