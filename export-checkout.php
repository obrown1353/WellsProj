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
include_once('database/dbCheckout.php');
include_once('database/dbMaterials.php');

$accessLevel = (int) $_SESSION['access_level'];
$isGuest     = ($accessLevel === 0);
$isWorker    = ($accessLevel === 1);
$isAdmin     = ($accessLevel >= 2);

// Only workers and admins can view checkouts
if ($isGuest) {
    header('Location: index.php');
    die();
}

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}

// Fetch all checkouts and materials for name lookup
$allCheckouts = fetch_all_checkouts();
$allMaterials = fetch_all_materials();

$materialMap = [];
foreach ($allMaterials as $mat) {
    $materialMap[$mat->getMaterialId()] = $mat->getName();
}

if ($_POST['export-type'] == "csv"){
    $filename = "checkouts.csv";
    $fp = fopen('php://output','w');
    $header_line = array("Material","First Name","Last Name","Email","Checkout Date", "Due Date", "Status");
    fputcsv($fp, $header_line);

    foreach ($allCheckouts as $checkout){
        if($checkout->isOverdue()){
            $status = "Overdue";
        } else {
            $status = "Active";
        }
        $line = array($materialMap[$checkout->getMaterialId()],
                    $checkout->getFirstName(), 
                    $checkout->getLastName(),
                    $checkout->getEmail(), 
                    $checkout->getCheckoutDate(),
                    $checkout->getDueDate(), 
                    $status);
        fputcsv($fp, $line);
    }
    fclose($fp);
    header('Content-type:application/csv');
    header('Content-disposition:attachment;filename="'. $filename.'"');
}

if ($_POST['export-type'] == "excel"){
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=checkouts.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<html><head><meta charset='UTF-8'></head><body>";
    echo "<table border='1' style='border-collapse: collapse; font-family: Arial, sans-serif; text-align: center;'>";

    echo "<tr><th colspan='7' style='font-size: 18px; background-color: #002D61; color: white; padding: 10px;'>Checkouts Report</th></tr>";

    echo "<tr>
        <th style='background-color: #8DC9F7; padding: 5px;'>Material</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>First Name</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>Last Name</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>Email</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>Checkout Date</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>Due Date</th>
        <th style='background-color: #8DC9F7; padding: 5px;'>Status</th>
      </tr>";

    foreach ($allCheckouts as $checkout){
        if($checkout->isOverdue()){
            $status = "Overdue";
        } else {
            $status = "Active";
        }
        echo "<tr>
            <td style='background-color: #EAEAEA; padding: 5px; text-align: center;'>{$materialMap[$checkout->getMaterialId()]}</td>
            <td style='padding: 5px;'>{$checkout->getFirstName()}</td>
            <td style='padding: 5px;'>{$checkout->getLastName()}</td>
            <td style='padding: 5px;'>{$checkout->getEmail()}</td>
            <td style='padding: 5px;'>{$checkout->getCheckoutDate()}</td>
            <td style='padding: 5px;'>{$checkout->getDueDate()}</td>
            <td style='padding: 5px;'>{$status}</td>
          </tr>";
    }
    echo "</table>";
    echo "</body></html>";
    exit();
}
?>