<?php
require_once __DIR__ . '/database/dbMaterials.php';
require_once __DIR__ . '/database/dbCheckout.php';
require_once __DIR__ . '/emailfunctions.php';

date_default_timezone_set('America/New_York');

$checkouts = fetch_all_checkouts();
$today     = new DateTime(date('Y-m-d'));

foreach ($checkouts as $c) {
    $due      = new DateTime($c->getDueDate());
    $diff     = (int)$today->diff($due)->format('%r%a');
    $email    = $c->getEmail();
    $name     = trim($c->getFirstName() . ' ' . $c->getLastName());
    $due_nice = $due->format('l, F j, Y');

    $material = fetch_material_by_id($c->getMaterialID());
    $title    = $material ? $material->getName() : 'Unknown Item';

    if ($diff === 7) {
        $html  = reminderEmailHTML($name, $title, $email, $due_nice);
        $plain = reminderEmailPlain($name, $title, $email, $due_nice);
        sendEmail($email, $name, 'Reminder: Your library item is due in 1 week', $html, $plain);
    } elseif ($diff === 0) {
        $html  = dueTodayEmailHTML($name, $title, $email, $due_nice);
        $plain = dueTodayEmailPlain($name, $title, $email, $due_nice);
        sendEmail($email, $name, 'Reminder: Your library item is due today', $html, $plain);
    } elseif ($diff === -1) {
        $html  = overdueEmailHTML($name, $title, $email, $due_nice);
        $plain = overdueEmailPlain($name, $title, $email, $due_nice);
        sendEmail($email, $name, 'Notice: Your library item is overdue', $html, $plain);
    }
}