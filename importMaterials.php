<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Admin-only access
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: index.php');
    die();
}

include_once('database/dbinfo.php');

$message = "";
$details = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];

    // Validate file type
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        $message = "Only CSV files are allowed.";
    } else {

        try {
            // Open CSV
            $handle = fopen($fileTmpPath, "r");
            if (!$handle) {
                throw new Exception("Could not open CSV file.");
            }

            $con = connect();
            $inserted = 0;
            $skipped  = 0;

            // Read header row
            $header = fgetcsv($handle);

            // Expected CSV format:
            // name, location, resource_type, isbn, author, description, capacity, instock

            while (($row = fgetcsv($handle)) !== false) {

                // Normalize row
                $row = array_map("trim", $row);

                $name        = mysqli_real_escape_string($con, $row[0] ?? '');
                $location    = mysqli_real_escape_string($con, $row[1] ?? '');
                $type        = mysqli_real_escape_string($con, $row[2] ?? '');
                $isbn        = mysqli_real_escape_string($con, $row[3] ?? '');
                $author      = mysqli_real_escape_string($con, $row[4] ?? '');
                $description = mysqli_real_escape_string($con, $row[5] ?? '');
                $capacity    = (int)($row[6] ?? 0);
                $instock     = (int)($row[7] ?? 0);

                // Basic validation
                if (!$name || $capacity < 0 || $instock < 0) {
                    $skipped++;
                    continue;
                }

                // Prevent duplicates (by name)
                $check = mysqli_query($con, "SELECT material_id FROM dbmaterials WHERE name='$name'");
                if (mysqli_num_rows($check) > 0) {
                    $skipped++;
                    continue;
                }

                // Insert row
                $query = "
                    INSERT INTO dbmaterials
                        (name, location, resource_type, isbn, author, description, copy_capacity, copy_instock)
                    VALUES 
                        ('$name', '$location', '$type', '$isbn', '$author', '$description', $capacity, $instock)
                ";

                if (mysqli_query($con, $query)) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }

            fclose($handle);
            mysqli_close($con);

            $message = "CSV import complete!";
            $details = [
                "Inserted: $inserted",
                "Skipped: $skipped"
            ];

        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Import Materials</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">


<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

body {
    background-color: #002D61;
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

.page-wrapper {
    max-width: 900px;
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

.upload-box {
    background: #8DC9F7;
    border-radius: 16px;
    padding: 30px;
    text-align: center;
}

input[type="file"] {
    margin-bottom: 15px;
}

button {
    background: #0067A2;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
}

button:hover {
    background: #004f80;
}

.message {
    margin-top: 20px;
    font-weight: 600;
}

.details {
    margin-top: 10px;
    font-size: 14px;
}

.divider {
    width: 90%;
    height: 1px;
    background: rgba(141,201,247,0.25);
    margin: 40px auto;
}

.footer {
    width: 100%;
    background: #8DC9F7;
    display: flex;
    justify-content: space-between;
    padding: 30px 50px;
    flex-wrap: wrap;
}

.footer-section {
    color: white;
}
</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="overlay"></div>

<div class="page-wrapper">

    <h1 class="page-heading">Import Materials</h1>
    <p class="page-subheading">
        Upload a CSV file to add materials to the catalog.
    </p>

    <!-- Download Template Button -->
    <div style="text-align: left; margin-top: 10px; margin-bottom: 25px;">
        <form method="GET" action="download_template.php" style="display:inline-block;">
            <button type="submit" class="edit-submit">
                Download CSV Template
            </button>
        </form>
    </div>
    
    <div class="upload-box">
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" accept=".csv" required>
            <br><br>
            <button type="submit" class="edit-submit">Upload & Import</button>
        </form>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($details)): ?>
            <div class="details">
                <?php foreach ($details as $d) echo "<div>$d</div>"; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<div class="divider"></div>

<div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <?php require 'footer.php'; ?>

    <script src="https://kit.fontawesome.com/yourkit.js" crossorigin="anonymous"></script>

</body>
</html>
