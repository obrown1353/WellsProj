<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

include_once "database/dbMaterials.php";

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}

if (isset($_GET['material_id'])){
  $id = $_GET['material_id'] ?? '';
}

if (!empty($_POST)) {
    $updated_material = new materials(
        (int) $id, 
        (string)($_POST['name']), 
        (string)($_POST['location']), 
        (string)($_POST['resource_type']),
        (string)($_POST['isbn']),
        (string)($_POST['author']),
        (string)($_POST['description']),
        (int)($_POST['copy_capacity']),
        (int)($_POST['copy_instock']),
    );

    update_material($updated_material);
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

<html> WORK IN PROGRESS WILL FIX - HANNAH
    <form method="POST" action="editMaterial.php?<?php echo http_build_query(['material_id' => $material->getMaterialID()]); ?>">
    <div class="form-group">
        <label class="from-group__label" for="name">Name:</label>
        <input class="from-group__input" type="text" id="name" name="name" value="<?php echo htmlspecialchars($material->getName()); ?>" required/>
    </div>
    <div class="form-group">
        <label class="from-group__label" for="location">Location:</label>
        <input class="from-group__input" type="text" id="location" name="location" value="<?php echo htmlspecialchars($material->getLocation()); ?>" required/>
    </div>
    <div class="form-group">
        <label class="from-group__label" for="resource_type">Resource Type:</label>
        <input class="from-group__input" type="text" id="resource_type" name="resource_type" value="<?php echo htmlspecialchars($material->getResourceType()); ?>" required/>
    </div>
    <div class="form-group">
        <label class="from-group__label" for="isbn">ISBN:</label>
        <input class="from-group__input" id="isbn" name="isbn"
            value = "<?php if ($material->getISBN()){
                echo htmlspecialchars($material->getISBN());
                } ?>" />
    </div>
    <div class="form-group">
        <label class="from-group__label" for="author">Author:</label>
        <input class="from-group__input" id="author" name="author"
            value = "<?php if ($material->getAuthor()){
                echo htmlspecialchars($material->getAuthor());
                } ?>" />
    </div>
    <div class="form-group">
        <label class="from-group__label" for="description">Description:</label>
        <textarea class="from-group__input" id="description" name="description" rows="6">
            <?php if ($material->getDescription()){
                echo htmlspecialchars($material->getDescription());
                } ?>
        </textarea>
    </div>
    <div class="form-group">
        <label class="from-group__label" for="copy_capacity">Copy Capacity:</label>
        <input class="from-group__input" type="text" id="copy_capacity" name="copy_capacity" value="<?php echo htmlspecialchars($material->getCopyCapacity()); ?>" required/>
    </div>
    <div class="form-group">
        <label class="from-group__label" for="copy_instock">Copy Instock:</label>
        <input class="from-group__input" type="text" id="copy_instock" name="copy_instock" value="<?php echo htmlspecialchars($material->getCopyInstock()); ?>" required/>
    </div>
    
    <div class="form-submit">
        <button class="button">
            Save!
        </button>
    </div>
</form>

</html>