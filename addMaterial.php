<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

require('include/input-validation.php');
include_once "database/dbMaterials.php";
include_once "database/dbLogs.php";


if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}

$args = sanitize($_POST);

if (!empty($_POST)) {
    $new_material = new materials(
        $id, 
        ($args['name']), 
        ($args['location']), 
        ($args['resource_type']),
        ($args['isbn'] ?? null),
        ($args['author'] ?? null),
        ($args['description'] ?? null),
        (int)($args['copy_capacity']),
        (int)($args['copy_instock']),
    );

    if(add_material($new_material)){
        $log = new Log(
                null, 
                $log_type = "catalog", 
                $message = "Material: " . $args['name'] .  " has been added to catalog", 
                $log_time = date('Y-m-d H:i:s')
        );
        new_log($log);
    };
    header('Location: viewMaterials.php');
    die();
}

$accessLevel = (int) $_SESSION['access_level'];
$isGuest     = ($accessLevel === 0);
$isWorker    = ($accessLevel === 1);
$isAdmin     = ($accessLevel >= 2);

// Only workers and admins can view checkouts
if ($isGuest) {
    header('Location: index.php');
    die();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | Materials Catalog</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

body {
    min-height: 100vh;
    padding-top: 95px;
    color: white;
    justify-content: space-between;
    background-image: url('images/library.jpg');
    background-size: cover;
    background-position: center;
    position: relative;
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgb(0, 45, 97, 0.88);
    z-index: -1;
}

.page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 24px 80px;
}

.page-heading {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 25px;
    text-align: center;
}

.edit-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 36px;
}

.edit-box {
    width: 100%;
    max-width: 1000px;
    border: 3px solid #0067A2;
    border-radius: 16px;
    padding: 18px 24px;
    background-color: #8DC9F7;
}

.edit-inner {
    width: 100%;
    margin-bottom: 14px;
}

@media (min-width: 600px) {
    .edit-inner {
        position: relative;
    }
    .edit-label {
        position: absolute;
        height: 100%;
        width: 180px;
        border-radius: 20px 0 0 20px;
        padding: 11px 11px 11px 18px;
        background: #0067A2;
        color: white;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    .edit-input {
        width: 100%;
        padding: 11px 24px 11px 196px;
        font-size: 15px;
        border-radius: 20px;
        border: none;
        outline: none;
        color: #0067A2;
        font-weight: 600;
    }
    .edit-submit {
        border-radius: 20px;
        background: #0067A2;
        color: white;
        font-weight: 700;
        width: 120px;
        padding: 11px;
        font-size: 15px;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .edit-submit:hover {
        background: #005080;
    }
}

/* Mobile: stacked label above input */
@media (max-width: 599px) {
    .edit-box {
        padding: 14px 12px;
    }
    .edit-label {
        display: block;
        width: 100%;
        border-radius: 10px 10px 0 0;
        padding: 7px 14px;
        background: #0067A2;
        color: white;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
    }
    .edit-input {
        display: block;
        width: 100%;
        padding: 10px 14px;
        font-size: 15px;
        border-radius: 0 0 10px 10px;
        border: none;
        outline: none;
        color: #0067A2;
        font-weight: 600;
    }
    .edit-submit {
        display: block;
        width: 100%;
        padding: 13px;
        border-radius: 12px;
        background: #0067A2;
        color: white;
        font-weight: 700;
        font-size: 16px;
        border: none;
        cursor: pointer;
        margin-top: 4px;
        transition: background 0.2s;
    }
    .edit-submit:hover {
        background: #005080;
    }
}
</style>
</head>

<body>

<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper">

    <h1 class="page-heading">Add New Material</h1>

    <div class="edit-wrapper">
        <div class="edit-box">
        <form method="POST" action="addMaterial.php">
            <div class="edit-inner">
                <label class="edit-label" for="name">Name:</label>
                <input class="edit-input" type="text" id="name" name="name" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="location">Location:</label>
                <input class="edit-input" type="text" id="location" name="location" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="resource_type">Resource Type:</label>
                <input class="edit-input" type="text" id="resource_type" name="resource_type" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="isbn">ISBN:</label>
                <input class="edit-input" type="text" id="isbn" name="isbn"/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="author">Author:</label>
                <input class="edit-input" type="text" id="author" name="author"/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="description">Description:</label>
                <input class="edit-input" type="text" id="description" name="description"/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_capacity">Copy Capacity:</label>
                <input class="edit-input" type="number" id="copy_capacity" name="copy_capacity" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_instock">Copy Instock:</label>
                <input class="edit-input" type="number" id="copy_instock" name="copy_instock" required/>
            </div>

            <div>
                <button class="edit-submit" type="submit">
                    Save!
                </button>
            </div>
        </form>
        </div>
    </div>

</div>

<?php require 'footer.php'; ?>
</body>
</html>