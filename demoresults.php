<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/database/dbinfo.php');

// ------------------------
// Search materials
// ------------------------
function searchMaterials(string $search = ''): array {
    $conn = connect();
    $results = [];

    $search = trim($search);

    if ($search === '') {
        return $results;
    }

    $sql = "
        SELECT material_id, name, location, resource_type, isbn, author, description, copy_capacity, copy_instock
        FROM dbmaterials
        WHERE name LIKE ?
           OR author LIKE ?
           OR resource_type LIKE ?
           OR isbn LIKE ?
        ORDER BY name ASC
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL prepare failed: " . $conn->error);
    }

    $like = "%" . $search . "%";
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();

    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
    return $results;
}

// ------------------------
// Handle search request
// ------------------------
$search = trim($_GET['query'] ?? '');
$results = [];

if ($search !== '') {
    $results = searchMaterials($search);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Search Demo</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 40px; background-color: #f8f8f8; color: #111;">

    <h1>Library Search Demo</h1>
    <p>This page is a basic test to confirm item data can be retrieved from <code>dbmaterials</code> and displayed on the frontend.</p>

    <form method="GET" action="" style="margin-bottom: 30px;">
        <input 
            type="text" 
            name="query" 
            placeholder="Search by name, author, type, or ISBN..."
            value="<?php echo htmlspecialchars($search); ?>"
            style="width: 100%; max-width: 700px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 10px; outline: none; color: black;"
            required
        >
        <br><br>
        <button 
            type="submit"
            style="padding: 10px 18px; font-size: 16px; cursor: pointer; border: none; border-radius: 8px; background-color: #9C2007; color: white;"
        >
            Search
        </button>
    </form>

    <?php if ($search !== ''): ?>
        <h2>Results for "<?php echo htmlspecialchars($search); ?>"</h2>

        <?php if (!empty($results)): ?>
            <div style="display: flex; flex-direction: column; gap: 15px; max-width: 900px;">
                <?php foreach ($results as $item): ?>
                    <div style="border: 1px solid #ddd; background: white; padding: 15px; border-radius: 10px;">
                        <h3 style="margin: 0 0 8px 0;">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </h3>

                        <p style="margin: 4px 0;"><strong>Material ID:</strong> <?php echo htmlspecialchars($item['material_id']); ?></p>
                        <p style="margin: 4px 0;"><strong>Author:</strong> <?php echo htmlspecialchars($item['author'] ?? 'N/A'); ?></p>
                        <p style="margin: 4px 0;"><strong>Resource Type:</strong> <?php echo htmlspecialchars($item['resource_type']); ?></p>
                        <p style="margin: 4px 0;"><strong>ISBN:</strong> <?php echo htmlspecialchars($item['isbn'] ?? 'N/A'); ?></p>
                        <p style="margin: 4px 0;"><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                        <p style="margin: 4px 0;"><strong>Description:</strong> <?php echo htmlspecialchars($item['description'] ?? 'N/A'); ?></p>
                        <p style="margin: 4px 0;"><strong>Copies In Stock:</strong> <?php echo htmlspecialchars($item['copy_instock']); ?></p>
                        <p style="margin: 4px 0;"><strong>Total Copy Capacity:</strong> <?php echo htmlspecialchars($item['copy_capacity']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No matching items found.</p>
        <?php endif; ?>
    <?php endif; ?>

</body>
</html>