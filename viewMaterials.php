<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

include_once('database/dbPersons.php');
include_once('domain/Person.php');
include_once('database/dbMaterials.php');
include_once('domain/Materials.php');
include_once('database/dbstats.php');
include_once('domain/Stats.php');

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

$searchQuery       = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';
$sort              = $_GET['sort'] ?? 'material_id';
$selectedLocations = $_GET['location'] ?? [];
$selectedTypes     = $_GET['resource_type'] ?? [];

$allMaterials = fetch_materials_by_query($searchQuery, $sort);

$locations = [];
$types = [];
foreach ($allMaterials as $m) {
    $loc  = $m->getLocation();
    $type = $m->getResourceType();
    if ($loc  && !isset($locations[$loc]))  $locations[$loc]  = $loc;
    if ($type && !isset($types[$type]))     $types[$type]     = $type;
}
ksort($locations);
ksort($types);

if (!empty($selectedLocations) || !empty($selectedTypes)) {
    $allMaterials = array_filter($allMaterials, function($m) use ($selectedLocations, $selectedTypes) {
        $matchLoc  = empty($selectedLocations) || in_array($m->getLocation(), $selectedLocations);
        $matchType = empty($selectedTypes)     || in_array($m->getResourceType(), $selectedTypes);
        return $matchLoc && $matchType;
    });
}

$allMaterials = array_values($allMaterials);

$perPage     = 10;
$totalItems  = count($allMaterials);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * $perPage;
$pageMaterials = array_slice($allMaterials, $offset, $perPage);

function buildUrl($page, $query, $sort, $locations, $types) {
    $parts = [
        'page='  . $page,
        'query=' . urlencode($query),
        'sort='  . urlencode($sort),
    ];
    foreach ($locations as $l) $parts[] = 'location[]=' . urlencode($l);
    foreach ($types     as $t) $parts[] = 'resource_type[]=' . urlencode($t);
    return 'viewMaterials.php?' . implode('&', $parts);
}

$hasFilters  = !empty($selectedLocations) || !empty($selectedTypes);
$filterCount = count($selectedLocations) + count($selectedTypes);
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
    min-height: 100vh;
    padding-top: 95px;
    color: white;
    background-image: url('images/library.jpg');
    background-size: cover;
    background-position: center;
    position: relative;
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgb(0, 45, 97, 0.88);
    z-index: -1;
}

.page-wrapper {
    max-width: 1280px;
    margin: 0 auto;
    padding: 40px 28px 80px;
}

.page-heading { font-size: 26px; font-weight: 700; margin-bottom: 4px; }

.page-subheading { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 24px; }

.search-card {
    border: 3px solid #0067A2;
    border-radius: 16px;
    padding: 16px 18px;
    background-color: #8DC9F7;
    margin-bottom: 16px;
}

.search-inner { position: relative; width: 100%; }

.search-input {
    width: 100%;
    padding: 11px 120px 11px 16px;
    font-size: 15px;
    border-radius: 20px;
    outline: none;
    color: #0067A2;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    border: 1px solid #ccc;
}

.search-btn {
    position: absolute;
    right: 0; top: 0;
    height: 100%;
    width: 110px;
    border-radius: 0 20px 20px 0;
    background: #0067A2;
    color: white;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    border: none;
    transition: background 0.2s;
}

