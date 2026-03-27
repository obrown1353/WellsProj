<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

// Includes
include_once('database/dbPersons.php');
include_once('domain/Person.php');
include_once('database/dbLogs.php');
include_once('domain/Log.php');

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

$type = $_GET['type'] ?? '';
$logs = [];
if (isset($_GET['type']) && $_GET['type'] != 'all') {
    $logs = fetch_logs_by_type($_GET['type']);
} else {
    $logs = fetch_all_logs();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | System Logs</title>

<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Quicksand', sans-serif; }

body {
    background-color: #002D61;
    min-height: 100vh;
    padding-top: 95px;
    color: white;
}

.page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 24px 80px;
}

.page-heading {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 6px;
}
.page-subheading {
    font-size: 14px;
    color: rgba(255,255,255,0.65);
    margin-bottom: 32px;
}

.search-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 36px;
}
.search-box {
    width: 100%;
    max-width: 700px;
    border: 3px solid #0067A2;
    border-radius: 16px;
    padding: 18px 24px;
    background-color: #8DC9F7;
}
.search-inner {
    position: relative;
    width: 100%;
}
.search-input {
    width: 100%;
    padding: 11px 130px 11px 16px;
    font-size: 15px;
    border-radius: 20px;
    outline: none;
    color: #0067A2;
    font-weight: 600;
}
.search-btn {
    position: absolute;
    right: 0; top: 0;
    height: 100%;
    width: 120px;
    border-radius: 0 20px 20px 0;
    background: #0067A2;
    color: white;
    font-weight: 700;
    cursor: pointer;
}

.section-heading {
    font-size: 22px;
    font-weight: 700;
    color: #8DC9F7;
    margin-bottom: 14px;
}
.badge {
    background: #8DC9F7;
    color: #002D61;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
}
table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(141,201,247,0.08);
}
thead {
    background: #8DC9F7;
    color: #002D61;
}
th, td {
    padding: 12px;
    text-align: left;
}
tbody tr:hover {
    background: rgba(141,201,247,0.12);
}

.material-name {
    font-weight: 700;
    color: #8DC9F7;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: rgba(255,255,255,0.5);
}

.edit-button{
    background: #8DC9F7;
    color: #002D61;
    padding: 10px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700; 
}
</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="page-wrapper">

    <h1 class="page-heading">Logs</h1>
    <p class="page-subheading">
        Browse all logs.
    </p>

    <!-- Materials Table -->
    <h2 class="section-heading">
        📚 Logs
        <!-- Sort by -->
    <div style="display: flex; justify-content: center; margin-top: -20px;">

        <form action="viewLogs.php?" method="GET" style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
        <span style="font-weight: bold; white-space: nowrap;">Filter by: </span>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="type" value="all" onchange="this.form.submit()"
                <?php if ($type === 'all') echo 'checked'; ?>> All
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="type" value="system" onchange="this.form.submit()"
                <?php if ($type === 'system') echo 'checked'; ?>> System
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="type" value="checkouts" onchange="this.form.submit()"
                <?php if ($type === 'checkouts') echo 'checked'; ?>> Checkouts
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="type" value="catalog" onchange="this.form.submit()"
                <?php if ($type === 'catalog') echo 'checked'; ?>> Catalog
        </label>
        </form>

    </div>
    </h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Log Type</th>
                    <th>Message</th>
                    <th>Log Time</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="3">
                        <div class="empty-state">
                            No logs found.
                        </div>
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="material-name" style="width:15%">
                        <?php echo htmlspecialchars($log->getLogType()); ?>
                    </td>
                    <td style="width:70%"><?php echo htmlspecialchars($log->getMessage()); ?></td>
                    <td style="width:15%"><?php echo htmlspecialchars($log->getLogTime()); ?></td>
                </tr>
                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>
</div> <!-- END page-wrapper -->

<div class="divider"></div>

<?php require 'footer.php'; ?>

</body>
</html>