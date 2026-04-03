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
      margin-bottom: 0.5rem; 
      text-align: center; 
      color: #bfe5ed;
      text-shadow: 
        1px 1px 0 black,
        -1px -1px 0 black,
        1px -1px 0 black,
        -1px 1px 0 black;
}
    body {
/*        background-color: #002D61; */
        min-height: 100vh;
        padding-top: 95px;
	color: white;
/*        display: flex; */
/*        flex-direction: column; */
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
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 50px 60px;
    }
    
   .optionsFlex {
    display: none;     
    flex-direction: column; 
    align-items: center;  
    margin-top: 10px;
    gap: 10px;          
}
.optionsGroup {
    display: flex;       
    gap: 10px;            
}
.searchBar {
  background-color: #ffffff; 
  color: #000000; 
  padding: 1.5rem; 
  border-radius: 0.5rem; 
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1),
              0 4px 6px rgba(0, 0, 0, 0.1); 
  width: 100%; 
  max-width: 28rem; 
}
.subheading {
  font-size: 0.875rem;        
  color: #ffffffff;            
  margin-bottom: 1.5rem;     
  text-align: center;        
  opacity: 0.8;              
}
.optionsWrapper{
   display: flex;              
  justify-content: center;   
}
.dropdown-button {
  width: 100%;                  
  background-color: #ffffff;   
  color: #000000;               
  padding: 0.5rem 1rem;         
  border-radius: 0.25rem;      
  display: flex;                
  justify-content: space-between; 
  align-items: center;        
  border: none;
  cursor: pointer;
}

.dropdown-button svg {
  width: 1.25rem;               
  height: 1.25rem;              
  stroke: currentColor;
  fill: none;
}

.dropdownMenu {
  position: absolute;          
  margin-top: 0.25rem;         
  width: 100%;                  
  background-color: #ffffff;    
  border: 1px solid #e5e7eb;    
  border-radius: 0.25rem;      
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1), 
              0 4px 6px rgba(0, 0, 0, 0.05); 
  display: none;               
  max-height: 15rem;            
  overflow-y: auto;             
  z-index: 50;                  
}

.dropdownMenu.show {
  display: block;
}

.dropdownItem {
  display: flex;              
  align-items: center;        
  padding: 0.5rem 1rem;       
  cursor: pointer;            
}

.dropdownItem:hover {
  background-color: #f3f4f6;  
}

.submit {
  width: 100%;                  
  background-color: #3b82f6;    
  color: #ffffff;               
  padding: 0.5rem 0;           
  border-radius: 0.25rem;       
  border: none;
  cursor: pointer;
}

.submit:hover {
  background-color: #2563eb;    
}

  </style>
  <title>Seacobeck Curriculum Lab | Report</title>
</head>
<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper text-center">
    <h1 class="title">
        Generate a Report
      </h1>
      <p class="subheading">
       Browse materials and collect information.
      </p>
</div>


<form method="GET" class="optionsWrapper">
  <div class="searchBar">
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
      <button type="button" id="dropdownButton" class="dropdown-button">
        <span id="dropdownPlaceholder">Select categories...</span>
        <svg viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div id="dropdownMenu" class="dropdownMenu">
        <label class="dropdownItem">
          <input type="checkbox" value="Total times checked out" class="category-checkbox mr-2"> Total times checked out
        </label>
        <label class="dropdownItem">
          <input type="checkbox" value="Current amount checked out" class="category-checkbox mr-2"> Current amount checked out
        </label>
        <label class="dropdownItem">
          <input type="checkbox" value="Current amount in inventory" class="category-checkbox mr-2"> Current amount in inventory
        </label>
        <label class="dropdownItem">
          <input type="checkbox" value="Location" class="category-checkbox mr-2"> Location
        </label>
        <label class="dropdownItem">
          <input type="checkbox" value="Material type" class="category-checkbox mr-2"> Material type
        </label>
        <label class="dropdownItem">
          <input type="checkbox" value="Date Added" class="category-checkbox mr-2"> Date Added
        </label>
      </div>

      <div id="hiddenInputs"></div>
    </div>
 <!-- Submit Button -->
<button onclick="document.getElementById('options').style.display='flex';"
  type="button" class="submit"
>
  Generate
</button>

<div id="options" class="optionsFlex">
  <p>Select file type:</p>
  <div class="optionsGroup">
    <button>CSV</button>
    <button>XLSX</button>
  </div>
</div>
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
    dropdownMenu.classList.toggle('show');
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
        dropdownMenu.classList.add('show');
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
