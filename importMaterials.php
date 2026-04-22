<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 1) {
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

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        $message = "Only CSV files are allowed.";
    } else {
        try {
            $handle = fopen($fileTmpPath, "r");
            if (!$handle) throw new Exception("Could not open CSV file.");

            $con = connect();
            $inserted = 0;
            $skipped  = 0;
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $row = array_map("trim", $row);
                $name = mysqli_real_escape_string($con, $row[0] ?? '');

                $new_material = new materials(
                    0, 
                    mysqli_real_escape_string($con, $row[0] ?? ''), //name
                    mysqli_real_escape_string($con, $row[1] ?? ''), //location 
                    mysqli_real_escape_string($con, $row[2] ?? ''), //type
                    mysqli_real_escape_string($con, $row[3] ?? null), //isbn
                    mysqli_real_escape_string($con, $row[4] ?? null), //author
                    mysqli_real_escape_string($con, $row[5] ?? null), //description
                    (int)($row[6] ?? 0), //capacity
                    (int)($row[7] ?? 0), //instock
                );
        
                if (!$name || $capacity < 0 || $instock < 0) { $skipped++; continue; }

                $check = mysqli_query($con, "SELECT material_id FROM dbmaterials WHERE name='$name'");
                if (mysqli_num_rows($check) > 0) { $skipped++; continue; }
                
                if (add_material($new_material)) { $inserted++; } else { $skipped++; }
            }

            fclose($handle);
            mysqli_close($con);
            $message = "CSV import complete!";
            $details = ["Inserted: $inserted", "Skipped: $skipped"];

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
<title>Seacobeck Curriculum Lab | Import Materials</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

body {
    min-height: 100vh;
    padding-top: 95px;
    color: white;
    display: flex;
    flex-direction: column;
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
    width: 100%;
    margin: 0 auto;
    padding: 40px 24px 80px;
    flex: 1;
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

.download-btn {
    display: inline-block;
    background: #0067A2;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    margin-bottom: 24px;
    transition: background 0.2s;
}
.download-btn:hover { background: #004f80; }

.upload-box {
    background: #8DC9F7;
    border-radius: 16px;
    padding: 30px;
    text-align: center;
    width: 100%;
}

.upload-box input[type="file"] {
    margin-bottom: 15px;
    color: #002D61;
    font-weight: 600;
}

.upload-btn {
    background: #0067A2;
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    transition: background 0.2s;
}
.upload-btn:hover { background: #004f80; }

.message {
    margin-top: 20px;
    font-weight: 600;
    color: #002D61;
}

.details {
    margin-top: 10px;
    font-size: 14px;
    color: #002D61;
}
</style>
</head>
<body>

<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper">
    <h1 class="page-heading">Import Materials</h1>
    <p class="page-subheading">Upload a CSV file to add materials to the catalog.</p>

    <form method="GET" action="download_template.php" style="display:inline-block;">
        <button type="submit" class="download-btn">Download CSV Template</button>
    </form>

    <div class="upload-box">
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" accept=".csv" required>
            <br><br>
            <button type="submit" class="upload-btn">Upload & Import</button>
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

<?php require 'footer.php'; ?>
</body>
</html>