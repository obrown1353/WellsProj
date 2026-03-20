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
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}

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
    body {
        background-color: #002D61;
        min-height: 100vh;
        padding-top: 95px;
        color: white;
    }
    .page-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 50px 60px;
    }
  </style>
  <title>Seacobeck Curriculum Lab | Report</title>
</head>
<body>
<?php require 'header.php'; ?>

<div class="page-wrapper text-center">
    <h1 class="text-3xl font-bold mb-2 text-center"
        style="text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black; color: #bfe5ed;">
        Generate a Report
      </h1>
      <p class="text-sm text-white mb-6 text-center opacity-80">
       Browse materials and collect information.
      </p>
</div>


<form method="GET" class="flex justify-center">
  <div class="bg-white text-black p-6 rounded-lg shadow-lg w-full max-w-md space-y-4">
    <!-- Search Bar -->
    <input 
      type="text" 
      name="search"
      value="<?= htmlspecialchars($searchQuery) ?>"
      placeholder="Search materials..."
      class="w-full px-4 py-2 border rounded"
    >

    <div class="relative w-full">
      <label class="block mb-1 font-bold text-black">Select Categories</label>

      <!-- Dropdown Button -->
      <button type="button" id="dropdownButton" class="w-full bg-white text-black px-4 py-2 rounded flex justify-between items-center">
        <span id="dropdownPlaceholder">Select categories...</span>
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div id="dropdownMenu" class="absolute mt-1 w-full bg-white border rounded shadow-lg hidden max-h-60 overflow-y-auto z-50">
        <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
          <input type="checkbox" value="Total times checked out" class="category-checkbox mr-2"> Total times checked out
        </label>
        <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
          <input type="checkbox" value="Current amount checked out" class="category-checkbox mr-2"> Current amount checked out
        </label>
        <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
          <input type="checkbox" value="Current amount in inventory" class="category-checkbox mr-2"> Current amount in inventory
        </label>
        <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
          <input type="checkbox" value="Location" class="category-checkbox mr-2"> Location
        </label>
        <label class="flex items-center px-4 py-2 hover:bg-gray-100 cursor-pointer">
          <input type="checkbox" value="Material type" class="category-checkbox mr-2"> Material type
        </label>
      </div>

      <div id="hiddenInputs"></div>
    </div>
    <!-- Submit Button -->
    <button 
      type="submit"
      class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600"
    >
      Apply Filters
    </button>

  </div>
</form>

<?php require 'footer.php'; ?>

<!-- JavaScript for selecting categories  -->
<script>
const dropdownButton = document.getElementById('dropdownButton');
const dropdownMenu = document.getElementById('dropdownMenu');
const dropdownPlaceholder = document.getElementById('dropdownPlaceholder');
const hiddenInputs = document.getElementById('hiddenInputs');
const checkboxes = document.querySelectorAll('.category-checkbox');


dropdownButton.addEventListener('click', () => {
    dropdownMenu.classList.toggle('hidden');
});


function updateSelections() {
    const selected = [];
    hiddenInputs.innerHTML = ''; 

    checkboxes.forEach(cb => {
        if (cb.checked) {
            selected.push(cb.value);
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'category[]';
            input.value = cb.value;
            hiddenInputs.appendChild(input);
        }
    });

    dropdownPlaceholder.textContent = selected.length > 0 ? selected.join(', ') : 'Select categories...';
}

checkboxes.forEach(cb => cb.addEventListener('change', updateSelections));

document.addEventListener('click', (e) => {
    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.add('hidden');
    }
});

const selectedFromPHP = <?php echo json_encode($categories); ?>;
checkboxes.forEach(cb => {
    if (selectedFromPHP.includes(cb.value)) {
        cb.checked = true;
    }
});
updateSelections();
</script>

</body>
</html>