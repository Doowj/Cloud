<?php
include $_SERVER['DOCUMENT_ROOT'] . '/components/_base.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle POST request: Register new member
if (is_post()) {
    // Get input fields
    $name      = req('name');
    $gender    = req('gender');
    $dob       = req('dob');
    $email     = req('email');
    $telephone = req('telephone');
    $address   = req('address');
    $password  = req('password');
    $confirm   = req('confirm');
    $f         = get_file('image'); // Get the image file
    $webcamImage = req('webcamImage'); // Get the Base64 image from the webcam

    // Initialize error array
    $_err = [];

    // Validate fields
    if ($name == '') {
        $_err['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $_err['name'] = 'Maximum length is 100 characters';
    }

    if ($gender == '') {
        $_err['gender'] = 'Gender is required';
    } elseif (!in_array($gender, ['M', 'F'])) {
        $_err['gender'] = 'Invalid gender selection';
    }

    if ($dob == '') {
        $_err['dob'] = 'Date of birth is required';
    }

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
    } elseif (!preg_match('/^\d{3}-\d{8}$/', $telephone)) {
        $_err['telephone'] = 'Telephone number must be a number and in the format xxx-xxxxxxxx';
    }

    if ($address == '') {
        $_err['address'] = 'Address is required';
    }

    if ($password == '') {
        $_err['password'] = 'Password is required';
    } elseif (strlen($password) < 5 || strlen($password) > 100) {
        $_err['password'] = 'Password must be between 5 and 100 characters';
    }

    if ($confirm == '') {
        $_err['confirm'] = 'Confirm password is required';
    } elseif ($confirm != $password) {
        $_err['confirm'] = 'Passwords do not match';
    }

    // Process webcam image if available
    if (!empty($webcamImage)) {
        $image_data = explode(',', $webcamImage); // Separate Base64 header from data
        $image_base64 = base64_decode($image_data[1]); // Decode Base64 string
        $image_name = uniqid() . '.png'; // Generate unique image name
        $image_path = 'user_img/' . $image_name; // Specify the image path

        if (file_put_contents($image_path, $image_base64)) {
            $image = $image_name; // Set image for database storage
        } else {
            $_err['image'] = 'Failed to save webcam image. Please try again.';
        }
    }

    if (!$f && empty($webcamImage)) {
        $_err['image'] = 'Image is required (either upload or webcam).';
    }


    // Validate file upload (if any)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['image'] = 'Must be an image';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['image'] = 'Maximum file size is 1MB';
        }
    }

    // Insert member data into the database if no errors
    if (empty($_err)) {
        if ($f) {
             $image = save_photo($f, $_SERVER['DOCUMENT_ROOT'] . '/user_img'); 
       
        }

        // Insert member data into the database
        $stm = $_db->prepare('
            INSERT INTO user (name, gender, dob, email, telephone, address, image, password, role,status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)
        ');
        $stm->execute([$name, $gender, $dob, $email, $telephone, $address, $image, sha1($password), 'Member','active']);

        // Set success message and redirect
        temp('info', 'Registration successful! Please login.');
        redirect('login.php');
    }
}

