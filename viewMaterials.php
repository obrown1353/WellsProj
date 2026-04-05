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

// Search + sort
$searchQuery       = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';
$sort              = $_GET['sort'] ?? 'material_id';
$selectedLocations = $_GET['location'] ?? [];
$selectedTypes     = $_GET['resource_type'] ?? [];

$allMaterials = fetch_materials_by_query($searchQuery, $sort);

// Apply filters
if (!empty($selectedLocations) || !empty($selectedTypes)) {
    $allMaterials = array_filter($allMaterials, function($m) use ($selectedLocations, $selectedTypes) {
        $matchLoc  = empty($selectedLocations) || in_array($m->getLocation(), $selectedLocations);
        $matchType = empty($selectedTypes)     || in_array($m->getResourceType(), $selectedTypes);
        return $matchLoc && $matchType;
    });
}

$allMaterials = array_values($allMaterials);

// Pagination
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


?>

<!DOCTYPE html>
    <script>
        /* ── Function for Deletion Confirmation ──────────────────────────────── */
        function confirmAndSubmit(formId, msg) {
            if (confirm(msg)) {
                document.getElementById(formId).submit();
            }
        }
    </script>
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
    /* background-color: #002D61; */
    min-height: 100vh;
    padding-top: 95px;
    color: white;
/*    display: flex; */
/*    flex-direction: column; */
    justify-content: space-between;
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

/* ── LAYOUT ─────────────────────────────────────── */
.outer {
    display: flex;
    gap: 36px;
    max-width: 1280px;
    margin: 0 auto;
    padding: 40px 32px 80px;
    align-items: flex-start;
}

/* ── FILTER SIDEBAR ──────────────────────────────── */
.filter-sidebar {
    flex: 0 0 260px;
    width: 260px;
    border: 2px solid #8DC9F7;
    border-radius: 14px;
    padding: 22px 18px;
    background-color: #0067A2;
    position: sticky;
    top: 115px;
    max-height: 88vh;
    overflow-y: auto;
}

.filter-sidebar h3 {
    font-size: 17px;
    font-weight: 700;
    margin-bottom: 10px;
    color: white;
}

.filter-sidebar hr {
    border-color: rgba(255,255,255,0.3);
    margin-bottom: 14px;
}

.filter-sidebar details summary {
    font-weight: 700;
    cursor: pointer;
    margin-bottom: 8px;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: white;
}

.filter-sidebar details summary::before {
    content: '▶';
    font-size: 10px;
    transition: transform 0.2s;
}

.filter-sidebar details[open] summary::before { transform: rotate(90deg); }

.filter-sidebar .check-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 6px;
    padding-left: 4px;
}

.filter-sidebar label {
    font-size: 13px !important;
    cursor: pointer;
    margin-left: 4px;
    color: white !important;
    font-weight: 400 !important;
    width: auto !important;
}

.filter-sidebar input[type="checkbox"] {
    accent-color: #8DC9F7;
    cursor: pointer;
    width: auto !important;
    margin-bottom: 0 !important;
    box-shadow: none !important;
    border: none !important;
}

.apply-btn {
    display: block;
    width: 100% !important;
    margin-top: 18px;
    padding: 12px 0 !important;
    border-radius: 10px !important;
    border: none !important;
    background: #8DC9F7 !important;
    color: #002D61 !important;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    text-align: center !important;
}

.apply-btn:hover {
    background: #ffffff !important;
    transform: translateY(-1px);
}

.clear-link {
    display: block;
    text-align: center;
    margin-top: 10px;
    font-size: 12px;
    color: rgba(255,255,255,0.6);
    text-decoration: underline;
    cursor: pointer;
}

.clear-link:hover { color: white; }

/* ── MAIN ────────────────────────────────────────── */
.main { flex: 1; min-width: 0; }

.page-heading {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 4px;
}
.page-subheading {
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    margin-bottom: 28px;
}

/* Search */
.search-card {
    border: 3px solid #0067A2;
    border-radius: 16px;
    padding: 18px 22px;
    background-color: #8DC9F7;
    margin-bottom: 22px;
}

.search-inner { position: relative; width: 100%; }

.search-input {
    width: 100%;
    padding: 11px 130px 11px 16px;
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
    width: 120px;
    border-radius: 0 20px 20px 0;
    background: #0067A2;
    color: white;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    border: none;
}

/* Sort row */
.sort-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.sort-row .sort-label { font-weight: 700; white-space: nowrap; font-size: 14px; }

