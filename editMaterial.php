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

if (isset($_GET['material_id'])){
  $id = $_GET['material_id'] ?? '';
}

$args = sanitize($_POST);

if (!empty($_POST)) {
    $updated_material = new materials(
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

    if(update_material($updated_material)){
        $log = new Log(
                null, 
                $log_type = "catalog", 
                $message = "Material: " . $args['name'] .  " has been updated", 
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

$material = fetch_material_by_id($id);

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
/*    background-color: #002D61; */
    min-height: 100vh;
    padding-top: 95px;
    color: white;
/*    display: flex; */
/*    flex-direction: column; */
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
<div class="overlay"></div>
<div class="page-wrapper">

    <h1 class="page-heading">Edit <?php echo htmlspecialchars($material->getName())?></h1>

    <div class = "edit-wrapper">
        <div class = "edit-box">
        <form method="POST" action="editMaterial.php?<?php echo http_build_query(['material_id' => $material->getMaterialID()]); ?>">
            <div class="edit-inner">
                <label class="edit-label" for="name">Name:</label>
                <input class="edit-input" type="text" id="name" name="name" value="<?php echo $material->getName(); ?>" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="location">Location:</label>
                <input class="edit-input" type="text" id="location" name="location" value="<?php echo $material->getLocation(); ?>" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="resource_type">Resource Type:</label>
                <input class="edit-input" type="text" id="resource_type" name="resource_type" value="<?php echo $material->getResourceType(); ?>" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="isbn">ISBN:</label>
                <input class="edit-input" id="isbn" name="isbn"
                    value = "<?php if ($material->getISBN()){
                        echo $material->getISBN();
                        } ?>" />
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="author">Author:</label>
                <input class="edit-input" id="author" name="author"
                    value = "<?php if ($material->getAuthor()){
                        echo $material->getAuthor();
                        } ?>" />
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="description">Description:</label>
                <input class="edit-input" id="description" name="description" 
                    value = "<?php if ($material->getDescription()){
                        echo $material->getDescription();
                        } ?>" />
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_capacity">Copy Capacity:</label>
                <input class="edit-input" type="text" id="copy_capacity" name="copy_capacity" value="<?php echo $material->getCopyCapacity(); ?>" required/>
            </div>
            <div class="edit-inner">
                <label class="edit-label" for="copy_instock">Copy Instock:</label>
                <input class="edit-input" type="text" id="copy_instock" name="copy_instock" value="<?php echo $material->getCopyInstock(); ?>" required/>
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