// Page title
$_title = 'Member Registration';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="/css/registercss.css">
    <style>
  body {
        background-image: url("images/background.png");
        background-size: cover; /* Cover the entire area */
        background-position: center; /* Center the background image */
    }
    h2 { 
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

<!-- Member Registration Form -->
<form method="post" class="form" enctype="multipart/form-data" novalidate>
<h2>Register form</h2>
    <div class="form-group">
        <label for="image">Profile Image</label>
        <label class="upload" tabindex="0">
            <?= html_file('image', 'image/*', 'hidden') ?>
            <img id="imagePreview" src="/user_img/photo.jpg" alt="Upload Image">
        </label>
        <?= err('image') ?>
    </div>

    <!-- Webcam Section -->
    <div>
        <video id="vid" autoplay muted style="display:none;"></video> <!-- Hidden video stream -->
        <canvas id="canvas" style="display:none;"></canvas> <!-- Hidden canvas for capturing -->
    </div>

    <br/>
    <button type="button" id="startWebcam">Open Webcam</button>
    <button type="button" id="capturePhoto" style="display:none;">Capture Photo</button>
    <button type="button" id="stopWebcam" style="display:none;">Stop Webcam</button>
    <input type="hidden" name="webcamImage" id="webcamImage">

    <div class="form-group">
        <label for="name">Name</label>
        <?= html_text('name', 'maxlength="100"') ?>
        <?= err('name') ?>
    </div>

    <div class="form-group">
        <label for="gender">Gender</label>
        <?= html_radios('gender', ['M' => 'Male', 'F' => 'Female']) ?>
        <?= err('gender') ?>
    </div>

    <div class="form-group">
        <label for="dob">Date of Birth</label>
        <input type="date" name="dob" value="<?= htmlspecialchars(req('dob')) ?>">
        <?= err('dob') ?>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <?= html_text('email', '', '') ?>
        <?= err('email') ?>
    </div>

    <div class="form-group">
        <label for="telephone">Telephone</label>
        <?= html_text('telephone', '', '') ?>
        <?= err('telephone') ?>
    </div>

    <div class="form-group">
        <label for="address">Address</label>
        <?= html_textarea('address', '' ) ?>
        <?= err('address') ?>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <?= html_password('password', 'maxlength="100"') ?>
        <?= err('password') ?>
    </div>

    <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <?= html_password('confirm', 'maxlength="100"') ?>
        <?= err('confirm') ?>
    </div>

    <section>
        <button type="submit">Register</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/login.php'">Back</button>
    </section>
</form>

<!-- Webcam and Image Handling Script -->
<script>
    document.querySelector('input[name="image"]').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('imagePreview').src = URL.createObjectURL(file);
    }
});
    // Resize images to fixed size (200x200)
    const FIXED_WIDTH = 200;
    const FIXED_HEIGHT = 200;


    // Function to resize uploaded images
    function resizeUploadedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.src = e.target.result;
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = FIXED_WIDTH;
                    canvas.height = FIXED_HEIGHT;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, FIXED_WIDTH, FIXED_HEIGHT);
                    const resizedDataURL = canvas.toDataURL('image/png');
                    document.getElementById('imagePreview').src = resizedDataURL;
                    document.getElementById('webcamImage').value = resizedDataURL;
                   
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
document.addEventListener("DOMContentLoaded", () => {
    const video = document.getElementById("vid");
    const canvas = document.getElementById("canvas");
    const startWebcamBtn = document.getElementById("startWebcam");
    const capturePhotoBtn = document.getElementById("capturePhoto");
    const webcamImageField = document.getElementById("webcamImage");
    const stopWebcamBtn = document.getElementById('stopWebcam');
    const imagePreview = document.getElementById("imagePreview");
    let webcamStream;

    // Start the webcam
    startWebcamBtn.addEventListener("click", () => {
        navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
            webcamStream = stream;
            video.srcObject = stream;
            video.style.display = "block";
            capturePhotoBtn.style.display = "inline";
            stopWebcamBtn.style.display = 'block';
            startWebcamBtn.style.display = "none";
        }).catch((err) => {
            alert("Webcam access denied: " + err);
        });
    });

    // Stop the webcam
    stopWebcamBtn.addEventListener('click', () => {
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop());
        }
        video.style.display = 'none';
        capturePhotoBtn.style.display = 'none';
        stopWebcamBtn.style.display = 'none';
        startWebcamBtn.style.display = 'inline';
    });

    // Capture photo from webcam
    capturePhotoBtn.addEventListener("click", () => {
        const context = canvas.getContext("2d");
        canvas.width = FIXED_WIDTH;
        canvas.height = FIXED_HEIGHT;
        context.drawImage(video, 0, 0, FIXED_WIDTH, FIXED_HEIGHT);
        const dataURL = canvas.toDataURL("image/png");
        webcamImageField.value = dataURL;
        imagePreview.src = dataURL;
        video.style.display = "none";
        capturePhotoBtn.style.display = "none";
        stopWebcamBtn.style.display = 'none';
        startWebcamBtn.style.display = 'inline';
    });
});
</script>
</body>
</html>
