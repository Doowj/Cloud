<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '_base.php';

session_start();
session_unset();
session_destroy();

header('location:../login.php');

?>