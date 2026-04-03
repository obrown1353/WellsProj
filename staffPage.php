<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    exit();
}

include_once('database/dbPersons.php');
include_once('domain/Person.php');
include_once('database/dbCheckout.php');
include_once('database/dbReturns.php');
include_once('database/dbMaterials.php');

$accessLevel = (int) $_SESSION['access_level'];
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);



// Handle filters
$searchQuery = $_GET['search'] ?? '';
$categories = $_GET['category'] ?? []; // multiple categories
?>
<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    * { font-family: StromaBold, 'Lucida Sans'; }
.title {
  font-size: 1.875rem; 
  line-height: 2.25rem;
  font-weight: 700; 
  margin: 5rem auto 0 auto;  /* top margin pushes it down, auto centers horizontally */
  text-align: center; 
  color: white;

  text-shadow: 
    1px 1px 0 black,
    -1px -1px 0 black,
    1px -1px 0 black,
    -1px 1px 0 black;

  background-color: #3b82f6;
  border: 2px solid black;
  border-radius: 12px;
  padding: 0.5rem 2.5rem;

  display: block;
  width: fit-content;
}
body {
/*    background-color: #002D61; */
    min-height: 100vh;
    padding-top: 95px;
    color: white;
    flex-direction: column;
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

.button-group {
  display: flex;               
  justify-content: center;      
  gap: 1rem;                    
  margin-top: 2rem;            
}


.button {
  background-color: #3b82f6;  
  color: white;
  border: 2px solid black;
  border-radius: 8px;
  padding: 0.5rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  text-shadow: 1px 1px 0 black;
  transition: transform 0.2s, background-color 0.2s;
}

.button:hover {
  background-color: #ff6666;
  transform: scale(1.05);
}
  </style>

  <title>Seacobeck Curriculum Lab | Staff Page</title>
</head>
<body>
<?php require 'header.php'; ?>
    <div class="overlay"></div>

    <div>
        <h1 class="title"> Manage Checkouts</h1> 
        <div class="button-group">
            <a href="viewCheckouts.php" class="button">View Checkouts</a>           
            <a href="importMaterials.php" class="button">Import Materials</a> 
        </div>
        <h1 class="title"> Manage Inventory</h1>
        <div class="button-group">
            <a href="viewMaterials.php" class="button">Catalog</a>
            <a href="viewLogs.php" class="button">View Logs</a>  
            <a href="genReport.php" class="button">Generate Reports</a>         
        </div>
    <?php
    //Admin only features
    if (!isset($_SESSION['logged_in']) || $_SESSION['access_level'] === 2) {
    echo('
        
        <h1 class="title"> Admin</h1>
        <div class="button-group">
            <a href="view-worker.php" class="button">View Accounts</a>           
            <a href="create-worker.php" class="button">Create worker</a> 
            <a href="delete-worker.php" class="button">Delete worker</a> 
        </div>');
    }
    ?>
    </div>

<?php require 'footer.php'; ?>

</body>
</html>
