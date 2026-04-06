<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}

include_once('database/dbPersons.php');
include_once('domain/Person.php');

$accessLevel = (int) $_SESSION['access_level'];
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}

include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";
if (isset($_GET['material_id'])){
  $id = $_GET['material_id'] ?? '';
} else {
  header('Location: results.php');
}
$material = fetch_material_by_id($id);

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
* { font-family: StromaBold, 'Inter', sans-serif; }
.body{
  min-height: 100vh;       
  display: flex;             
  flex-direction: column;    
  background-size: cover;    
  background-position: center; 
  position: relative;        
}
.input-field {
  width: 100%;
  background: rgba(255,255,255,0.88);
  color: #111;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 15px;
  outline: none;
  transition: box-shadow 0.2s, border-color 0.2s;
  margin-bottom: 12px;
}
.input-field:focus {
  box-shadow: 0 0 0 3px rgba(156,32,7,0.25);
  border-color: #9C2007;
}
.input-field::placeholder { color: #6b7280; }
#toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  color: white;
  padding: 14px 28px;
  border-radius: 10px;
  font-size: 15px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  transition: transform 0.4s ease, opacity 0.4s ease;
  opacity: 0;
  z-index: 9999;
  text-align: center;
  white-space: nowrap;
}
.pageWrapper {
  display: flex;              
  flex-grow: 1;                
  align-items: center;        
  justify-content: center;     
  position: relative;        
  z-index: 10;               
}
.textWrapper {
  width: 100%;                        
  padding: 2rem 1.5rem;              
  display: flex;                      
  flex-direction: column;              
  align-items: center;                 
  color: white;                      
  background-color: rgba(141, 201, 247, 0.1); 
  backdrop-filter: blur(8px);          
  border-radius: 1rem;               
  box-shadow: 0 10px 15px rgba(0, 0, 0, 0.25); 
  max-width: 100%;                    
}

