<?php
session_cache_expire(30);
session_start();

include_once('database/dbPersons.php');
include_once('domain/Person.php');
include_once('database/dbMaterials.php');
include_once('domain/Materials.php');


if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    exit();
}

$accessLevel = (int) $_SESSION['access_level'];
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}

// DELETE SELECTED
if (isset($_POST['bulk_delete']) && isset($_POST['selected_materials']) && is_array($_POST['selected_materials'])) {
    $ids = array_map('intval', $_POST['selected_materials']);
    delete_materials_by_ids($ids);
    header('Location: viewMaterials.php');
    exit;
}

header('Location: viewMaterials.php');
exit;
