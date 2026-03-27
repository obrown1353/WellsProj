<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";
include_once "database/dbLogs.php";

$id         = $_POST["id"];
$first_name = $_POST["first_name"] ?? '';
$last_name  = $_POST["last_name"]  ?? '';
$email      = $_POST["email"]      ?? '';
$full_name  = trim($first_name . ' ' . $last_name);

$checkout_date = date('Y-m-d H:i:s');
$due_date      = date('Y-m-d', strtotime($checkout_date . ' + 14 days'));
$due_date_nice = date('l, F j, Y', strtotime($due_date));

$material = fetch_material_by_id($id);
$title    = $material ? $material->getName() : 'Ungigknown Item';

$LOGO_URL = "https://umwathletics.com/images/logos/site/site.png";

require __DIR__ . '/emailfunctions.php';

// Handle Checkout
if (isset($_POST['Checkout'])) {
    if ($material->canBeCheckedOut()){
      $success = new_checkout($id, $first_name, $last_name, $email, $checkout_date, $due_date);
      if ($success) {
          self_service_update($id, true);
          $html  = checkoutEmailHTML($full_name, $title, $email, $due_date_nice, $LOGO_URL);
          $plain = "Hi $full_name,\n\nWe have received your confirmation!\n\nItem: $title\nReturn By: $due_date_nice\n\nWarm regards,\nThe Seacobeck Library Team";
          $emailSent = sendEmail($email, $full_name, 'Seacobeck Library - Checkout Confirmation', $html, $plain);
          $status = $emailSent ? 'checkout_success' : 'checkout_no_email';
          $log = new Log(
            null, 
            $log_type = "checkouts", 
            $message = $full_name . " has checked out " . $title, 
            $log_time = date('Y-m-d H:i:s')
          );
          new_log($log);
      } else {
          $status = 'checkout_fail';
      } 
    } else {
      $status = 'cant_checkout';
    }
    
// Handle Return
} else if (isset($_POST['Return'])) {
    if ($material->canBeReturned()){
      $success = remove_checkout($id, $email);
      if ($success) {
          self_service_update($id, false);
          $html  = returnEmailHTML($full_name, $title, $email, $LOGO_URL);
          $plain = "Hi $full_name,\n\nThank you for returning: $title.\n\nWarm regards,\nThe Seacobeck Library Team";
          $emailSent = sendEmail($email, $full_name, 'Seacobeck Library - Return Confirmation', $html, $plain);
          $status = $emailSent ? 'return_success' : 'return_no_email';
          $log = new Log(
            null, 
            $log_type = "checkouts", 
            $message = $full_name . " has returned " . $title, 
            $log_time = date('Y-m-d H:i:s')
          );
          new_log($log);
      } else {
          $status = 'return_fail';
      }
    } else {
      $status = 'cant_return';
    }
}

header('Location: self_service.php?status=' . ($status ?? 'unknown') . '&material_id=' . $id);
die();
?>