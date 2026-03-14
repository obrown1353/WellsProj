<?php

include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";

$id = $_POST["id"];

if (isset($_POST['Checkout'])){
  $checkout_date = date('Y-m-d H:i:s');
  $due_date = date('Y-m-d', strtotime($checkout_date. ' + 14 days'));
  $success = new_checkout($id, $_POST["first_name"], $_POST["last_name"], $_POST["email"], $checkout_date, $due_date);
  if ($success){
      self_service_update($id, true);
  }
} else if (isset($_POST['Return'])) {
  $success = remove_checkout($id, $_POST["email"]);
  if ($success){
    self_service_update($id, false);
  }
} 

header('Location: self_service.php');
die();
?>