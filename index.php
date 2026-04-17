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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
	    font-family: 'Arimo', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-image: url('images/library.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            padding-top: 100px !important;
        }

        h2 { font-weight: normal; font-size: 30px; }

        .overlay {
            position: absolute;
            inset: 0;
            background: rgb(0, 45, 97, 0.88);
            z-index: -1;
        }
    </style>
</head>

<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div style="text-align: left; padding: 20px 40px 0;">
    <?php if ($isAdmin || $isWorker): ?>
        <h2 style="color:white; font-size:clamp(22px,4vw,32px); font-weight:700;">
            Welcome back, <?php echo htmlspecialchars($person->get_first_name()); ?>!
        </h2>
        <p style="color:white; font-size:clamp(14px,2vw,16px); margin-top:10px;">Search the catalog or use the staff panel to manage materials.</p>
    <?php endif; ?>
    <?php if ($isGuest): ?>
        <h2 style="color:white; font-size:clamp(22px,4vw,32px); font-weight:700;">Welcome to the Seacobeck Curriculum Lab!</h2>
        <p style="color:white; font-size:clamp(14px,2vw,16px); margin-top:10px;">Search our catalog to find available materials for checkout.</p>
    <?php endif; ?>
</div>

<div style="display: flex; justify-content: center; margin: 40px 0 0; padding: 0 16px;">
    <div style="width:100%; max-width: 900px; border: 3px solid #0067A2; border-radius: 16px; padding: 16px 18px; background-color: #8DC9F7;">
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

<?php require 'footer.php'; ?>
</body>
</html>
