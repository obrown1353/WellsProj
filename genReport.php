<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}

include_once('database/dbPersons.php');
include_once('domain/Person.php');

$accessLevel = (int) $_SESSION['access_level'];
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
* { font-family: StromaBold, 'Lucida Sans'; }
body {
    background-color: #002D61;
    min-height: 100vh;
    padding-top: 95px;
    color: white;
}
</style>

<title>Seacobeck Curriculum Lab | Check Out</title>
</head>
<body">

  <?php require 'header.php'; ?>


  

<footer class="footer">
    <div class="footer-left">
        <img src="images/UMW_Eagles-logo.png" alt="Logo" class="footer-logo">
        <div class="social-icons">
            <a href="https://www.facebook.com/profile.php?id=100086673730177#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/umw_coe/" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://education.umw.edu/" aria-label="Website"><i class="fas fa-globe"></i></a>
        </div>
    </div>
    <div class="footer-right">
        <div class="footer-section">
            <div class="footer-topic">Connect</div>
            <a href="https://www.facebook.com/profile.php?id=100086673730177#">Facebook</a>
            <a href="https://www.instagram.com/umw_coe/">Instagram</a>
            <a href="https://education.umw.edu/">Main Website</a>
        </div>
        <div class="footer-section">
            <div class="footer-topic">Contact Us</div>
            <a href="mailto:mwells@umw.edu">mwells@umw.edu</a>
            <a href="tel:5406541290">(540) 654-1290</a>
        </div>
    </div>
</footer>

</body>
</html>