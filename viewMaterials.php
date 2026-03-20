<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

// Allow guests (0), workers (1), admins (2+)
if (!isset($_SESSION['access_level'])) {
    $_SESSION['access_level'] = 0; // treat as guest
}

$accessLevel = (int) $_SESSION['access_level'];
$isGuest     = ($accessLevel === 0);
$isWorker    = ($accessLevel === 1);
$isAdmin     = ($accessLevel >= 2);

// Includes
include_once('database/dbMaterials.php');
include_once('domain/Materials.php');

// Search
$searchQuery = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';
$filteredMaterials = fetch_materials_by_query($searchQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<title>Seacobeck Curriculum Lab | Materials Catalog</title>

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

    <h1 class="page-heading">Materials Catalog</h1>
    <p class="page-subheading">
        Browse all available materials. Search by Title, Author, Description, or ISBN.
    </p>

    <!-- Search -->
    <div class="search-wrapper">
        <div class="search-box">
            <form method="GET">
                <div class="search-inner">
                    <input
                        type="text"
                        name="query"
                        class="search-input"
                        placeholder="Search materials..."
                        value="<?php echo htmlspecialchars($searchQuery); ?>"
                    >
                    <button class="search-btn">Search</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Materials Table -->
    <h2 class="section-heading">
        📚 Materials
        <span class="badge"><?php echo count($filteredMaterials); ?></span>
    </h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>ISBN</th>
                    <th>Available</th>
                    <th>Description</th>
                    <th>Edit</th>
                </tr>
            </thead>
            <tbody>

            <?php if (empty($filteredMaterials)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            No materials found<?php echo $searchQuery ? ' matching your search' : ''; ?>.
                        </div>
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($filteredMaterials as $mat): ?>
                <tr>
                    <td class="material-name">
                        <?php echo htmlspecialchars($mat->getName()); ?>
                    </td>
                    <td><?php if ($mat->getAuthor()){echo htmlspecialchars($mat->getAuthor()); } else { echo "N/A"; }?></td>
                    <td><?php echo htmlspecialchars($mat->getResourceType()); ?></td>
                    <td><?php echo htmlspecialchars($mat->getLocation()); ?></td>
                    <td><?php if ($mat->getISBN()){echo htmlspecialchars($mat->getISBN()); } else { echo "N/A"; }?></td>
                    <td> <?php echo $mat->getCopyInstock(); ?> / <?php echo $mat->getCopyCapacity(); ?></td>
                    <td><?php if ($mat->getDescription()){echo htmlspecialchars($mat->getDescription()); } else { echo "N/A"; }?></td>
                    <td><a href="editMaterial.php?material_id=<?php echo htmlspecialchars($mat->getMaterialID()); ?>" class="edit-button">Edit</a></td>
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