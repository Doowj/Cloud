<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include $_SERVER['DOCUMENT_ROOT'] . '/_base.php';


session_start();
session_unset();
session_destroy();

// use root-relative redirect to avoid fragile ../ paths
header('Location: /login.php');

?>