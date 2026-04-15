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

$do_scheduled = false;

// ── Handle Checkout ────────────────────────────────────────────────────────
if (isset($_POST['Checkout'])) {
    if ($material->canBeCheckedOut()) {
        $success = new_checkout($id, $first_name, $last_name, $email, $checkout_date, $due_date);
        if ($success) {
            self_service_update($id, true);
            update_for_checkout($id); //updates stats
            $html  = checkoutEmailHTML($full_name, $title, $email, $due_date_nice);
            $plain = checkoutEmailPlain($full_name, $title, $email, $due_date_nice);
            $emailSent = sendEmail($email, $full_name, 'Seacobeck Library – Checkout Confirmation', $html, $plain);
            $status = $emailSent ? 'checkout_success' : 'checkout_no_email';

            $log = new Log(null, "checkouts", $full_name . " has checked out " . $title, date('Y-m-d H:i:s'));
            new_log($log);

            $do_scheduled = true;

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
            update_for_return($id); //updates stats

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

// ── Flush redirect to browser immediately, then keep running ──────────────
$redirect_url = 'self_service.php?status=' . ($status ?? 'unknown') . '&material_id=' . $id;

ignore_user_abort(true);
set_time_limit(300);

ob_start();
header('Location: ' . $redirect_url);
header('Connection: close');
header('Content-Length: 0');
ob_end_flush();
flush();

// Scheduled emails 
if ($do_scheduled) {

    // start of demo 
    sleep(60);
    $still_out = array_filter(fetch_checkout_by_material_id($id), fn($c) => strtolower($c->getEmail()) === strtolower($email));
    if (!empty($still_out)) {
        $html  = reminderEmailHTML($full_name, $title, $email, $due_date_nice);
        $plain = reminderEmailPlain($full_name, $title, $email, $due_date_nice);
        sendEmail($email, $full_name, 'Reminder: Your library item is due in 1 week', $html, $plain);
    }

    sleep(60);
    $still_out = array_filter(fetch_checkout_by_material_id($id), fn($c) => strtolower($c->getEmail()) === strtolower($email));
    if (!empty($still_out)) {
        $html  = dueTodayEmailHTML($full_name, $title, $email, $due_date_nice);
        $plain = dueTodayEmailPlain($full_name, $title, $email, $due_date_nice);
        sendEmail($email, $full_name, 'Reminder: Your library item is due today', $html, $plain);
    }

    sleep(60);
    $still_out = array_filter(fetch_checkout_by_material_id($id), fn($c) => strtolower($c->getEmail()) === strtolower($email));
    if (!empty($still_out)) {
        $html  = overdueEmailHTML($full_name, $title, $email, $due_date_nice);
        $plain = overdueEmailPlain($full_name, $title, $email, $due_date_nice);
        sendEmail($email, $full_name, 'Notice: Your library item is overdue', $html, $plain);
    }
    // end of demo

    // ── after demo replace with this (8am est on day 7 / 14 / 15) 
    // $tz = new DateTimeZone('America/New_York');
    // function seconds_until_8am(string $from, int $days, DateTimeZone $tz): int {
    //     $target = new DateTime($from, $tz);
    //     $target->modify("+{$days} days")->setTime(8, 0, 0);
    //     return max($target->getTimestamp() - time(), 0);
    // }
    // set_time_limit(1400000);
    //
    // sleep( seconds_until_8am($checkout_date, 7, $tz) );
    // $html  = reminderEmailHTML($full_name, $title, $email, $due_date_nice);
    // $plain = reminderEmailPlain($full_name, $title, $email, $due_date_nice);
    // sendEmail($email, $full_name, 'Reminder: Your library item is due in 1 week', $html, $plain);
    //
    // sleep( seconds_until_8am($checkout_date, 14, $tz) - seconds_until_8am($checkout_date, 7, $tz) );
    // $html  = dueTodayEmailHTML($full_name, $title, $email, $due_date_nice);
    // $plain = dueTodayEmailPlain($full_name, $title, $email, $due_date_nice);
    // sendEmail($email, $full_name, 'Reminder: Your library item is due today', $html, $plain);
    //
    // sleep( seconds_until_8am($checkout_date, 15, $tz) - seconds_until_8am($checkout_date, 14, $tz) );
    // $html  = overdueEmailHTML($full_name, $title, $email, $due_date_nice);
    // $plain = overdueEmailPlain($full_name, $title, $email, $due_date_nice);
    // sendEmail($email, $full_name, 'Notice: Your library item is overdue', $html, $plain);
}