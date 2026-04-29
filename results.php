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
include_once('database/dbMaterials.php');
include_once('domain/Person.php');

$accessLevel = (int) $_SESSION['access_level'];
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

$query             = "";
$sort              = $_GET['sort'] ?? 'material_id';
$selectedLocations = $_GET['location'] ?? [];
$selectedTypes     = $_GET['resource_type'] ?? [];

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}

if (isset($_GET['query'])) {
    $query = strtolower(trim($_GET['query']));
}

$allResults = fetch_materials_by_query($query, $sort);

$locations = [];
$types = [];
foreach ($allResults as $m) {
    $loc  = $m->getLocation();
    $type = $m->getResourceType();
    if ($loc  && !isset($locations[$loc]))  $locations[$loc]  = $loc;
    if ($type && !isset($types[$type]))     $types[$type]     = $type;
}
ksort($locations);
ksort($types);

if (!empty($selectedLocations) || !empty($selectedTypes)) {
    $allResults = array_filter($allResults, function($material) use ($selectedLocations, $selectedTypes) {
        $matchLocation = empty($selectedLocations) || in_array($material->getLocation(), $selectedLocations);
        $matchType     = empty($selectedTypes)     || in_array($material->getResourceType(), $selectedTypes);
        return $matchLocation && $matchType;
    });
}

$allResults  = array_values($allResults);
$perPage     = 10;
$totalItems  = count($allResults);
$totalPages  = max(1, ceil($totalItems / $perPage));
$currentPage = max(1, min((int)($_GET['page'] ?? 1), $totalPages));
$offset      = ($currentPage - 1) * $perPage;
$results     = array_slice($allResults, $offset, $perPage);

function buildUrl($page, $query, $sort, $locations, $types) {
    $parts = [
        'page='  . $page,
        'query=' . urlencode($query),
        'sort='  . urlencode($sort),
    ];
    foreach ($locations as $l) $parts[] = 'location[]=' . urlencode($l);
    foreach ($types     as $t) $parts[] = 'resource_type[]=' . urlencode($t);
    return 'results.php?' . implode('&', $parts);
}

$hasFilters  = !empty($selectedLocations) || !empty($selectedTypes);
$filterCount = count($selectedLocations) + count($selectedTypes);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
<link href="./css/base.css" rel="stylesheet">
<title>Seacobeck Curriculum Lab | Search Results</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

body {
    padding-top: 95px;
    min-height: 100vh;
    background-image: url('images/library.jpg');
    background-size: cover;
    background-position: center;
    position: relative;
    color: white;
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgb(0, 45, 97, 0.88);
    z-index: -1;
}

.page-wrapper {
    width: 100% !important;
    max-width: 1280px !important;
    margin: 0 auto !important;
    padding: 40px 28px 80px !important;
    box-sizing: border-box !important;
}

.search-card {
    border: 3px solid #0067A2;
    border-radius: 16px;
    padding: 16px 18px;
    background-color: #8DC9F7;
    margin-bottom: 16px;
}

.search-inner {
    position: relative !important;
    width: 100% !important;
    display: block !important;
}

.search-input {
    display: block !important;
    width: 100% !important;
    padding: 11px 120px 11px 16px !important;
    font-size: 15px !important;
    border-radius: 20px !important;
    outline: none !important;
    color: #0067A2 !important;
    font-weight: 600 !important;
    font-family: 'Inter', sans-serif !important;
    border: 1px solid #ccc !important;
    box-shadow: none !important;
    margin: 0 !important;
    height: auto !important;
    background: white !important;
}

.search-btn {
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
    height: 100% !important;
    width: 110px !important;
    border-radius: 0 20px 20px 0 !important;
    background: #0067A2 !important;
    color: white !important;
    font-weight: 700 !important;
    font-size: 14px !important;
    cursor: pointer !important;
    font-family: 'Inter', sans-serif !important;
    border: none !important;
    transition: background 0.2s;
    margin: 0 !important;
    padding: 0 !important;
    display: block !important;
}

