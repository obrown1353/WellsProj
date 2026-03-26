<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_cache_expire(30);
    session_start();

    date_default_timezone_set("America/New_York");

    // Redirect to login if no session
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
    $query = "";
    $materials = fetch_all_materials();
    $results = [];
    $sort = $_GET['sort'] ?? '';
    $selectedLocations = $_GET['location'] ?? [];
    $selectedTypes = $_GET['material_type'] ?? [];

    if (!$isGuest && isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }

    if (isset($_GET['query'])) {
	    $query = strtolower(trim($_GET['query']));
    }

    $results = fetch_materials_by_query($query);

    if (!empty($sort) && !empty($results)) {

    
    usort($results, function($a, $b) use ($sort) {

        switch ($sort) {

            case "title":
                return strcmp(strtolower((string)$a->getName()), strtolower((string)$b->getName()));

            case "author":
                return strcmp(strtolower((string)$a->getAuthor()), strtolower((string)$b->getAuthor()));

            case "material_type":
                return strcmp(strtolower((string)$a->getResourceType()), strtolower((string)$b->getResourceType()));

            case "location":
                return strcmp(strtolower((string)$a->getLocation()), strtolower((string)$b->getLocation()));

            default:
                return 0;
        }
    });

    if (!empty($selectedLocations) || !empty($selectedTypes)) {
    $results = array_filter($results, function($material) use ($selectedLocations, $selectedTypes) {
        $matchLocation = empty($selectedLocations) || in_array($material->getLocation(), $selectedLocations);
        $matchType = empty($selectedTypes) || in_array($material->getResourceType(), $selectedTypes);
        return $matchLocation && $matchType;
    });
    }
}



    $notRoot = !$isAdmin;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Quicksand, sans-serif;
            background-color: #002D61 !important;
        }

        h2 {
            font-weight: normal;
            font-size: 30px;
        }

        h3 a:link, h3 a:visited, h3 a:active {
            color: #002D61;
        }

        h3 a:hover {
            color: #8DC9F7;
        }


        .full-width-bar {
            width: 100%;
            background: #8DC9F7 !important;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .full-width-bar-sub {
            width: 100%;
            background: #002D61 !important;
            padding: 17px 5%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .content-box {
            flex: 1 1 280px;
            max-width: 375px;
            padding: 10px 2px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box-sub {
            flex: 1 1 300px;
            max-width: 470px;
            padding: 10px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .content-box img {
            width: 100%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 0.5px solid #828282;
        }

        .content-box-sub img {
            width: 105%;
            height: auto;
            background: white;
            border-radius: 5px;
            border-bottom-right-radius: 50px;
            border: 1px solid #828282;
        }

        .small-text {
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 14px;
            font-weight: 700;
            color: #3A3A3A;
        }

        .large-text {
            position: absolute;
            top: 40px;
            left: 30px;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .large-text-sub {
            position: absolute;
            top: 60%;
            left: 10%;
            font-size: 22px;
            font-weight: 700;
            color: black;
            max-width: 90%;
        }

        .graph-text {
            position: absolute;
            top: 75%;
            left: 10%;
            font-size: 14px;
            font-weight: 700;
            color: #8DC9F7;
            max-width: 90%;
        }

        .navbar {
            width: 100%;
            height: 95px;
            position: fixed;
            top: 0;
            left: 0;
            background: #8DC9F7;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            padding: 0 30px;
            z-index: 1000;
        }

        .left-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo-container {
            background: #8DC9F7;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.25) inset;
        }

        .logo-container img {
            width: 128px;
            height: 52px;
            display: block;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links div {
            font-size: 24px;
            font-weight: 700;
            color: black;
            cursor: pointer;
        }

        @media (max-width: 900px) { .nav-links div { font-size: 18px; } }
        @media (max-width: 600px) { .nav-links div { font-size: 16px; } }

        .right-section {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .date-box {
            background: #8DC9F7;
            padding: 10px 30px;
            border-radius: 50px;
            box-shadow: -4px 4px 4px rgba(0, 0, 0, 0.25) inset;
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
        }

        .icon { width: 47px; height: 47px; border-radius: 50%; }

        .arrow-button {
            position: absolute;
            bottom: 30px;
            right: 30px;
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .arrow-button:hover { transform: translateX(5px); }

        .circle-arrow-button {
            position: absolute;
            bottom: 30px;
            right: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            border: none;
            font-size: 20px;
            font-family: Quicksand, sans-serif;
            font-weight: bold;
            color: black;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .circle {
            width: 30px;
            height: 30px;
            background-color: #8DC9F7;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            transition: transform 0.3s ease;
        }

        .circle-arrow-button:hover { background-color: transparent !important; }
        .circle-arrow-button:hover .circle { transform: translateX(5px); }

        .colored-box {
            display: inline-block;
            background-color: #8DC9F7;
            color: white;
            padding: 1px 5px;
            border-radius: 5px;
            font-weight: bold;
        }

        .footer {
            width: 100%;
            background: #8DC9F7;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 30px 50px;
            flex-wrap: wrap;
        }

        .footer-left { display: flex; flex-direction: column; align-items: center; }
        .footer-logo { width: 150px; margin-bottom: 15px; }
        .social-icons { display: flex; gap: 15px; }
        .social-icons a { color: white; font-size: 20px; transition: color 0.3s ease; }
        .social-icons a:hover { color: #dcdcdc; }
        .footer-right { display: flex; gap: 50px; flex-wrap: wrap; align-items: flex-start; }
        .footer-section { display: flex; flex-direction: column; justify-content: center; gap: 10px; color: #8DC9F7; font-family: Inter, sans-serif; font-size: 16px; font-weight: 500; }
        .footer-topic { font-size: 18px; font-weight: bold; }
        .footer a { color: white; text-decoration: none; transition: background 0.2s ease, color 0.2s ease; padding: 5px 10px; border-radius: 5px; }
        .footer a:hover { background: rgba(255, 255, 255, 0.1); color: #dcdcdc; }

        .background-image { width: 100%; border-radius: 10px; }

        .icon-overlay {
            position: absolute;
            top: 40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.8);
            padding: 10px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .icon-overlay img { width: 40px; height: 40px; opacity: 0.9; }

        .content-box-test:hover .icon-overlay img {
            transform: scale(1.1) rotate(5deg);
            transition: transform 0.5s ease;
        }

        .content-box-test {
            position: relative;
            background-color: #8DC9F7 !important;
            border-radius: 12px;
            padding: 20px;
            color: black;
            flex: 1 1 280px;
            max-width: 375px;
            min-height: 250px;
        }

        .content-box-test .large-text-sub,
        .content-box-test .graph-text { color: black; }

        .background-image { display: none; }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const extra = document.querySelector(".extra-info");
            if (extra) extra.style.maxHeight = "0px";
        });
        function toggleInfo(event) {
            event.stopPropagation();
            let info = event.target.nextElementSibling;
            let isVisible = info.style.maxHeight !== "0px";
            info.style.maxHeight = isVisible ? "0px" : "100px";
            event.target.innerText = isVisible ? "↓" : "↑";
        }
    </script>
</head>

<body>
<?php require 'header.php'; ?>

    <!-- MAIN TWO-COLUMN LAYOUT -->
<div style="flex: 1; display: flex; width: 100%; gap: 40px; justify-content: center; align-items: flex-start;">
    <!-- LEFT SIDEBAR (Filters) -->
    <div style="flex: 0 0 25%; border: 2px solid #8DC9F7; border-radius: 12px; padding: 20px; background-color: #0067A2; position: sticky; top: 120px; height: fit-content; overflow-y: auto; max-height: 90vh;">
        <h3>Filters</h3>
        <hr>


        <form action="results.php" method="GET">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
            <?php if (isset($_GET['sort'])):?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort'] ?? []); ?>">
            <?php endif; ?>

        <!-- Location -->
        <details>
        <summary style="font-weight:bold; cursor:pointer; margin-bottom:10px;">Location</summary>

        <input type="checkbox" name="location[]" value="Early Readers1" id="loc-early1"
        <?php if(in_array("Early Readers 1", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-early1">Early Readers 1</label><br>

        <input type="checkbox" name="location[]" value="Early Readers2" id="loc-early2"
        <?php if(in_array("Early Readers 2", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-early2">Early Readers 2</label><br>

        <input type="checkbox" name="location[]" value="General Fiction A-M" id="loc-gen-a-m"
        <?php if(in_array("General Fiction A-M", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-gen-a-m">General Fiction A-M</label><br>

        <input type="checkbox" name="location[]" value="General Fiction N-Z" id="loc-gen-n-z"
        <?php if(in_array("General Fiction N-Z", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-gen-n-z">General Fiction N-Z</label><br>

        <input type="checkbox" name="location[]" value="General Nonfiction" id="loc-nonfiction"
        <?php if(in_array("General Nonfiction", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-nonfiction">General Nonfiction</label><br>

        <input type="checkbox" name="location[]" value="Holiday" id="loc-holiday"
        <?php if(in_array("Holiday", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-holiday">Holiday</label><br>

        <input type="checkbox" name="location[]" value="Middle Grade Novels" id="loc-middle-grade"
        <?php if(in_array("Middle Grade Novels", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-middle-grade">Middle Grade Novels</label><br>

        <input type="checkbox" name="location[]" value="Multilingual" id="loc-multilingual"
        <?php if(in_array("Multilingual", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-multilingual">Multilingual</label><br>

        <input type="checkbox" name="location[]" value="Realistic Fiction A-G" id="loc-realistic-a-g"
        <?php if(in_array("Realistic Fiction A-G", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-realistic-a-g">Realistic Fiction A-G</label><br>

        <input type="checkbox" name="location[]" value="Realistic Fiction H-Z" id="loc-realistic-h-z"
        <?php if(in_array("Realistic Fiction H-Z", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-realistic-h-z">Realistic Fiction H-Z</label><br>

        <input type="checkbox" name="location[]" value="Science A-F" id="loc-science-a-f"
        <?php if(in_array("Science A-F", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-a-f">Science A-F</label><br>

        <input type="checkbox" name="location[]" value="Science A-M" id="loc-science-a-m"
        <?php if(in_array("Science A-M", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-a-m">Science A-M</label><br>

        <input type="checkbox" name="location[]" value="Science G-Q" id="loc-science-g-q"
        <?php if(in_array("Science G-Q", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-g-q">Science G-Q</label><br>

        <input type="checkbox" name="location[]" value="Science N-Z" id="loc-science-n-z"
        <?php if(in_array("Science N-Z", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-n-z">Science N-Z</label><br>

        <input type="checkbox" name="location[]" value="Science R-Z" id="loc-science-r-z"
        <?php if(in_array("Science R-Z", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-r-z">Science R-Z</label><br>

        <input type="checkbox" name="location[]" value="Science Resources" id="loc-science-resources"
        <?php if(in_array("Science Resources", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-science-resources">Science Resources</label><br>

        <input type="checkbox" name="location[]" value="Social Studies" id="loc-social-studies"
        <?php if(in_array("Social Studies", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-social-studies">Social Studies</label><br>

        <input type="checkbox" name="location[]" value="Social Studies Stories A-F" id="loc-social-f"
        <?php if(in_array("Social Studies Stories A-F", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-social-f">Social Studies Stories A-F</label><br>

        <input type="checkbox" name="location[]" value="Social Studies Stories A-L" id="loc-social-l"
        <?php if(in_array("Social Studies Stories A-L", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-social-l">Social Studies Stories A-L</label><br>

        <input type="checkbox" name="location[]" value="Social Studies Stories G-O" id="loc-social-g-o"
        <?php if(in_array("Social Studies Stories G-O", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-social-g-o">Social Studies Stories G-O</label><br>

        <input type="checkbox" name="location[]" value="Social Studies Stories P-Z" id="loc-social-p-z"
        <?php if(in_array("Social Studies Stories P-Z", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-social-p-z">Social Studies Stories P-Z</label><br>

        <input type="checkbox" name="location[]" value="Trad Folk" id="loc-trad-folk"
        <?php if(in_array("Trad Folk", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-trad-folk">Trad/Folk</label><br>

        <input type="checkbox" name="location[]" value="Transportation" id="loc-transportation"
        <?php if(in_array("Transportation", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-transportation">Transportation</label><br>

        <input type="checkbox" name="location[]" value="Wordless Picture Books" id="loc-wordless"
        <?php if(in_array("Wordless Picture Books", $_GET['location'] ?? [])) echo 'checked'; ?>>
        <label for="loc-wordless">Wordless Picture Books</label><br>

    </details>

    <br>

        <!-- Material Type -->
        <details>
        <summary style="font-weight:bold; cursor:pointer; margin-bottom:10px;">Material Type</summary>

        <input type="checkbox" name="material_type[]" value="Children's Literature" id="mat-child-lit"
        <?php if(in_array("Children's Literature", $_GET['material_type'] ?? [])) echo 'checked'; ?>>
        <label for="mat-child-lit">Children's Literature</label><br>

        <input type="checkbox" name="material_type[]" value="Math Manipulatives" id="mat-math"
        <?php if(in_array("Math Manipulatives", $_GET['material_type'] ?? [])) echo 'checked'; ?>>
        <label for="mat-math">Math Manipulatives</label><br>

        <input type="checkbox" name="material_type[]" value="Professional Text" id="mat-prof"
        <?php if(in_array("Professional Text", $_GET['material_type'] ?? [])) echo 'checked'; ?>>
        <label for="mat-prof">Professional Text</label><br>

        <input type="checkbox" name="material_type[]" value="Textbook" id="mat-textbook"
        <?php if(in_array("Textbook", $_GET['material_type'] ?? [])) echo 'checked'; ?>>
        <label for="mat-textbook">Textbook</label><br>

        <input type="checkbox" name="material_type[]" value="Supplies" id="mat-supplies"
        <?php if(in_array("Supplies", $_GET['material_type'] ?? [])) echo 'checked'; ?>>
        <label for="mat-supplies">Supplies</label><br>

        </details>
        </br>
        <button type="submit" style="padding: 10px 20px; border-radius: 12px; border:none; background:#fff; color:#0067A2; font-weight:bold; cursor:pointer;">
            Apply Filters
        </button>
    </form>

</div>


    <!-- RIGHT SIDE: MAIN CONTENT -->
    <div style="flex: 1; max-width: 900px;">

    <!--Search Bar -->
    <div style="display: flex; justify-content: center; margin: 40px 0;">
        <div style="width:100%; max-width: 900px; border: 3px solid #0067A2; border-radius: 16px; padding: 30px; background-color: #8DC9F7;">
            <form action="results.php" method="GET" style="width: 100%; max-width: 900px; display: flex;">
                <div style="position: relative; width:100%;">
                <input type="text" name="query" placeholder="Search materials..."
                    style="flex: 7; width: 100%; max-width: 900px; padding: 12px 16px; font-size: 16px; border: 1px solid #ccc; border-radius: 20px; outline: none; color: #0067A2;">
                <button type="submit" style="position: absolute; right: 0; top: 0; height: 83%; width: 120px; border: 1px solid #ccc; border-radius:0 20px 20px 0; background: #0067A2; color: white; font-size: 16px; cursor: pointer;">
                Search
            </button>
            </div>
            </form>
        </div>
    </div>


    <!-- Sort by -->
    <div style="display: flex; justify-content: center; margin-top: -20px;">
        <form action="results.php" . method="GET" style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
        <input type="hidden" name="query" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
        
        <span style="font-weight: bold; white-space: nowrap;">Sort by: </span>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="title" onchange="this.form.submit()"
                <?php if ($sort === 'title') echo 'checked'; ?>> Title
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="author" onchange="this.form.submit()"
                <?php if ($sort === 'author') echo 'checked'; ?>> Author
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="material_type" onchange="this.form.submit()"
                <?php if ($sort === 'material_type') echo 'checked'; ?>> Material Type
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="location" onchange="this.form.submit()"
                <?php if ($sort === 'location') echo 'checked'; ?>> Location
        </label>

        </form>

    </div>


    <!-- Search Results -->
    <div style="margin-top: 30px; padding: 30px 20px;">
        <h2><b>Search Results</b></h2>
        <!-- Results from database go here -->
        <?php
            if (!empty($results)) {
                    foreach ($results as $material) {
                        echo "<div style='background:white; color:#0067A2; padding:20px; margin-bottom:15px; border-radius:12px;'>";
                        echo "<h3><a href='self_service.php?material_id=" . $material->getMaterialID() . "'>" .  $material->getName() .  "</a></h3>";
                        echo "<p><b>Author:</b> " . $material->getAuthor() . "</p>";
                        echo "<p><b>ISBN:</b> " . $material->getISBN() . "</p>";
                        echo "<p><b>Location:</b> " . $material->getLocation() . "</p>";
                        echo "<p><b>Material Type:</b> " . $material->getResourceType() . "</p>";
                        echo "<p>" . $material->getDescription() . "</p>";
                        echo "<p><b>Available:</b> " . $material->getCopyInstock() . " / " . $material->getCopyCapacity() . "</p>";
                        if ($material->canBeCheckedOut()) {
                            echo "<p style='color:green'><b>Available for Checkout</b></p>";
                        } else {
                            echo "<p style='color:red'><b>Out of Stock</b></p>";
                        }
                        echo "</div>";
                }

            } else {
                echo "<p>No materials found.</p>";
            }

        ?>
    </div>
</div>
</div>
</div>

    <!-- Footer -->
    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <?php require 'footer.php'; ?>
</body>


</html>

