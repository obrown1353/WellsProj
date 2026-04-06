<?php
session_cache_expire(30);
session_start();

include_once('database/dbPersons.php');
include_once('domain/Person.php');
include_once('database/dbLogs.php');
include_once('domain/Logs.php');


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
if (isset($_POST['bulk_delete']) && isset($_POST['selected_logs']) && is_array($_POST['selected_logs'])) {
    $ids = array_map('intval', $_POST['selected_logs']);
    delete_logs_by_ids($ids);
    header('Location: viewLogs.php');
    exit;
}

// DELETE BY DATE
if (isset($_POST['date_delete']) && isset($_POST['selected_date'])) {
    $date = strtotime($_POST['selected_date']);
    $date = date('Y-m-d H:i:s', $date);
    delete_logs_before_date($date);
    header('Location: viewLogs.php');
    exit;
}

header('Location: viewLogs.php');
exit;
