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

$type = $_GET['type'] ?? 'all';
$logs = [];
if (isset($_GET['type']) && $_GET['type'] != 'all') {
    $logs = fetch_logs_by_type($_GET['type']);
} else {
    $logs = fetch_all_logs();
}

$logs = array_values($logs);

$perPage     = 10;
$totalItems  = count($logs);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * $perPage;
$pageLogs = array_slice($logs, $offset, $perPage);

function buildUrl($page, $type) {
    $parts = [
        'page='  . $page,
        'type=' . urlencode($type),
    ];
    return 'viewLogs.php?' . implode('&', $parts);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | Materials Catalog</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

body {
    background-color: #002D61;
    min-height: 100vh;
    padding-top: 95px;
    color: white;
    justify-content: space-between;
    background-image: url('images/library.jpg');
    background-size: cover;
    background-position: center;
    position: relative;
    overflow-x: hidden;
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgb(0, 45, 97, 0.88);
    z-index: -1;
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

.section-heading {
    font-size: 22px;
    font-weight: 700;
    color: #8DC9F7;
    margin-bottom: 14px;
}

.badge {
    background: #8DC9F7;
    color: #002D61;
    padding: 2px 15px;
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

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 28px;
    flex-wrap: wrap;
}

.page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    border-radius: 8px;
    border: 2px solid #8DC9F7;
    background: transparent;
    color: white;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.18s, color 0.18s;
}

.page-btn:hover { background: #8DC9F7; color: #002D61; }
.page-btn.active { background: #8DC9F7; color: #002D61; pointer-events: none; }
.page-btn.disabled { opacity: 0.3; pointer-events: none; }

.ellipsis { color: white; align-self: center; font-weight: 700; font-size: 16px; padding: 0 2px; }

.jump-input {
    width: 76px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 2px solid #8DC9F7;
    background: transparent;
    color: white;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
}

.jump-input::placeholder { color: rgba(255,255,255,0.45); }


/* ================= MOBILE FIXES ================= */
@media (max-width: 768px) {

    .page-wrapper {
        padding: 20px 12px 60px;
    }

    .page-heading {
        font-size: 22px;
        text-align: center;
    }

    .page-subheading {
        text-align: center;
    }

    form[method="GET"] {
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px !important;
    }

    div[style*="display: flex"] {
        flex-direction: column !important;
        gap: 15px;
        align-items: center;
    }

    .section-heading form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
        text-align: center;
    }

    input[type="date"] {
        width: 100%;
        max-width: 250px;
    }

    table {
        font-size: 13px;
    }

    th, td {
        padding: 8px;
    }

    .badge,
    button {
        padding: 10px 14px;
        font-size: 14px;
    }

    input[type="radio"] {
        transform: scale(1.2);
    }
}
</style>

</head>

<body>

<?php require 'header.php'; ?>
<div class="overlay"></div>

    <div class="page-wrapper">

        <h1 class="page-heading">Logs</h1>
        <p class="page-subheading">Browse all logs.</p>

        <h2 class="section-heading">
        📚 Logs

            <div style="display: flex; justify-content: center; margin-top: -20px;">
            <form method="GET" style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
            <span style="font-weight: bold;">Filter by Log Type:</span>

            <label><input type="radio" name="type" value="all" onchange="this.form.submit()" <?php if ($type === 'all') echo 'checked'; ?>> All</label>
            <label><input type="radio" name="type" value="system" onchange="this.form.submit()" <?php if ($type === 'system') echo 'checked'; ?>> System</label>
            <label><input type="radio" name="type" value="checkouts" onchange="this.form.submit()" <?php if ($type === 'checkouts') echo 'checked'; ?>> Checkouts</label>
            <label><input type="radio" name="type" value="catalog" onchange="this.form.submit()" <?php if ($type === 'catalog') echo 'checked'; ?>> Catalog</label>

            </form>
            </div>
        </h2>

<div style="display: flex;">

    <h2 class="section-heading" style="margin-right: 20px">
        <form action="deleteLogs.php" method="POST">
        Delete Logs Before Date:
        <input type="date" class="badge" name="selected_date">
        <button type="submit" name="date_delete" class="badge">Delete</button>
        </form>
    </h2>

    <h2 class="section-heading">
        <form id="bulkDeleteForm" action="deleteLogs.php" method="POST">
        Delete Logs By Selection:
        <button type="submit" name="bulk_delete" class="badge">Delete Selected</button>
        
    </h2>

</div>

<div class="table-wrapper">
    <table>
        <thead>
        <tr>
        <th><input type="checkbox" id="selectAll"></th>
        <th>Log Type</th>
        <th>Message</th>
        <th>Log Time</th>
        </tr>
        </thead>

    <tbody>
        <?php if (empty($pageLogs)): ?>
        <tr>
        <td colspan="4">
        <div class="empty-state">No logs found.</div>
        </td>
        </tr>
        <?php else: ?>
            <?php foreach ($pageLogs as $log): ?>
            <tr>
            <td><input type="checkbox" class="rowCheckbox" name="selected_logs[]" value="<?= $log->getLogID() ?>"></td>
            <td class="material-name"><?= htmlspecialchars($log->getLogType()); ?></td>
            <td><?= htmlspecialchars($log->getMessage()); ?></td>
            <td><?= htmlspecialchars($log->getLogTime()); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>

    </table>
</div>
            </form>

    <?php if ($totalPages > 1):
        $win = 2;
    ?>
    <div class="pagination">
        <a href="<?php echo buildUrl($currentPage - 1, $type); ?>"
           class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">&#8592; Prev</a>

        <?php
        if ($currentPage > $win + 2) {
            echo '<a href="' . buildUrl(1, $type) . '" class="page-btn">1</a>';
            if ($currentPage > $win + 3) echo '<span class="ellipsis">…</span>';
        }
        for ($p = max(1, $currentPage - $win); $p <= min($totalPages, $currentPage + $win); $p++) {
            $cls = $p === $currentPage ? 'active' : '';
            echo '<a href="' . buildUrl($p, $type) . '" class="page-btn ' . $cls . '">' . $p . '</a>';
        }
        if ($currentPage < $totalPages - $win - 1) {
            if ($currentPage < $totalPages - $win - 2) echo '<span class="ellipsis">…</span>';
            echo '<a href="' . buildUrl($totalPages, $type) . '" class="page-btn">' . $totalPages . '</a>';
        }
        ?>

        <a href="<?php echo buildUrl($currentPage + 1, $type); ?>"
           class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">Next &#8594;</a>

        <form method="GET" action="viewLogs.php" style="display:flex; align-items:center; gap:6px; margin-left:8px;">
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <input type="number" name="page" min="1" max="<?php echo $totalPages; ?>"
                   class="jump-input" placeholder="Go to…">
            <button type="submit" class="page-btn" style="padding:0 14px;">Go</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('.rowCheckbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

document.querySelectorAll('.rowCheckbox').forEach(cb => {
    cb.addEventListener('change', () => {});
});
</script>

<?php require 'footer.php'; ?>

</body>
</html>
