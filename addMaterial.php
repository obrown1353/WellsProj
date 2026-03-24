<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

require('include/input-validation.php');
include_once "database/dbMaterials.php";

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

    add_material($new_material);
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
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | Materials Catalog</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Quicksand', sans-serif; }

body {
    background-color: #002D61;
    min-height: 100vh;
    padding-top: 95px;
    color: white;
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
.page-subheading {
    font-size: 14px;
    color: rgba(255,255,255,0.65);
    margin-bottom: 32px;
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
    position: relative;
    width: 100%;
    margin-bottom: 18px;
}
.edit-input {
    width: 100%;
    padding: 11px 24px 11px 190px;
    font-size: 15px;
    border-radius: 20px;
    outline: none;
    color: #0067A2;
    font-weight: 600;
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
    cursor: pointer;
}
.edit-submit{
    border-radius: 20px 20px 20px 20px;
    background: #0067A2;
    color: white;
    font-weight: 700;
    width: 100px;
    padding: 11px 11px 11px 11px;
}
</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="page-wrapper">

    <h1 class="page-heading">Add New Material</h1>

    <div class = "edit-wrapper">
        <div class = "edit-box">
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
                <input class="edit-input" id="isbn" name="isbn"/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="author">Author:</label>
                <input class="edit-input" id="author" name="author"/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="description">Description:</label>
                <input class="edit-input" id="description" name="description" />
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_capacity">Copy Capacity:</label>
                <input class="edit-input" type="text" id="copy_capacity" name="copy_capacity" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_instock">Copy Instock:</label>
                <input class="edit-input" type="text" id="copy_instock" name="copy_instock" required/>
            </div>
            
            <div>
                <button class="edit-submit">
                    Save!
                </button>
            </div>
        </form>
        </div>
    </div>

</html>