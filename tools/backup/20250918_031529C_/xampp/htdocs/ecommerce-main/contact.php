<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$_title="Contact Us";
include 'components/_base.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?= $_title?></title>

    <!--  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
      -->
   
   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <style>
.container {
    display: flex;
    flex-direction: row; /* Map on the left, info on the right */
    max-width: 1200px;
    margin: auto;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.map-container {
    flex: 1; /* Ensures the map-container takes the left half */
    margin-right: 20px; /* Adds space between the map and the info section */
    width: 50%; /* Ensures it takes up half the width of the page */
}

.map-container iframe {
    width: 100%;
    height: 400px; /* Set a fixed height */
    border: none;
}

.info-container {
    flex: 1; /* Ensures info-container takes the right half */
    padding-left: 20px; /* Adds space on the left side of the info-container */
    width: 50%; /* Ensures it takes up half the width of the page */
}

.contact {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 30px;
    background-color: #f9f9f9;
}

h1 {
    font-size: 28px;
    color: #333;
    margin-bottom: 10px;
}

.subtitle {
    font-size: 16px;
    color: #666;
    margin-bottom: 20px;
}

.contact-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.contact-item img {
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.contact-item span {
    font-size: 16px;
    color: #00b8a9;
    line-height: 1.6;
}

a {
  text-decoration: underline;
  color: #00b8a9;
}

a:hover {
  color: blue;
}


.landmark {
    background-color: #f9f9f9;
    padding: 10px;
    margin-top: 20px;
    border-left: 4px solid #00b8a9;
    font-size: 14px;
}

.contact p {
    color: #333;
    font-size: 18px;
    margin-bottom: 30px;
}


   </style>

</head>
<body>
   
<!-- header section starts  -->
<?php include 'components/user_header.php'; ?>
<!-- header section ends -->

<div class="heading">
   <h3>Contact us</h3>
   <p><a href="index.php">Home</a> <span> / contact</span></p>
</div>
<!-- contact section starts  -->
<div class="contact">
    <div class="map-container"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1018108.57084422!2d99.95672793124997!3d4.596421999999993!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31caedf18ef3fdc7%3A0x78268e76c80da326!2sBookXcess!5e0!3m2!1sen!2smy!4v1727256063323!5m2!1sen!2smy" width="200" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
        <div class="info-container">
            <h1>Baby Shark Book Shop</h1>
            <p class="subtitle">BnB administrative office (1st floor) and warehouse. You may self collect your online orders from here by appointment.</p>
            
            <div class="contact-item">
                <img src="img/map-icon.png" alt="Location Icon">
                <span>91 Jalan Sultan Yussof<br>58100 Ipoh Perak<br>Malaysia</span>
            </div>
            
            <div class="contact-item">
                <img src="img/phone-icon.png" alt="Phone Icon">
                <span><a href="tel:+601157676523"><u>+60 11 5767 6523</u></a></span>
            </div>
            
            <div class="contact-item">
                <img src="img/email-icon.png" alt="Email Icon">
                <span><a href="mailto:doowj-am22@student.tarc.edu.my"><u>doowj-am22@student.tarc.edu.my</u></a></span>
            </div>
            
            <div class="contact-item">
                <img src="img/clock-icon.png" alt="Clock Icon">
                <span>Monday - Friday, 9am - 6pm<br>Closed on public holidays</span>
            </div>
            
            <div class="landmark">
                <strong>Landmark:</strong> We're located at the same row of Taman Desa Medical Center.
            </div>
    </div>



</div>



<!-- custom js file link  -->
<script src="js/script.js"></script>

<?php include 'components/footer.php'; ?>





</body>
</html>
