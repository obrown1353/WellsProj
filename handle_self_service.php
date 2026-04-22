<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('include/input-validation.php');
include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";
include_once "database/dbLogs.php";
include_once "database/dbstats.php";

$args = sanitize($_POST);

$id         = $args["id"];
$first_name = $args["first_name"] ?? '';
$last_name  = $args["last_name"]  ?? '';
$email      = $args["email"]      ?? '';
$full_name  = trim($first_name . ' ' . $last_name);

$checkout_date = date('Y-m-d H:i:s');
$due_date      = date('Y-m-d', strtotime($checkout_date . ' + 14 days'));
$due_date_nice = date('l, F j, Y', strtotime($due_date));

$material = fetch_material_by_id($id);
$title    = $material ? $material->getName() : 'Unknown Item';

require __DIR__ . '/emailfunctions.php';

// ── Handle Checkout ────────────────────────────────────────────────────────
if (isset($_POST['Checkout'])) {
    if ($material->canBeCheckedOut()) {
        $success = new_checkout($id, $first_name, $last_name, $email, $checkout_date, $due_date);
        if ($success) {
            self_service_update($id, true);
            update_for_checkout($id);
            $html  = checkoutEmailHTML($full_name, $title, $email, $due_date_nice);
            $plain = checkoutEmailPlain($full_name, $title, $email, $due_date_nice);
            $emailSent = sendEmail($email, $full_name, 'Seacobeck Library – Checkout Confirmation', $html, $plain);
            $status = $emailSent ? 'checkout_success' : 'checkout_no_email';

            $log = new Log(null, "checkouts", $full_name . " has checked out " . $title, date('Y-m-d H:i:s'));
            new_log($log);
        } else {
            $status = 'checkout_fail';
        }
    } else {
        $status = 'cant_checkout';
    }

// ── Handle Return ──────────────────────────────────────────────────────────
} else if (isset($_POST['Return'])) {
    if ($material->canBeReturned()) {
        $success = remove_checkout($id, $email);
        if ($success) {
            self_service_update($id, false);
            update_for_return($id);

            $html  = returnEmailHTML($full_name, $title, $email);
            $plain = returnEmailPlain($full_name, $title, $email);
            $emailSent = sendEmail($email, $full_name, 'Seacobeck Library – Return Confirmation', $html, $plain);
            $status = $emailSent ? 'return_success' : 'return_no_email';

            $log = new Log(null, "checkouts", ($full_name . " has returned " . $title), date('Y-m-d H:i:s'));
            new_log($log);
        } else {
            $status = 'return_fail';
        }
    } else {
        $status = 'cant_return';
    }
}

// ── Redirect immediately ───────────────────────────────────────────────────
$redirect_url = 'self_service.php?status=' . ($status ?? 'unknown') . '&material_id=' . $id;
header('Location: ' . $redirect_url);
exit;