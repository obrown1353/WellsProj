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
    include_once('database/dbMaterials.php');

    $accessLevel = (int) $_SESSION['access_level'];
    $isGuest  = ($accessLevel === 0);
    $isWorker = ($accessLevel === 1);
    $isAdmin  = ($accessLevel >= 2);
    $query = "";
    $materials = fetch_all_materials();
    $results = [];


    if (!$isGuest && isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }

    if (isset($_GET['query'])) {
            $query = strtolower(trim($_GET['query']));
    }

    foreach ($materials as $material) {
            if (
                   str_contains(strtolower((string)$material->getName()), $query) ||
                   str_contains(strtolower((string)$material->getAuthor()), $query) ||
                   str_contains(strtolower((string)$material->getDescription()), $query) ||
                   str_contains(strtolower((string)$material->getISBN()), $query)
                ) {
        $results[] = $material;
            }
    }	

    $notRoot = !$isAdmin;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <!-- <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet"> -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">

    <title>Seacobeck Curriculum Lab | Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arimo', sans-serif;
	/*    background-color: #002D61 !important; */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-image: url('images/library.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
        }

        h2 {
            font-weight: normal;
            font-size: 30px;
	}

        .overlay {
            position: absolute;
            inset: 0;
            background: rgb(0, 45, 97, 0.88);
            z-index: -1;
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
            font-family: 'Arimo', sans-serif;
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

<body>
<?php require 'header.php'; ?>
    <div class="overlay"></div>
    <div style="margin-top: 0px; padding: 30px 20px;">
        <?php if ($isAdmin or $isWorker): ?>
            <h2><b>Welcome to the Seacobeck Curriculum Lab, <?php echo $person->get_first_name() ?>!</b> Let's get started.</h2>
        <?php endif ?>
        <?php if ($isGuest): ?>
            <h2><b>Welcome to the Seacobeck Curriculum Lab!</b> Let's get started.</h2>
        <?php endif ?>
    </div>

    <!-- Search Bar -->
    <div style="display: flex; justify-content: center; margin: 40px 0;">
        <div style="width:100%; max-width: 900px; border: 3px solid #0067A2; border-radius: 16px; padding: 30px; background-color: #8DC9F7;">
            <form action="results.php" method="GET" style="width: 100%; max-width: 900px; display: flex;">
                <div style="position: relative; width:100%;">
                <input type="text" name="query" placeholder="Search materials..."
                    style="flex: 7; width: 100%; max-width: 900px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 20px; outline: none; color: #0067A2;">
                <button type="submit" style="position: absolute; right: 0; top: 0; height: 83%; width: 120px; border: 1px solid #ccc; border-radius:0 20px 20px 0; background: #0067A2; color: white; font-size: 16px; cursor: pointer;">
                Search
            </button>
            </div>
            </form>
	</div>
    </div>

    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <?php require 'footer.php'; ?>

    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</body>
</html>
