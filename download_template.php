<?php
// Only allow admins to download
session_start();
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: index.php');
    die();
}

// Headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="materials_template.csv"');

// CSV template content
$template = [
    ["name", "location", "resource_type", "isbn", "author", "description", "copy_capacity", "copy_instock"],
    ["Book A", "Room 101", "Book", "1234567890", "John Smith", "Sample description", "10", "5"],
    ["Book B", "Room 202", "Book", "0987654321", "Sarah Lee", "Another description", "8", "2"]
];

// Output CSV rows
foreach ($template as $row) {
    echo implode(",", $row) . "\n";
}
exit;