@media (min-width: 640px) {          
  .textWrapper {
    width: 66.666667%;                
    max-width: 28rem;                  
  }
}
.header {
  font-size: 1.875rem;      
  font-weight: 700;          
  margin-bottom: 0.5rem;     
  text-align: center;       
  color: white;        
}
 .subheading {
  font-size: 0.875rem;   
  color: white;           
  margin-bottom: 0.5rem;  
  text-align: left;            
}
.buttonsWrapper {
  display: flex;                     
  width: 100%;                       
  margin-bottom: 1.25rem;           
  border-radius: 1rem;               
  overflow: hidden;                  
  border: 1px solid rgba(255, 255, 255, 0.2); 
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);   
}
.checkoutOption {
  flex: 1;                               
  padding-top: 0.75rem;                  
  padding-bottom: 0.75rem;               
  font-size: 0.875rem;                    
  font-weight: 700;                        
  letter-spacing: 0.05em;                 
  transition: all 0.2s ease-in-out;       
  background-color: #0d2b8d;           
  color: white;                          
}
.returnOption {
  flex: 1;                              
  padding-top: 0.75rem;                    
  padding-bottom: 0.75rem;                
  font-size: 0.875rem;                      
  font-weight: 700;                          
  letter-spacing: 0.05em;                  
  transition: all 0.2s ease-in-out;         
  background-color: rgba(255, 255, 255, 0.1); 
  color: rgba(255, 255, 255, 0.5);        
}
.checkoutButton {
  width: 100%;
  background-color: #0d2b8d;
  color: white;
  font-weight: 700;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.checkoutButton:hover {
  background-color: #0a1e61;
}

.checkoutButton:active {
  transform: scale(0.95);
}
.returnButton {
  width: 100%;
  background-color: #9C2007;
  color: white;
  font-weight: 700;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.returnButton:hover {
  background-color: #7a1905;
}

.returnButton:active {
  transform: scale(0.95);
}
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
#toast.success { background: #002D61; border-left: 5px solid #8df79d; }
#toast.error   { background: #002D61; border-left: 5px solid #f87171; }
#toast.warning { background: #002D61; border-left: 5px solid #fbbf24; }
</style>
<title>Seacobeck Curriculum Lab | Self Service</title>
</head>
<body class="body"
  style="background-image: url('images/library.jpg'); padding-top: 95px;">

  <?php require 'header.php'; ?>

  <div class="absolute inset-0 bg-[#002D61]/85" style="top: 95px;"></div>
  <div id="toast"></div>

  <div class="pageWrapper">
    <div class="textWrapper">

      <h2 class="header">
        SELF SERVICE
      </h2>

      <p class="subheading">
        <?php echo $material->getName(); ?>
      </p>
      <p class="subheading">
        Location: <?php echo htmlspecialchars($material->getLocation()); ?> | Resource Type: <?php echo htmlspecialchars($material->getResourceType()); ?><br>
        Copy Instock: <?php echo htmlspecialchars($material->getCopyInstock()); ?> | Copy Total: <?php echo htmlspecialchars($material->getCopyCapacity()); ?><br>
        <?php if ($material->getISBN()): ?>ISBN: <?php echo htmlspecialchars($material->getISBN()); ?> <?php endif; ?>
        <?php if ($material->getAuthor()): ?>| Author: <?php echo htmlspecialchars($material->getAuthor()); ?><?php endif; ?><br>
        <?php if ($material->getDescription()): ?>Description: <?php echo htmlspecialchars($material->getDescription()); ?><?php endif; ?>
      </p>

      <!-- TOGGLE -->
      <div class="buttonsWrapper">
        <button type="button" id="tab-checkout" onclick="switchMode('checkout')"
          class="checkoutOption">
          📤 Check Out
        </button>
        <button type="button" id="tab-return" onclick="switchMode('return')"
          class="returnOption">
          📥 Return
        </button>
      </div>

      <!-- FORM -->
      <div class="w-full">
        <form action="./handle_self_service.php?id=' . (<?php echo (int)$id; ?> ?? 0)" method="post">
          <input type="text"  name="first_name" placeholder="First Name" class="input-field" required />
          <input type="text"  name="last_name"  placeholder="Last Name"  class="input-field" required />
          <input type="email" name="email"      placeholder="Email"      class="input-field" required />
          <input type="hidden" name="id" value="<?php echo (int)$id; ?>" />

          <div id="checkout-btn">
            <input type="submit" name="Checkout" value="Checkout"
              class="checkoutButton">
          </div>

          <div id="return-btn" style="display:none;">
            <input type="submit" name="Return" value="Return"
              class="returnButton">
          </div>
        </form>
      </div>

    </div>
  </div>

  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Melissa Wells
    <a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">mwells@umw.edu</a>
  </footer>

  <script>
    function switchMode(mode) {
      const checkoutBtn  = document.getElementById('checkout-btn');
      const returnBtn    = document.getElementById('return-btn');
      const tabCheckout  = document.getElementById('tab-checkout');
      const tabReturn    = document.getElementById('tab-return');

      if (mode === 'checkout') {
        checkoutBtn.style.display = 'block';
        returnBtn.style.display   = 'none';
        tabCheckout.style.background = '#0d2b8d';
        tabCheckout.style.color      = 'white';
        tabCheckout.style.opacity    = '1';
        tabReturn.style.background   = 'rgba(255,255,255,0.1)';
        tabReturn.style.color        = 'rgba(255,255,255,0.5)';
      } else {
        checkoutBtn.style.display = 'none';
        returnBtn.style.display   = 'block';
        tabReturn.style.background   = '#9C2007';
        tabReturn.style.color        = 'white';
        tabReturn.style.opacity      = '1';
        tabCheckout.style.background = 'rgba(255,255,255,0.1)';
        tabCheckout.style.color      = 'rgba(255,255,255,0.5)';
      }
    }

    const statusMap = {
      'checkout_success':  { msg: '✅ Checked out! Confirmation email sent.',        type: 'success' },
      'checkout_no_email': { msg: '⚠️ Checked out, but email failed to send.',       type: 'warning' },
      'checkout_fail':     { msg: '❌ Checkout failed. Please try again.',            type: 'error'   },
      'return_success':    { msg: '✅ Returned! Confirmation email sent.',            type: 'success' },
      'return_no_email':   { msg: '⚠️ Returned, but email failed to send.',          type: 'warning' },
      'return_fail':       { msg: '❌ Return failed. Material not found for that email.', type: 'error'},
      'cant_checkout':     { msg: '⚠️ Material not available to be checked out.', type: 'warning'},
      'cant_return':       { msg: '⚠️ Material not available to be returned.', type: 'warning'},
    };

    const status = "<?php echo htmlspecialchars($status); ?>";
    if (status && statusMap[status]) {
      const { msg, type } = statusMap[status];
      const toast = document.getElementById('toast');
      toast.textContent = msg;
      toast.className = `show ${type}`;
      setTimeout(() => { toast.className = ''; }, 5000);
      history.replaceState(null, '', 'self_service.php');
    }
  </script>

</body>
</html>
