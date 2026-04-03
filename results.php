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
    $results = [];
    $sort = $_GET['sort'] ?? 'material_id';
    $selectedLocations = $_GET['location'] ?? [];
    $selectedTypes = $_GET['resource_type'] ?? [];

    if (!$isGuest && isset($_SESSION['_id'])) {
        $person = retrieve_person($_SESSION['_id']);
    }

    if (isset($_GET['query'])) {
	    $query = strtolower(trim($_GET['query']));
    }

    $allResults = fetch_materials_by_query($query, $sort);
    
    if (!empty($selectedLocations) || !empty($selectedTypes)) {
        $allResults = array_filter($allResults, function($material) use ($selectedLocations, $selectedTypes) {
            $matchLocation = empty($selectedLocations) || in_array($material->getLocation(), $selectedLocations);
            $matchType = empty($selectedTypes) || in_array($material->getResourceType(), $selectedTypes);
            return $matchLocation && $matchType;
        });
    }

    $allResults = array_values($allResults); 
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

    $notRoot = !$isAdmin;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="./css/base.css" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Search Results</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Quicksand, sans-serif;
	/*    background-color: #002D61 !important; */
	    padding-top: 95px;
	/*    min-height: 100vh;
            display: flex;
	    flex-direction: column; */
            justify-content: space-between;
            background-image: url('images/library.jpg');
            background-size: cover;
            background-position: center;
	    position: relative;
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

        .overlay {
            position: absolute;
            inset: 0;
            background: rgb(0, 45, 97, 0.88);
            z-index: -1;
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
            font-family: Quicksand, sans-serif;
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

        .outer {
            display: flex;
            gap: 36px;
            max-width: 1280px;
            margin: 0 auto;
            padding: 40px 32px 80px;
            align-items: flex-start;
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
            border-radius: 8px !important;
            border: 2px solid #8DC9F7 !important;
            background: transparent !important;
            color: white !important;
            font-family: Quicksand, sans-serif;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s, color 0.18s;
            width: auto !important;
        }

        .page-btn:hover {
            background: #8DC9F7 !important;
            color: #002D61 !important;
        }

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
            width: 85px !important;
            height: 38px !important;
            background: #002D61 !important;
            border: 2px solid #8DC9F7 !important;
            border-radius: 8px !important;
            padding: 0 10px !important;
            font-family: Quicksand, sans-serif !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            color: #8DC9F7 !important;
            display: inline-block !important;
            margin: 0 !important;
            vertical-align: middle;
            text-align: center !important;
        }

        .pagination form {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            margin-left: 8px !important;
            width: auto !important;
            background: transparent !important;
        }
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
    <div class="overlay"></div>

    <div class="outer">

    <div class="filter-sidebar">
        <h3>🔍 Filters</h3>
        <hr>

        <form method="GET" action="results.php">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
            <input type="hidden" name="sort"  value="<?php echo htmlspecialchars($sort); ?>">

            <details <?php if (!empty($selectedLocations)) echo 'open'; ?>>
                <summary>Location</summary>
                <div class="check-group">
                <?php
                $locations = [
                    "Early Readers1"             => "Early Readers 1",
                    "Early Readers2"             => "Early Readers 2",
                    "General Fiction A-M"        => "General Fiction A-M",
                    "General Fiction N-Z"        => "General Fiction N-Z",
                    "General Nonfiction"         => "General Nonfiction",
                    "Holiday"                    => "Holiday",
                    "Middle Grade Novels"        => "Middle Grade Novels",
                    "Multilingual"               => "Multilingual",
                    "Realistic Fiction A-G"      => "Realistic Fiction A-G",
                    "Realistic Fiction H-Z"      => "Realistic Fiction H-Z",
                    "Science A-F"                => "Science A-F",
                    "Science A-M"                => "Science A-M",
                    "Science G-Q"                => "Science G-Q",
                    "Science N-Z"                => "Science N-Z",
                    "Science R-Z"                => "Science R-Z",
                    "Science Resources"          => "Science Resources",
                    "Social Studies"             => "Social Studies",
                    "Social Studies Stories A-F" => "Social Studies Stories A-F",
                    "Social Studies Stories A-L" => "Social Studies Stories A-L",
                    "Social Studies Stories G-O" => "Social Studies Stories G-O",
                    "Social Studies Stories P-Z" => "Social Studies Stories P-Z",
                    "Trad Folk"                  => "Trad/Folk",
                    "Transportation"             => "Transportation",
                    "Wordless Picture Books"     => "Wordless Picture Books",
                ];
                foreach ($locations as $val => $label):
                    $checked = in_array($val, $selectedLocations) ? 'checked' : '';
                ?>
                <div>
                    <input type="checkbox" name="location[]" value="<?php echo $val; ?>"
                           id="loc-<?php echo $val; ?>" <?php echo $checked; ?>>
                    <label for="loc-<?php echo $val; ?>"><?php echo $label; ?></label>
                </div>
                <?php endforeach; ?>
                </div>
            </details>

            <br>

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
                           id="type-<?php echo $val; ?>" <?php echo $checked; ?>>
                    <label for="type-<?php echo $val; ?>"><?php echo $label; ?></label>
                </div>
                <?php endforeach; ?>
                </div>
            </details>

            <button type="submit" class="apply-btn">✓ Apply Filters</button>
            <?php if (!empty($selectedLocations) || !empty($selectedTypes)): ?>
                <a href="results.php?query=<?php echo urlencode($_GET['query'] ?? ''); ?>&sort=<?php echo urlencode($sort); ?>" class="clear-link">Clear filters</a>
            <?php endif; ?>
        </form>
    </div>


    <div style="flex: 1; max-width: 900px;">

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


    <div style="display: flex; justify-content: center; margin-top: -20px;">
        <form action="results.php?" method="GET" style="display: flex; gap: 20px; align-items: center; max-width: 900px;">
        <input type="hidden" name="query" value="<?php echo htmlspecialchars($_GET['query'] ?? ''); ?>">
        <?php foreach ($selectedLocations as $location){
            echo "<input type='hidden' name='location[]' value='" . htmlspecialchars($location) . "'>";
        }?>
        <?php foreach ($selectedTypes as $resource_type){
            echo "<input type='hidden' name='resource_type[]' value='" . htmlspecialchars($resource_type) . "'>";
        }?>

        <span style="font-weight: bold; white-space: nowrap;">Sort by: </span>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="name" onchange="this.form.submit()"
                <?php if ($sort === 'name') echo 'checked'; ?>> Name
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="author" onchange="this.form.submit()"
                <?php if ($sort === 'author') echo 'checked'; ?>> Author
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="resource_type" onchange="this.form.submit()"
                <?php if ($sort === 'resource_type') echo 'checked'; ?>> Resource Type
        </label>

        <label style="color:white; white-space: nowrap;">
            <input type="radio" name="sort" value="location" onchange="this.form.submit()"
                <?php if ($sort === 'location') echo 'checked'; ?>> Location
        </label>

        </form>

    </div>


    <div style="margin-top: 30px; padding: 30px 20px;">
        <h2 style="color:white"><b>Search Results</b> (<?php echo $totalItems; ?>)</h2>
        <p style="font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 20px;">
                Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $perPage, $totalItems); ?> of <?php echo $totalItems; ?> materials
        </p>

        <?php
            if (!empty($results)) {
                    foreach ($results as $material) {
                        echo "<div style='background:white; color:#0067A2; padding:20px; margin-bottom:15px; border-radius:12px;'>";
                        echo "<h3><a href='self_service.php?material_id=" . $material->getMaterialID() . "'>" .  $material->getName() .  "</a></h3>";
                        echo "<p><b>Author:</b> " . $material->getAuthor() . "</p>";
                        echo "<p><b>ISBN:</b> " . $material->getISBN() . "</p>";
                        echo "<p><b>Location:</b> " . $material->getLocation() . "</p>";
                        echo "<p><b>Resource Type:</b> " . $material->getResourceType() . "</p>";
                        echo "<p>" . $material->getDescription() . "</p>";
                        echo "<p><b>Available:</b> " . $material->getCopyInstock() . " / " . $material->getCopyCapacity() . "</p>";
                        if ($material->canBeCheckedOut()) {
                            echo "<p style='color:green'><b>Available for Checkout</b></p>";
                        } else {
                            echo "<p style='color:red'><b>Out of Stock</b></p>";
                        }
                        echo "</div>";
                }

                if ($totalPages > 1):
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

                    // Numbered buttons
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

                    <form method="GET" action="results.php">
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
                <?php endif; 

            } else {
                echo "<p style='color:white'>No materials found.</p>";
            }

        ?>
    </div>
</div>
</div>

    <div style="width: 90%; height: 100%; outline: 1px #8DC9F7 solid; outline-offset: -0.5px; margin: 70px auto; padding: 1px 0;"></div>

    <?php require 'footer.php'; ?>
</body>


</html>
