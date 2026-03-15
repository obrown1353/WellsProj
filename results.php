<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_cache_expire(30);
    session_start();

    date_default_timezone_set("America/New_York");

    // Redirect to login if no session
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
    $notRoot = !$isAdmin;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Quicksand, sans-serif;
            background-color: #002D61 !important;
        }

        h2 {
            font-weight: normal;
            font-size: 30px;
        }

        .full-width-bar {
            width: 100%;
            background: #8DC9F7 !important;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .full-width-bar-sub {
            width: 100%;
            background: #002D61 !important;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .content-box {
            flex: 1 1 280px;
            max-width: 375px;
            padding: 10px 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box-sub {
            flex: 1 1 300px;
            max-width: 470px;
            padding: 10px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box img {
            width: 100%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 0.5px solid #828282;
        }

        .content-box-sub img {
            width: 105%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 1px solid #828282;
        }

        .small-text {
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 14px;
            font-weight: 700;
            color: #3A3A3A;
        }

        .large-text {
            position: absolute;
            top: 40px;
            left: 30px;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .large-text-sub {
            position: absolute;
            top: 60%;
            left: 10%;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .graph-text {
            position: absolute;
            top: 75%;
            left: 10%;
            font-size: 14px;
            font-weight: 700;
            color: #8DC9F7;
            max-width: 90%;
        }

        .navbar {
            width: 100%;
            height: 95px;
            position: fixed;
            top: 0;
            left: 0;
            background: #8DC9F7;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            padding: 0 30px;
            z-index: 1000;
        }

        .left-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo-container {
            background: #8DC9F7;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25) inset;
        }

        .logo-container img {
            width: 128px;
            height: 52px;
            display: block;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links div {
            font-size: 24px;
            font-weight: 700;
            color: black;
            cursor: pointer;
        }

        @media (max-width: 900px) { .nav-links div { font-size: 18px; } }
        @media (max-width: 600px) { .nav-links div { font-size: 16px; } }

        .right-section {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .date-box {
            background: #8DC9F7;
            padding: 10px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }

        .icon { width: 47px; height: 47px; border-radius: 50%; }

        .arrow-button {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .arrow-button:hover { transform: translateX(5px); }

        .circle-arrow-button {
            position: absolute;
            bottom: 30px;
            right: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            border: none;
            font-size: 20px;
            font-family: Quicksand, sans-serif;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .circle {
            width: 30px;
            height: 30px;
            background-color: #8DC9F7;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: transform 0.3s ease;
        }

        .circle-arrow-button:hover { background-color: transparent !important; }
        .circle-arrow-button:hover .circle { transform: translateX(5px); }

        .colored-box {
            display: inline-block;
            background-color: #8DC9F7;
            color: white;
            padding: 1px 5px;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            width: 100%;
            background: #8DC9F7;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 30px 50px;
            flex-wrap: wrap;
        }

        .footer-left { display: flex; flex-direction: column; align-items: center; }
        .footer-logo { width: 150px; margin-bottom: 15px; }
        .social-icons { display: flex; gap: 15px; }
        .social-icons a { color: white; font-size: 20px; transition: color 0.3s ease; }
        .social-icons a:hover { color: #dcdcdc; }
        .footer-right { display: flex; gap: 50px; flex-wrap: wrap; align-items: flex-start; }
        .footer-section { display: flex; flex-direction: column; justify-content: center; gap: 10px; color: #8DC9F7; font-family: Inter, sans-serif; font-size: 16px; font-weight: 500; }
        .footer-topic { font-size: 18px; font-weight: bold; }
        .footer a { color: white; text-decoration: none; transition: background 0.2s ease, color 0.2s ease; padding: 5px 10px; border-radius: 5px; }
        .footer a:hover { background: rgba(255, 255, 255, 0.1); color: #dcdcdc; }

        .background-image { width: 100%; border-radius: 10px; }

        .icon-overlay {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-overlay img { width: 40px; height: 40px; opacity: 0.9; }

        .content-box-test:hover .icon-overlay img {
            transform: scale(1.1) rotate(5deg);
            transition: transform 0.5s ease;
        }

        .content-box-test {
            position: relative;
            background-color: #8DC9F7 !important;
            border-radius: 12px;
            padding: 20px;
            color: black;
            flex: 1 1 280px;
            max-width: 375px;
            min-height: 250px;
        }

        .content-box-test .large-text-sub,
        .content-box-test .graph-text { color: black; }

        .background-image { display: none; }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const extra = document.querySelector(".extra-info");
            if (extra) extra.style.maxHeight = "0px";
        });
        function toggleInfo(event) {
            event.stopPropagation();
            let info = event.target.nextElementSibling;
            let isVisible = info.style.maxHeight !== "0px";
            info.style.maxHeight = isVisible ? "0px" : "100px";
            event.target.innerText = isVisible ? "↓" : "↑";
        }
    </script>
</head>

<!-- ADMIN VIEW -->
<?php if ($isAdmin): ?>
<body>
<?php require 'header.php'; ?>

    <!-- Search Bar -->
    <div style="display: flex; justify-content: center; margin: 40px 0;">
        <div style="width:100%; max-width: 900px; border: 3px solid #0067A2; border-radius: 16px; padding: 30px; background-color: #8DC9F7;">
            <form action="calendar.php" method="GET" style="width: 100%; max-width: 900px; display: flex;">
                <input type="text" name="query" placeholder="Search..."
                    style="width: 100%; max-width: 900px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 20px; outline: none;" required>
            </form>
        </div>
    </div>

    <!-- Sort by -->
    <div style="display: flex; justify-content: center; margin-top: -20px;">
        <form style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
            <span style="font-weight: bold; white-space: nowrap;">Sort by: </span>

            <input type="radio" id="sort-title" name="sort" value="title">
            <label for="sort-title" style="color: white; white-space: nowrap;">Title</label>

            <input type="radio" id="sort-author" name="sort" value="author">
            <label for="sort-author" style="color: white; white-space: nowrap;">Author</label>

            <input type="radio" id="sort-material-type" name="sort" value="material_type">
            <label for="sort-material-type" style="color: white; white-space: nowrap;">Material Type</label>

            <input type="radio" id="sort-location" name="sort" value="location">
            <label for="sort-location" style="color: white; white-space: nowrap;">Location</label>
        </form>

    </div>


    <div style="margin-top: 0px; padding: 30px 20px;">
        <h2><b>Search Results</b></h2>
    </div>

    <?php if (isset($_GET['pcSuccess'])): ?>
        <div class="happy-toast">Password changed successfully!</div>
    <?php elseif (isset($_GET['deleteService'])): ?>
        <div class="happy-toast">Service successfully removed!</div>
    <?php elseif (isset($_GET['serviceAdded'])): ?>
        <div class="happy-toast">Service successfully added!</div>
    <?php elseif (isset($_GET['animalRemoved'])): ?>
        <div class="happy-toast">Animal successfully removed!</div>
    <?php elseif (isset($_GET['locationAdded'])): ?>
        <div class="happy-toast">Location successfully added!</div>
    <?php elseif (isset($_GET['deleteLocation'])): ?>
        <div class="happy-toast">Location successfully removed!</div>
    <?php elseif (isset($_GET['registerSuccess'])): ?>
        <div class="happy-toast">Volunteer registered successfully!</div>
    <?php endif ?>


    <!-- Footer -->
    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <footer class="footer" style="margin-top: 100px;">
        <div class="footer-left">
            <img src="images/UMW_Eagles-logo.png" alt="Logo" class="footer-logo">
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
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
                <a href="">mwells@umw.edu</a>
                        <a href="tel:5406541290">(540) 654-1290</a>
            </div>
        </div>
    </footer>
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</body>
<?php endif ?>

<!-- WORKER VIEW -->
<?php if ($isWorker): ?>
<body>
<?php require 'header.php'; ?>

    <!-- Search Bar -->
    <div style="display: flex; justify-content: center; margin: 40px 0;">
        <div style="width:100%; max-width: 900px; border: 3px solid #0067A2; border-radius: 16px; padding: 30px; background-color: #8DC9F7;">
            <form action="calendar.php" method="GET" style="width: 100%; max-width: 900px; display: flex;">
                <input type="text" name="query" placeholder="Search..."
                    style="width: 100%; max-width: 900px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 20px; outline: none;">
            </form>
        </div>
    </div>

    <!-- Sort by -->
    <div style="display: flex; justify-content: center; margin-top: -20px;">
        <form style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
            <span style="font-weight: bold; white-space: nowrap;">Sort by: </span>

            <input type="radio" id="sort-title" name="sort" value="title">
            <label for="sort-title" style="color: white; white-space: nowrap;">Title</label>

            <input type="radio" id="sort-author" name="sort" value="author">
            <label for="sort-author" style="color: white; white-space: nowrap;">Author</label>

            <input type="radio" id="sort-material-type" name="sort" value="material_type">
            <label for="sort-material-type" style="color: white; white-space: nowrap;">Material Type</label>

            <input type="radio" id="sort-location" name="sort" value="location">
            <label for="sort-location" style="color: white; white-space: nowrap;">Location</label>
        </form>

    </div>


    <div style="margin-top: 0px; padding: 30px 20px;">
	<h2><b>Search Results</b></h2>
    </div>

    <!-- Footer -->
    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <footer class="footer" style="margin-top: 100px;">
        <div class="footer-left">
            <img src="images/UMW_Eagles-logo.png" alt="Logo" class="footer-logo">
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
        </div>
        <div class="footer-right">
            <div class="footer-section">
                <div class="footer-topic">Connect</div>
                <a href="https://www.facebook.com/profile.php?id=100086673730177#">Facebook</a>
                <a href="https://www.instagram.com/umw_coe/">Instagram</a>
                <a href="https://education.umw.edu/">Main Website</a
            </div>
            <div class="footer-section">
                <div class="footer-topic">Contact Us</div>
                <a href="">mwells@umw.edu</a>
                <a href="tel:5406541290">(540) 654-1290</a>
            </div>
        </div>
    </footer>
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</body>
<?php endif ?>

<!-- GUEST VIEW -->
<?php if ($isGuest): ?>
<body>
<?php require 'header.php'; ?>

<!-- MAIN TWO-COLUMN LAYOUT -->
<body style="display: flex; flex-direction: column; min-height: 100vh;">
<div style="display: flex; width: 100%; gap: 40px; justify-content: center; align-items: flex-start;">
    <!-- LEFT SIDEBAR (Filters) -->
    <div style="flex: 0 0 25%; border: 2px solid #0067A2; border-radius: 12px; padding: 20px; background-color: #f5f9ff; position: sticky; top: 120px; height: fit-content; overflow-y: auto; max-height: 90vh;">
	<h3>Filters</h3>
	<hr>

    	<!-- Location -->
    	<strong>Location</strong><br>
    	<input type="checkbox" id="loc-early1"> <label for="loc-early1">Early Readers 1</label><br>
    	<input type="checkbox" id="loc-early2"> <label for="loc-early2">Early Readers 2</label><br>
    	<input type="checkbox" id="loc-gen-a-m"> <label for="loc-gen-a-m">General Fiction A-M</label><br>
    	<input type="checkbox" id="loc-gen-n-z"> <label for="loc-gen-n-z">General Fiction N-Z</label><br>
    	<input type="checkbox" id="loc-nonfiction"> <label for="loc-nonfiction">General Nonfiction</label><br>
    	<input type="checkbox" id="loc-holiday"> <label for="loc-holiday">Holiday</label><br>
    	<input type="checkbox" id="loc-middle-grade"> <label for="loc-middle-grade">Middle Grade Novels</label><br>
    	<input type="checkbox" id="loc-multilingual"> <label for="loc-multilingual">Multilingual</label><br>
    	<input type="checkbox" id="loc-realistic-a-g"> <label for="loc-realistic-a-g">Realistic Fiction A-G</label><br>
    	<input type="checkbox" id="loc-realistic-h-z"> <label for="loc-realistic-h-z">Realistic Fiction H-Z</label><br>
    	<input type="checkbox" id="loc-science-a-f"> <label for="loc-science-a-f">Science A-F</label><br>
    	<input type="checkbox" id="loc-science-a-m"> <label for="loc-science-a-m">Science A-M</label><br>
    	<input type="checkbox" id="loc-science-g-q"> <label for="loc-science-g-q">Science G-Q</label><br>
    	<input type="checkbox" id="loc-science-n-z"> <label for="loc-science-n-z">Science N-Z</label><br>
    	<input type="checkbox" id="loc-science-r-z"> <label for="loc-science-r-z">Science R-Z</label><br>
    	<input type="checkbox" id="loc-science-resources"> <label for="loc-science-resources">Science Resources</label><br>
    	<input type="checkbox" id="loc-social-studies"> <label for="loc-social-studies">Social Studies</label><br>
    	<input type="checkbox" id="loc-social-f"> <label for="loc-social-f">Social Studies Stories A-F</label><br>
    	<input type="checkbox" id="loc-social-l"> <label for="loc-social-l">Social Studies Stories A-L</label><br>
    	<input type="checkbox" id="loc-social-g-o"> <label for="loc-social-g-o">Social Studies Stories G-O</label><br>
    	<input type="checkbox" id="loc-social-p-z"> <label for="loc-social-p-z">Social Studies Stories P-Z</label><br>
    	<input type="checkbox" id="loc-trad-folk"> <label for="loc-trad-folk">Trad/Folk</label><br>
    	<input type="checkbox" id="loc-transportation"> <label for="loc-transportation">Transportation</label><br>
    	<input type="checkbox" id="loc-wordless"> <label for="loc-wordless">Wordless Picture Books</label><br>

    <br>

    	<!-- Material Type -->
    	<strong>Material Type</strong><br>
    	<input type="checkbox" id="mat-child-lit"> <label for="mat-child-lit">Children’s Literature</label><br>
    	<input type="checkbox" id="mat-math"> <label for="mat-math">Math Manipulatives</label><br>
    	<input type="checkbox" id="mat-prof"> <label for="mat-prof">Professional Text</label><br>
    	<input type="checkbox" id="mat-textbook"> <label for="mat-textbook">Textbook</label><br>
    	<input type="checkbox" id="mat-supplies"> <label for="mat-supplies">Supplies</label><br>
</div>

    <!-- RIGHT SIDE: MAIN CONTENT -->
    <div style="flex: 1; max-width: 900px;">

    <!--Search Bar -->
	<div style="margin: 40px 0; border: 3px solid #0067A2; border-radius: 16px; padding: 30px; background-color: #8DC9F7;">
            <form action="calendar.php" method="GET" style="width: 100%; max-width: 900px; display: flex;">
                <input type="text" name="query" placeholder="Search..."
                    style="width: 100%; max-width: 900px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 20px; outline: none;">
            </form>
        </div>

    <!-- Sort by -->
    <div style="display: flex; justify-content: center; margin-top: -20px;">
        <form style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
            <span style="font-weight: bold; white-space: nowrap;">Sort by: </span>

            <input type="radio" id="sort-title" name="sort" value="title">
            <label for="sort-title" style="color: white; white-space: nowrap;">Title</label>

            <input type="radio" id="sort-author" name="sort" value="author">
            <label for="sort-author" style="color: white; white-space: nowrap;">Author</label>

            <input type="radio" id="sort-material-type" name="sort" value="material_type">
            <label for="sort-material-type" style="color: white; white-space: nowrap;">Material Type</label>

            <input type="radio" id="sort-location" name="sort" value="location">
            <label for="sort-location" style="color: white; white-space: nowrap;">Location</label>
        </form>

    </div>

    <!-- Search Results -->
    <div style="margin-top: 30px; padding: 30px 20px;">
	<h2><b>Search Results</b></h2>
	<!-- Results from database go here -->
    </div>
</div>

    <!-- Footer -->
    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <footer class="footer" style="margin-top: 100px;">
        <div class="footer-left">
            <img src="images/UMW_Eagles-logo.png" alt="Logo" class="footer-logo">
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
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
    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</body>
<?php endif ?>

</html>

