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

// Search
$searchQuery = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';

function matchesSearch($checkout, $materialMap, $query) {
    if ($query === '') return true;
    $matName  = strtolower($materialMap[$checkout->getMaterialId()] ?? '');
    $first    = strtolower($checkout->getFirstName());
    $last     = strtolower($checkout->getLastName());
    $email    = strtolower($checkout->getEmail());
    return str_contains($matName, $query)
        || str_contains($first, $query)
        || str_contains($last, $query)
        || str_contains($email, $query);
}

$filteredCheckouts = array_filter($allCheckouts, fn($c) => matchesSearch($c, $materialMap, $searchQuery));

$today      = new DateTime();
$checkedOut = [];
$overdue    = [];

foreach ($filteredCheckouts as $checkout) {
    $due = new DateTime($checkout->getDueDate());
    if ($due < $today) {
        $overdue[]    = $checkout;
    } else {
        $checkedOut[] = $checkout;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | View Checkouts</title>
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
    color: white;
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
    display: flex;
    gap: 0;
}
.search-inner {
    position: relative;
    width: 100%;
}
.search-input {
    width: 100%;
    padding: 11px 130px 11px 16px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 20px;
    outline: none;
    color: #0067A2;
    font-family: 'Quicksand', sans-serif;
    font-weight: 600;
}
.search-input::placeholder { color: #5aa5d4; }
.search-btn {
    position: absolute;
    right: 0; top: 0;
    height: 100%;
    width: 120px;
    border: 1px solid #ccc;
    border-radius: 0 20px 20px 0;
    background: #0067A2;
    color: white;
    font-size: 15px;
    font-family: 'Quicksand', sans-serif;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
}
.search-btn:hover { background: #004f80; }

.section-heading {
    font-size: 22px;
    font-weight: 700;
    color: #8DC9F7;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.section-heading .badge {
    background: #8DC9F7;
    color: #002D61;
    font-size: 13px;
    font-weight: 700;
    padding: 2px 10px;
    border-radius: 20px;
}
.section-heading.overdue-heading { color: #f87171; }
.section-heading.overdue-heading .badge { background: #f87171; color: white; }

.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border-radius: 14px;
    margin-bottom: 48px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
}
table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(141,201,247,0.08);
    font-size: 14px;
}
thead tr {
    background: #8DC9F7;
    color: #002D61;
}
thead th {
    padding: 14px 16px;
    text-align: left;
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}
tbody tr {
    border-bottom: 1px solid rgba(141,201,247,0.15);
    transition: background 0.15s;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: rgba(141,201,247,0.12); }
tbody td {
    padding: 13px 16px;
    color: rgba(255,255,255,0.88);
    vertical-align: middle;
}
.material-name {
    font-weight: 700;
    color: #8DC9F7;
}
.overdue-row td { color: #fca5a5; }
.overdue-row .material-name { color: #f87171; }
.due-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
}
.due-badge.ok { background: rgba(141,201,247,0.2); color: #8DC9F7; }
.due-badge.overdue { background: rgba(248,113,113,0.2); color: #f87171; }

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: rgba(255,255,255,0.4);
    font-size: 15px;
}
.empty-state svg {
    width: 48px; height: 48px;
    margin-bottom: 12px;
    opacity: 0.3;
}

.divider {
    width: 90%;
    height: 1px;
    background: rgba(141,201,247,0.25);
    margin: 0 auto 48px;
}

.footer {
    width: 100%;
    background: #8DC9F7;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 30px 50px;
    flex-wrap: wrap;
    gap: 20px;
}
.footer-left { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.footer-logo { width: 150px; }
.social-icons { display: flex; gap: 15px; }
.social-icons a { color: white; font-size: 20px; text-decoration: none; transition: color 0.3s; }
.social-icons a:hover { color: #002D61; }
.footer-right { display: flex; gap: 50px; flex-wrap: wrap; }
.footer-section { display: flex; flex-direction: column; gap: 10px; color: white; font-size: 16px; font-weight: 500; }
.footer-topic { font-size: 18px; font-weight: 700; }
.footer a { color: white; text-decoration: none; padding: 5px 10px; border-radius: 5px; transition: background 0.2s; }
.footer a:hover { background: rgba(255,255,255,0.15); }

@media (max-width: 700px) {
    .footer { flex-direction: column; align-items: center; text-align: center; }
    .footer-right { flex-direction: column; align-items: center; gap: 24px; }
    .page-heading { font-size: 22px; }
}
</style>
</head>
<body>

<?php require 'header.php'; ?>

<div class="page-wrapper">

    <h1 class="page-heading">View Checkouts</h1>
    <p class="page-subheading">Browse all currently checked-out materials. Use the search bar to filter by name, email, or material.</p>

    <!-- Search -->
    <div class="search-wrapper">
        <div class="search-box">
            <form action="viewCheckouts.php" method="GET" style="width:100%;">
                <div class="search-inner">
                    <input
                        type="text"
                        name="query"
                        class="search-input"
                        placeholder="Search by material, name, or email…"
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                    >
                    <button type="submit" class="search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overdue -->
    <h2 class="section-heading overdue-heading">
        ⚠ Overdue
        <span class="badge"><?php echo count($overdue); ?></span>
    </h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Date Checked Out</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($overdue)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p>No overdue items<?php echo $searchQuery ? ' matching your search' : ''; ?>.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($overdue as $co): ?>
                    <tr class="overdue-row">
                        <td class="material-name"><?php echo htmlspecialchars($materialMap[$co->getMaterialId()] ?? 'Unknown (#' . $co->getMaterialId() . ')'); ?></td>
                        <td><?php echo htmlspecialchars($co->getFirstName()); ?></td>
                        <td><?php echo htmlspecialchars($co->getLastName()); ?></td>
                        <td><?php echo htmlspecialchars($co->getEmail()); ?></td>
                        <td><?php echo htmlspecialchars($co->getCheckoutDate()); ?></td>
                        <td>
                            <?php echo htmlspecialchars($co->getDueDate()); ?>
                            <span class="due-badge overdue">Overdue</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Checked Out -->
    <h2 class="section-heading">
        📚 Checked Out
        <span class="badge"><?php echo count($checkedOut); ?></span>
    </h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Date Checked Out</th>
                    <th>Due Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($checkedOut)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <p>No active checkouts<?php echo $searchQuery ? ' matching your search' : ''; ?>.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($checkedOut as $co): ?>
                    <tr>
                        <td class="material-name"><?php echo htmlspecialchars($materialMap[$co->getMaterialId()] ?? 'Unknown (#' . $co->getMaterialId() . ')'); ?></td>
                        <td><?php echo htmlspecialchars($co->getFirstName()); ?></td>
                        <td><?php echo htmlspecialchars($co->getLastName()); ?></td>
                        <td><?php echo htmlspecialchars($co->getEmail()); ?></td>
                        <td><?php echo htmlspecialchars($co->getCheckoutDate()); ?></td>
                        <td>
                            <?php echo htmlspecialchars($co->getDueDate()); ?>
                            <span class="due-badge ok">Active</span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<div class="divider"></div>

<?php require 'footer.php'; ?>
<script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>
</body>
</html>