.search-btn:hover { background: #005080 !important; }

.controls-bar {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    width: 100%;
}

.sort-label { font-weight: 700; font-size: 14px; white-space: nowrap; flex-shrink: 0; }

.sort-option {
    font-size: 13px;
    white-space: nowrap;
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 4px;
    cursor: pointer;
    color: white !important;
    font-weight: 400;
    width: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    background: none !important;
    border: none !important;
    flex-shrink: 0;
}

.sort-option input[type="radio"] {
    width: auto !important;
    margin: 0 !important;
    padding: 0 !important;
    display: inline-block !important;
    flex-shrink: 0;
}

.divider {
    width: 1px;
    height: 18px;
    background: rgba(255,255,255,0.25);
    flex-shrink: 0;
}

.filter-toggle {
    display: inline-flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 7px;
    background: transparent !important;
    border: 2px solid #8DC9F7 !important;
    border-radius: 20px !important;
    color: white !important;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 700;
    padding: 5px 14px !important;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
    white-space: nowrap;
    width: auto !important;
    flex-shrink: 0;
    margin: 0 !important;
}

.filter-toggle:hover,
.filter-toggle.active { background: #8DC9F7 !important; color: #002D61 !important; }

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

.results-heading {
    font-size: 24px;
    font-weight: 700;
    color: white;
    margin-bottom: 4px;
}

.result-meta {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    margin-bottom: 20px;
}

.result-card {
    background: white;
    color: #0067A2;
    padding: 20px;
    margin-bottom: 14px;
    border-radius: 12px;
}

.result-card h3 a:link,
.result-card h3 a:visited,
.result-card h3 a:active { color: #002D61; text-decoration: none; }

.result-card h3 a:hover { color: #0067A2; }

.result-card h3 { font-size: 18px; margin-bottom: 8px; }

.result-card p { font-size: 14px; margin-bottom: 4px; line-height: 1.5; }

.available-yes { color: green; font-weight: 700; }
.available-no  { color: red;   font-weight: 700; }

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

.no-results { color: white; font-size: 16px; margin-top: 20px; }

@media (max-width: 768px) {
    .page-wrapper { padding: 20px 14px 60px; }
    .search-input { padding: 11px 100px 11px 14px; font-size: 14px; }
    .search-btn { width: 90px; font-size: 13px; }
    .filter-grid { grid-template-columns: 1fr; gap: 16px; }
    .check-columns { grid-template-columns: 1fr 1fr; }
    .controls-bar { gap: 10px; }
    .divider { display: none; }
}
</style>
</head>

<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper" style="width:100%; max-width:1280px; margin:0 auto; padding:40px 28px 80px; box-sizing:border-box;">

    <div class="search-card">
        <form action="results.php" method="GET">
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
                       value="<?php echo htmlspecialchars($query); ?>">
                <button type="submit" class="search-btn">Search</button>
            </div>
        </form>
    </div>

    <form method="GET" action="results.php" id="filterSortForm" style="display:block;">
        <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">

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
                'resource_type' => 'Resource Type',
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
                    <a href="results.php?query=<?php echo urlencode($query); ?>&sort=<?php echo urlencode($sort); ?>" class="clear-link">Clear filters</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <h2 class="results-heading">Search Results <span style="font-weight:400;">(<?php echo $totalItems; ?>)</span></h2>
    <p class="result-meta">
        Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $totalItems); ?> of <?php echo $totalItems; ?> materials
    </p>

    <?php if (!empty($results)): ?>
        <?php foreach ($results as $material): ?>
        <div class="result-card">
            <h3><a href="self_service.php?material_id=<?php echo $material->getMaterialID(); ?>"><?php echo htmlspecialchars($material->getName()); ?></a></h3>
            <p><b>Author:</b> <?php echo htmlspecialchars($material->getAuthor() ?: 'N/A'); ?></p>
            <p><b>ISBN:</b> <?php echo htmlspecialchars($material->getISBN() ?: 'N/A'); ?></p>
            <p><b>Location:</b> <?php echo htmlspecialchars($material->getLocation()); ?></p>
            <p><b>Resource Type:</b> <?php echo htmlspecialchars($material->getResourceType()); ?></p>
            <?php if ($material->getDescription()): ?>
                <p><?php echo htmlspecialchars($material->getDescription()); ?></p>
            <?php endif; ?>
            <p><b>Available:</b> <?php echo $material->getCopyInstock(); ?> / <?php echo $material->getCopyCapacity(); ?></p>
            <?php if ($material->canBeCheckedOut()): ?>
                <p class="available-yes">Available for Checkout</p>
            <?php else: ?>
                <p class="available-no">Out of Stock</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php if ($totalPages > 1):
            $win = 2;
        ?>
        <div class="pagination">
            <a href="<?php echo buildUrl($currentPage - 1, $query, $sort, $selectedLocations, $selectedTypes); ?>"
               class="page-btn <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">&#8592; Prev</a>

            <?php
            if ($currentPage > $win + 2) {
                echo '<a href="' . buildUrl(1, $query, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn">1</a>';
                if ($currentPage > $win + 3) echo '<span class="ellipsis">…</span>';
            }
            for ($p = max(1, $currentPage - $win); $p <= min($totalPages, $currentPage + $win); $p++) {
                $cls = $p === $currentPage ? 'active' : '';
                echo '<a href="' . buildUrl($p, $query, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn ' . $cls . '">' . $p . '</a>';
            }
            if ($currentPage < $totalPages - $win - 1) {
                if ($currentPage < $totalPages - $win - 2) echo '<span class="ellipsis">…</span>';
                echo '<a href="' . buildUrl($totalPages, $query, $sort, $selectedLocations, $selectedTypes) . '" class="page-btn">' . $totalPages . '</a>';
            }
            ?>

            <a href="<?php echo buildUrl($currentPage + 1, $query, $sort, $selectedLocations, $selectedTypes); ?>"
               class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">Next &#8594;</a>

            <form method="GET" action="results.php" style="display:flex; align-items:center; gap:6px; margin-left:8px;">
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($query); ?>">
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

    <?php else: ?>
        <p class="no-results">No materials found.</p>
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