.sort-row label {
    font-size: 13px !important;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    color: white !important;
    font-weight: 400 !important;
    width: auto !important;
}

/* Section heading with badge */
.section-heading {
    font-size: 20px;
    font-weight: 700;
    color: #8DC9F7;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.badge {
    background: #8DC9F7;
    color: #002D61;
    padding: 3px 11px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s;
}

.badge:hover { background: white; }

.result-meta {
    color: rgba(255,255,255,0.6);
    font-size: 13px;
    margin-bottom: 14px;
}

/* Table */
.table-wrapper {
    overflow-x: auto;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(141,201,247,0.07);
}

thead { background: #8DC9F7; color: #002D61; }

th, td {
    padding: 12px 14px;
    text-align: left;
    font-size: 14px;
    border-bottom: 1px solid rgba(141,201,247,0.12);
}

th { font-weight: 700; }

tbody tr:hover { background: rgba(141,201,247,0.1); }

.material-name { font-weight: 700; color: #8DC9F7; }

.edit-btn {
    background: #8DC9F7;
    color: #002D61;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background 0.2s;
    border: none !important;
    width: auto !important;
}

.edit-btn:hover { background: white; }

.empty-state {
    text-align: center;
    padding: 40px;
    color: rgba(255,255,255,0.45);
}

/* ── PAGINATION ───────────────────────────────────── */
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
    border-radius: 8px !important;
    border: 2px solid #8DC9F7 !important;
    background: transparent !important;
    color: white !important;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.18s, color 0.18s;
    width: auto !important;
}

.page-btn:hover { background: #8DC9F7 !important; color: #002D61 !important; }

.page-btn.active {
    background: #8DC9F7 !important;
    color: #002D61 !important;
    pointer-events: none;
}

.page-btn.disabled {
    opacity: 0.3;
    pointer-events: none;
}

.ellipsis {
    color: white;
    align-self: center;
    font-weight: 700;
    font-size: 16px;
    padding: 0 2px;
}

.jump-input {
    width: 76px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 2px solid #8DC9F7 !important;
    background: transparent !important;
    color: white !important;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    font-size: 13px;
    text-align: center;
    box-shadow: none !important;
    margin-bottom: 0 !important;
}

.jump-input::placeholder { color: rgba(255,255,255,0.45); }
</style>
</head>

<body>
<?php require 'header.php'; ?>

<div class="overlay"></div>
<div class="outer">

    <!-- ── FILTER SIDEBAR ── -->
    <div class="filter-sidebar">
        <h3>🔍 Filters</h3>
        <hr>

        <form method="GET" action="viewMaterials.php">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="sort"  value="<?php echo htmlspecialchars($sort); ?>">

            <!-- Location -->
            <details <?php if (!empty($selectedLocations)) echo 'open'; ?>>
                <summary>Location</summary>
                <div class="check-group">
                <?php
                $locations = [
                    "Early Readers1" => "Early Readers 1",
                    "Early Readers2" => "Early Readers 2",
                    "General Fiction A-M" => "General Fiction A-M",
                    "General Fiction N-Z" => "General Fiction N-Z",
                    "General Nonfiction" => "General Nonfiction",
                    "Holiday" => "Holiday",
                    "Middle Grade Novels" => "Middle Grade Novels",
                    "Multilingual" => "Multilingual",
                    "Realistic Fiction A-G" => "Realistic Fiction A-G",
                    "Realistic Fiction H-Z" => "Realistic Fiction H-Z",
                    "Science A-F" => "Science A-F",
                    "Science A-M" => "Science A-M",
                    "Science G-Q" => "Science G-Q",
                    "Science N-Z" => "Science N-Z",
                    "Science R-Z" => "Science R-Z",
                    "Science Resources" => "Science Resources",
                    "Social Studies" => "Social Studies",
                    "Social Studies Stories A-F" => "Social Studies Stories A-F",
                    "Social Studies Stories A-L" => "Social Studies Stories A-L",
                    "Social Studies Stories G-O" => "Social Studies Stories G-O",
                    "Social Studies Stories P-Z" => "Social Studies Stories P-Z",
                    "Trad Folk" => "Trad/Folk",
                    "Transportation" => "Transportation",
                    "Wordless Picture Books" => "Wordless Picture Books",
                ];
                foreach ($locations as $val => $label):
                    $checked = in_array($val, $selectedLocations) ? 'checked' : '';
                ?>
                <div>
                    <input type="checkbox" name="location[]" value="<?php echo $val; ?>"
                           id="vloc-<?php echo $val; ?>" <?php echo $checked; ?>>
                    <label for="vloc-<?php echo $val; ?>"><?php echo $label; ?></label>
                </div>
                <?php endforeach; ?>
                </div>
            </details>

            <br>

            <!-- Resource Type -->
            <details <?php if (!empty($selectedTypes)) echo 'open'; ?>>
                <summary>Resource Type</summary>
                <div class="check-group">
                <?php
                $types = [
                    "Children's Literature" => "Children's Literature",
                    "Math Manipulatives"    => "Math Manipulatives",
                    "Professional Text"     => "Professional Text",
                    "Textbook"              => "Textbook",
                    "Supplies"              => "Supplies",
                ];
                foreach ($types as $val => $label):
                    $checked = in_array($val, $selectedTypes) ? 'checked' : '';
                ?>
                <div>
                    <input type="checkbox" name="resource_type[]" value="<?php echo $val; ?>"
                           id="vtype-<?php echo $val; ?>" <?php echo $checked; ?>>
                    <label for="vtype-<?php echo $val; ?>"><?php echo $label; ?></label>
                </div>
                <?php endforeach; ?>
                </div>
            </details>

            <button type="submit" class="apply-btn">✓ Apply Filters</button>
            <?php if (!empty($selectedLocations) || !empty($selectedTypes)): ?>
                <a href="viewMaterials.php?query=<?php echo urlencode($searchQuery); ?>&sort=<?php echo urlencode($sort); ?>" class="clear-link">Clear filters</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- ── MAIN ── -->
    <div class="main">

        <h1 class="page-heading">Materials Catalog</h1>
        <p class="page-subheading">Browse all available materials. Search by Title, Author, Description, or ISBN.</p>

        <!-- Search -->
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

        <!-- Sort row -->
        <form method="GET" action="viewMaterials.php" class="sort-row">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <?php foreach ($selectedLocations as $l): ?>
                <input type="hidden" name="location[]" value="<?php echo htmlspecialchars($l); ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedTypes as $t): ?>
                <input type="hidden" name="resource_type[]" value="<?php echo htmlspecialchars($t); ?>">
            <?php endforeach; ?>

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
            <label>
                <input type="radio" name="sort" value="<?php echo $val; ?>"
                       onchange="this.form.submit()"
                       <?php if ($sort === $val) echo 'checked'; ?>>
                <?php echo $label; ?>
            </label>
            <?php endforeach; ?>
        </form>

        <!-- Section heading -->
        <form id="bulkDeleteForm" action="deleteMaterials.php" method="POST">
        <div class="section-heading">
            📚 Materials
            <span class="badge"><?php echo $totalItems; ?></span>
            <a href="addMaterial.php" class="badge">+ Add Material</a>
            <button type="submit" name="bulk_delete" class="badge" onclick="return confirm('Delete selected materials?');">Delete Material(s)</button>
        </div>

        <p class="result-meta">
            Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $totalItems); ?>
            of <?php echo $totalItems; ?> materials
            &nbsp;·&nbsp; Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
        </p>

        <!-- Table -->
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
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pageMaterials)): ?>
                    <tr><td colspan="8">
                        <div class="empty-state">No materials found<?php echo $searchQuery ? ' matching your search' : ''; ?>.</div>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($pageMaterials as $mat): ?>
                    <tr>
                        <td><input type="checkbox" class="rowCheckbox" name="selected_materials[]" value="<?= $mat->getMaterialID() ?>"></td>
                        <td class="material-name"><?php echo htmlspecialchars($mat->getName()); ?></td>
                        <td><?php echo $mat->getAuthor() ? htmlspecialchars($mat->getAuthor()) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($mat->getResourceType()); ?></td>
                        <td><?php echo htmlspecialchars($mat->getLocation()); ?></td>
                        <td><?php echo $mat->getISBN() ? htmlspecialchars($mat->getISBN()) : 'N/A'; ?></td>
                        <td><?php echo $mat->getCopyInstock(); ?> / <?php echo $mat->getCopyCapacity(); ?></td>
                        <td><a href="editMaterial.php?material_id=<?php echo $mat->getMaterialID(); ?>" class="edit-btn">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        </form>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
            $win = 2;
        ?>
        <div class="pagination">
            <!-- Prev -->
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

            <!-- Next -->
            <a href="<?php echo buildUrl($currentPage + 1, $searchQuery, $sort, $selectedLocations, $selectedTypes); ?>"
               class="page-btn <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">Next &#8594;</a>

            <!-- Jump to page -->
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
</div>

<?php require 'footer.php'; ?>
</body>
</html>