.search-btn:hover { background: #005080; }

.controls-bar {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}

.sort-label { font-weight: 700; font-size: 14px; white-space: nowrap; }

.sort-option {
    font-size: 13px;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    color: white;
    font-weight: 400;
}

.divider {
    width: 1px;
    height: 18px;
    background: rgba(255,255,255,0.25);
    flex-shrink: 0;
}

.filter-toggle {
    display: flex;
    align-items: center;
    gap: 7px;
    background: transparent;
    border: 2px solid #8DC9F7;
    border-radius: 20px;
    color: white;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 700;
    padding: 5px 14px;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    white-space: nowrap;
}

.filter-toggle:hover,
.filter-toggle.active { background: #8DC9F7; color: #002D61; }

.filter-count {
    background: #002D61;
    color: #8DC9F7;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    display: none;
}

.filter-count.visible { display: inline-block; }

.filter-arrow { font-size: 9px; transition: transform 0.2s; }
.filter-toggle.open .filter-arrow { transform: rotate(180deg); }

.filter-panel {
    display: none;
    background: #0067A2;
    border: 2px solid #8DC9F7;
    border-radius: 14px;
    padding: 20px 20px 16px;
    margin-bottom: 18px;
    animation: fadeIn 0.15s ease;
}

.filter-panel.open { display: block; }

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.filter-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 40px;
}

.filter-group-title {
    font-size: 13px;
    font-weight: 700;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.check-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px 16px;
}

.check-columns.single-col { grid-template-columns: 1fr; }

.check-row { display: flex; align-items: center; gap: 8px; }

.check-row input[type="checkbox"] {
    accent-color: #8DC9F7;
    cursor: pointer;
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.check-row label { font-size: 13px; color: white; cursor: pointer; font-weight: 400; line-height: 1.3; }

.filter-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-top: 18px;
    padding-top: 16px;
    border-top: 1px solid rgba(255,255,255,0.2);
}

.apply-btn {
    padding: 9px 24px;
    border-radius: 10px;
    border: none;
    background: #8DC9F7;
    color: #002D61;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
}

.apply-btn:hover { background: white; }

.clear-link {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    text-decoration: underline;
    cursor: pointer;
    background: none;
    border: none;
    font-family: 'Inter', sans-serif;
    padding: 0;
}

.clear-link:hover { color: white; }

.section-heading {
    font-size: 20px;
    font-weight: 700;
    color: #8DC9F7;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    background: #8DC9F7;
    color: #002D61;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
    border: none;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
}

.badge:hover { background: white; }

.result-meta { color: rgba(255,255,255,0.6); font-size: 13px; margin-bottom: 14px; }

.table-wrapper {
    overflow-x: auto;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(141,201,247,0.07);
    min-width: 700px;
}

thead { background: #8DC9F7; color: #002D61; }

th, td { padding: 11px 13px; text-align: left; font-size: 14px; border-bottom: 1px solid rgba(141,201,247,0.12); }

th { font-weight: 700; }

tbody tr:hover { background: rgba(141,201,247,0.1); }

.material-name { font-weight: 700; color: #8DC9F7; }

.edit-btn {
    background: #8DC9F7;
    color: #002D61;
    padding: 5px 13px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
    display: inline-block;
}

.edit-btn:hover { background: white; }

.empty-state { text-align: center; padding: 40px; color: rgba(255,255,255,0.45); }

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

@media (max-width: 768px) {
    .page-wrapper { padding: 20px 14px 60px; }
    .page-heading { font-size: 22px; }
    .search-input { padding: 11px 100px 11px 14px; font-size: 14px; }
    .search-btn { width: 90px; font-size: 13px; }
    .filter-grid { grid-template-columns: 1fr; gap: 16px; }
    .check-columns { grid-template-columns: 1fr 1fr; }
    .controls-bar { gap: 10px; }
    .divider { display: none; }
    th, td { padding: 9px 10px; font-size: 13px; }
}
</style>
</head>

<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper">

    <h1 class="page-heading">Materials Catalog</h1>
    <p class="page-subheading">Browse all available materials. Search by Title, Author, Description, or ISBN.</p>

    <div class="search-card">
        <form method="GET" action="viewMaterials.php">
            <?php foreach ($selectedLocations as $l): ?>
                <input type="hidden" name="location[]" value="<?php echo htmlspecialchars($l); ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedTypes as $t): ?>
                <input type="hidden" name="resource_type[]" value="<?php echo htmlspecialchars($t); ?>">
            <?php endforeach; ?>
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            <div class="search-inner">
                <input type="text" name="query" class="search-input"
                       placeholder="Search materials..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" class="search-btn">Search</button>
            </div>
        </form>
    </div>

    <form method="GET" action="viewMaterials.php" id="filterSortForm">
        <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">

        <div style="margin-bottom:10px;">
            <button type="button" class="filter-toggle <?php echo $hasFilters ? 'active' : ''; ?>" id="filterToggle">
                🔍 Filters
                <span class="filter-count <?php echo $hasFilters ? 'visible' : ''; ?>"><?php echo $filterCount; ?></span>
                <span class="filter-arrow">▼</span>
            </button>
        </div>

        <div class="controls-bar">
            <span class="sort-label">Sort by:</span>
            <?php
            $sortOpts = [
                'material_id'   => 'Default',
                'name'          => 'Name',
                'author'        => 'Author',
                'resource_type' => 'Type',
                'location'      => 'Location',
            ];
            foreach ($sortOpts as $val => $label):
            ?>
            <label class="sort-option">
                <input type="radio" name="sort" value="<?php echo $val; ?>"
                       onchange="this.form.submit()"
                       <?php if ($sort === $val) echo 'checked'; ?>>
                <?php echo $label; ?>
            </label>
            <?php endforeach; ?>
        </div>

        <div class="filter-panel <?php echo $hasFilters ? 'open' : ''; ?>" id="filterPanel">
            <div class="filter-grid">
                <div>
                    <div class="filter-group-title">Location</div>
                    <div class="check-columns">
                        <?php foreach ($locations as $val => $label):
                            $checked = in_array($val, $selectedLocations) ? 'checked' : '';
                        ?>
                        <div class="check-row">
                            <input type="checkbox" name="location[]"
                                   value="<?php echo $val; ?>"
                                   id="loc-<?php echo $val; ?>" <?php echo $checked; ?>>
                            <label for="loc-<?php echo $val; ?>"><?php echo $label; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <div class="filter-group-title">Resource Type</div>
                    <div class="check-columns single-col">
                        <?php foreach ($types as $val => $label):
                            $checked = in_array($val, $selectedTypes) ? 'checked' : '';
                        ?>
                        <div class="check-row">
                            <input type="checkbox" name="resource_type[]"
                                   value="<?php echo $val; ?>"
                                   id="type-<?php echo $val; ?>" <?php echo $checked; ?>>
                            <label for="type-<?php echo $val; ?>"><?php echo $label; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="filter-actions">
                <button type="submit" class="apply-btn">✓ Apply Filters</button>
                <?php if ($hasFilters): ?>
                    <a href="viewMaterials.php?query=<?php echo urlencode($searchQuery); ?>&sort=<?php echo urlencode($sort); ?>" class="clear-link">Clear filters</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <form id="bulkDeleteForm" action="deleteMaterials.php" method="POST">
    <div class="section-heading">
        📚 Materials
        <span class="badge"><?php echo $totalItems; ?></span>
        <a href="addMaterial.php" class="badge">+ Add Material</a>
        <button type="submit" name="bulk_delete" class="badge"
                onclick="return confirm('Delete selected materials?');">
            Delete Selected Material(s)
        </button>
    </div>

    <p class="result-meta">
        Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $totalItems); ?>
        of <?php echo $totalItems; ?> materials
        &nbsp;·&nbsp; Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
    </p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>ISBN</th>
                    <th>Available</th>
                    <th>Times Checked Out</th>
                    <th>Last Checkout</th>
                    <th>Last Return</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($pageMaterials)): ?>
                <tr><td colspan="11">
                    <div class="empty-state">No materials found<?php echo $searchQuery ? ' matching your search' : ''; ?>.</div>
                </td></tr>
            <?php else: ?>
                <?php foreach ($pageMaterials as $mat): ?>
                    <?php $stat = fetch_stats_by_id($mat->getMaterialID()); ?>
                <tr>
                    <td><input type="checkbox" class="rowCheckbox" name="selected_materials[]" value="<?= $mat->getMaterialID() ?>"></td>
                    <td class="material-name"><?php echo htmlspecialchars($mat->getName()); ?></td>
                    <td><?php echo $mat->getAuthor() ? htmlspecialchars($mat->getAuthor()) : 'N/A'; ?></td>
                    <td><?php echo htmlspecialchars($mat->getResourceType()); ?></td>
                    <td><?php echo htmlspecialchars($mat->getLocation()); ?></td>
                    <td><?php echo $mat->getISBN() ? htmlspecialchars($mat->getISBN()) : 'N/A'; ?></td>
                    <td><?php echo $mat->getCopyInstock(); ?> / <?php echo $mat->getCopyCapacity(); ?></td>
                    <td><?php echo $stat->getTimesCheckedOut(); ?></td>
                    <td><?php echo $stat->getLastCheckout() ? htmlspecialchars($stat->getLastCheckout()) : 'N/A'; ?></td>
                    <td><?php echo $stat->getLastReturn() ? htmlspecialchars($stat->getLastReturn()) : 'N/A'; ?></td>
                    <td><a href="editMaterial.php?material_id=<?php echo $mat->getMaterialID(); ?>" class="edit-btn">Edit</a></td>
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
        <a href="<?php echo buildUrl($currentPage - 1, $searchQuery, $sort, $selectedLocations, $selectedTypes); ?>"
           class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">&#8592; Prev</a>

        <?php
        if ($currentPage > $win + 2) {
            echo '<a href="' . buildUrl(1, $searchQuery, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn">1</a>';
            if ($currentPage > $win + 3) echo '<span class="ellipsis">…</span>';
        }
        for ($p = max(1, $currentPage - $win); $p <= min($totalPages, $currentPage + $win); $p++) {
            $cls = $p === $currentPage ? 'active' : '';
            echo '<a href="' . buildUrl($p, $searchQuery, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn ' . $cls . '">' . $p . '</a>';
        }
        if ($currentPage < $totalPages - $win - 1) {
            if ($currentPage < $totalPages - $win - 2) echo '<span class="ellipsis">…</span>';
            echo '<a href="' . buildUrl($totalPages, $searchQuery, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn">' . $totalPages . '</a>';
        }
        ?>

        <a href="<?php echo buildUrl($currentPage + 1, $searchQuery, $sort, $selectedLocations, $selectedTypes); ?>"
           class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">Next &#8594;</a>

        <form method="GET" action="viewMaterials.php" style="display:flex; align-items:center; gap:6px; margin-left:8px;">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="sort"  value="<?php echo htmlspecialchars($sort); ?>">
            <?php foreach ($selectedLocations as $l): ?>
                <input type="hidden" name="location[]" value="<?php echo htmlspecialchars($l); ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedTypes as $t): ?>
                <input type="hidden" name="resource_type[]" value="<?php echo htmlspecialchars($t); ?>">
            <?php endforeach; ?>
            <input type="number" name="page" min="1" max="<?php echo $totalPages; ?>"
                   class="jump-input" placeholder="Go to…">
            <button type="submit" class="page-btn" style="padding:0 14px;">Go</button>
        </form>
    </div>
    <?php endif; ?>

</div>

<?php require 'footer.php'; ?>

<script>
const filterToggle = document.getElementById('filterToggle');
const filterPanel  = document.getElementById('filterPanel');

if (filterPanel.classList.contains('open')) {
    filterToggle.classList.add('open');
}

filterToggle.addEventListener('click', function () {
    const isOpen = filterPanel.classList.toggle('open');
    filterToggle.classList.toggle('open', isOpen);
});
</script>

</body>
</html>
