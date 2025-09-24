<?php
require '../components/_base.php'; // Include base file for DB connection and other utilities
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Authorization check for 'Admin'
auth('Admin');
$_title="Admin- Update Member";
// Initialize errors array
$_err = [];

// Handle GET request to fetch member data
if (is_get()) {
    $id = req('id');

    // Prepare the statement to fetch the member data
    $stm = $_db->prepare('SELECT * FROM user WHERE id = ?');
    $stm->execute([$id]);
    $member = $stm->fetch();

    if (!$member) {
        // Redirect if no member is found
        redirect('/admin_member_accounts.php');
    }

    // Extract user details to populate the form
    extract((array)$member);
    $_SESSION['image'] = $member->image; // Store existing image in session
}

// Handle POST request to update member data
if (is_post()) {
    $id          = req('id');
    $name        = req('name');
    $gender      = req('gender');
    $dob         = req('dob');
    $email       = req('email');
    $telephone   = req('telephone');
    $address     = req('address');
    $role        = 'Member'; // Explicitly set the role to 'Member'
    $f           = get_file('image'); // Fetch uploaded image file
    $image       = req('existing_image') ?: $_SESSION['image']; // Use the hidden field instead of the session for the image
    $webcamImage = req('webcamImage'); // The Base64 image from the webcam

    // Validation checks for each field
    if ($name == '') {
        $_err['name'] = 'Name is required';
    } elseif (strlen($name) > 100) {
        $_err['name'] = 'Maximum length is 100 characters';
    }

    if ($gender == '') {
        $_err['gender'] = 'Gender is required';
    } elseif (!in_array($gender, ['M', 'F'])) {
        $_err['gender'] = 'Invalid gender';
    }

    if ($dob == '') {
        $_err['dob'] = 'Date of Birth is required';
    }

    if ($email == '') {
        $_err['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Invalid email format';
    }

    if ($telephone == '') {
        $_err['telephone'] = 'Telephone is required';
    }elseif (!preg_match('/^\d{3}-\d{8}$/', $telephone)) {
        $_err['telephone'] = 'Telephone number must be in number and in the format xxx-xxxxxxxx';
    }

    if ($address == '') {
        $_err['address'] = 'Address is required';
    }

    // Validate webcam image if it exists
    if (!empty($webcamImage)) {
        // Convert Base64 to an image file
        $image_data = explode(',', $webcamImage); // Separate the Base64 header from the actual data
        $image_base64 = base64_decode($image_data[1]); // Decode Base64 string to binary
        $image_name = uniqid() . '.png'; // Generate a unique filename for the image

        // Save the image on the server
        $image_path = '../user_img/' . $image_name;
        file_put_contents($image_path, $image_base64); // Save binary data as an image file

        // Set the new image path to be stored in the database
        $image = $image_name;
    }

    // Validate the uploaded file (if applicable)
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['image'] = 'Must be an image';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['image'] = 'Maximum file size is 1MB';
        } else {
            // Delete the old image and save the new one
            unlink("/user_img/$image"); // Delete the old image
             $image = save_photo($f, $_SERVER['DOCUMENT_ROOT'] . '/user_img'); 
        }
    }

    // If no errors, proceed with the update in the database
    if (empty($_err)) {
        // Prepare and execute the update query
        $stm = $_db->prepare('
            UPDATE user
            SET name = ?, gender = ?, dob = ?, email = ?, telephone = ?, address = ?, image = ?
            WHERE id = ?
        ');
        $stm->execute([$name, $gender, $dob, $email, $telephone, $address, $image, $id]);

        // Check if the update was successful
        if ($stm->rowCount()) {
            temp('info', 'Record updated');
        } else {
            temp('error', 'Failed to update the record');
        }

        // Redirect to the member accounts page after updating
        redirect('admin_member_accounts.php');
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
   <link rel="stylesheet" href="../css/admin_member_update.css">
   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   <script src="../js/member.js"></script>
</head>
<body>
<?php include '../components/admin_header.php'; ?>
<form method="post" class="form" enctype="multipart/form-data">
    <label for="id"><h2>ID</h2></label> <b><?= encode($id) ?></b> <?= err('id') ?>
    
    <label for="image"><h2>Profile Picture</h2></label>
    <label class="upload" tabindex="0">
       <?= html_file('image', 'image/*', 'hidden', 'onchange="resizeUploadedImage(this)"') ?>
       <img id="imagePreview" src="../user_img/<?= encode($image ?? '') ?>" alt="Profile Image">
    </label>
    <?= err('image') ?>

    <!-- Webcam Capture Section -->
    <div>
        <video id="vid" autoplay muted style="display:none;"></video> <!-- Video stream (hidden initially) -->
        <canvas id="canvas" style="display:none;"></canvas> <!-- Canvas for capturing the image -->
    </div>

    <br/>
    <button type="button" id="startWebcam">Open Webcam</button>
    <button type="button" id="capturePhoto" style="display:none;">Capture Photo</button>
    <button type="button" id="stopWebcam" style="display:none;">Stop Webcam</button>
    <input type="hidden" name="webcamImage" id="webcamImage">

    <!--Member name-->
    <label for="name"><h2>Name</h2></label>
    <?= html_text('name', 'maxlength="100"', $name) ?> <?= err('name') ?>

    <label><h2>Gender</h2></label>
    <?= html_radios('gender', ['M' => 'M', 'F' => 'F'], $gender) ?> <?= err('gender') ?>

    <label for="dob"><h2>Date of Birth</h2></label>
    <input type="date" name="dob" value="<?= encode($dob) ?>"> <?= err('dob') ?>

    <label for="email"><h2>Email</h2></label>
    <?= html_text('email', '', $email) ?> <?= err('email') ?>

    <label for="telephone"><h2>Telephone</h2></label>
    <?= html_text('telephone', '', $telephone) ?> <?= err('telephone') ?>

    <label for="address"><h2>Address</h2></label>
    <textarea name="address"><?= encode($address) ?></textarea> <?= err('address') ?>

    <section>
        <button type="submit" onclick="return confirmSubmit()">Submit</button>
        <button type="reset">Reset</button>
        <button type="button" onclick="window.location.href='/admin/admin_member_accounts.php'">Back</button>
    </section>
</form>

<script>

document.querySelector('input[name="image"]').addEventListener('change', function (event) {
    const [file] = event.target.files;
    if (file) {
        document.getElementById('imagePreview').src = URL.createObjectURL(file);
    }
});
    // Resize webcam and uploaded images to fixed size (150x150)
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
    let video = document.getElementById("vid");
    let canvas = document.getElementById("canvas");
    let startWebcamBtn = document.getElementById("startWebcam");
    let capturePhotoBtn = document.getElementById("capturePhoto");
    let webcamImageField = document.getElementById("webcamImage");
    let stopWebcamBtn = document.getElementById('stopWebcam');
    let imagePreview = document.getElementById("imagePreview");
    let mediaDevices = navigator.mediaDevices;
    let webcamStream; // Declare the webcamStream variable to store the stream

    // Start the webcam stream when the button is clicked
    startWebcamBtn.addEventListener("click", () => {
        mediaDevices.getUserMedia({
            video: true
        }).then((stream) => {
            webcamStream = stream; // Store the stream in webcamStream
            video.srcObject = stream;
            video.style.display = "block";
            capturePhotoBtn.style.display = "inline";
            stopWebcamBtn.style.display = 'block';
            startWebcamBtn.style.display = "none"; // Hide "Open Webcam" button
        }).catch((err) => {
            alert("Webcam access denied: " + err);
        });
    });

    // Stop the webcam when the "Stop Webcam" button is clicked
    stopWebcamBtn.addEventListener('click', () => {
        if (webcamStream) {
            webcamStream.getTracks().forEach(track => track.stop()); // Stop the webcam stream
        }
        video.style.display = 'none';
        capturePhotoBtn.style.display = 'none';
        stopWebcamBtn.style.display = 'none';
        startWebcamBtn.style.display = 'inline';
    });

    // Capture the photo and resize it to fixed dimensions
    capturePhotoBtn.addEventListener("click", () => {
        // Draw the video frame on the canvas
        let context = canvas.getContext('2d');
        canvas.width = FIXED_WIDTH;
        canvas.height = FIXED_HEIGHT;
        context.drawImage(video, 0, 0, FIXED_WIDTH, FIXED_HEIGHT);

        // Convert the canvas content to a Base64 image string
        let imageDataURL = canvas.toDataURL('image/png');
        webcamImageField.value = imageDataURL; // Set the hidden field value

        // Show the resized image in the preview
        imagePreview.src = imageDataURL;

        // Stop the video stream
        webcamStream.getTracks().forEach(track => track.stop());
        video.style.display = "none";
        capturePhotoBtn.style.display = "none"; // Hide capture button
        startWebcamBtn.style.display = "inline"; // Show "Open Webcam" button again
    });
});

</script>

</body>
</html>
