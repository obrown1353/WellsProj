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
$id = 1; //change this to update on id sent to page
$material = fetch_material_by_id($id);

$status = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
* { font-family: StromaBold, 'Lucida Sans'; }
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
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
#toast.success { background: #002D61; border-left: 5px solid #8df79d; }
#toast.error   { background: #002D61; border-left: 5px solid #f87171; }
#toast.warning { background: #002D61; border-left: 5px solid #fbbf24; }
</style>
<title>Seacobeck Curriculum Lab | Self Service</title>
</head>
<body class="min-h-screen flex flex-col bg-cover bg-center relative"
  style="background-image: url('images/library.jpg'); padding-top: 95px;">

  <?php require 'header.php'; ?>

  <div class="absolute inset-0 bg-[#002D61]/85" style="top: 95px;"></div>
  <div id="toast"></div>

  <div class="flex-grow flex items-center justify-center relative z-10">
    <div class="w-full sm:w-2/3 sm:max-w-md px-6 py-8 flex flex-col items-center text-white bg-[#8DC9F7]/10 backdrop-blur-md rounded-xl shadow-xl">

      <h2 class="text-3xl font-bold mb-2 text-center"
        style="text-shadow: 1px 1px 0 black,-1px -1px 0 black,1px -1px 0 black,-1px 1px 0 black; color:#bfe5ed;">
        SELF SERVICE
      </h2>

      <p class="text-sm text-white mb-2 text-left opacity-80">
        <?php echo htmlspecialchars($material->getName()); ?>
      </p>
      <p class="text-xs text-white mb-5 text-left opacity-70">
        Location: <?php echo htmlspecialchars($material->getLocation()); ?> | Resource Type: <?php echo htmlspecialchars($material->getResourceType()); ?><br>
        Copy Instock: <?php echo htmlspecialchars($material->getCopyInstock()); ?> | Copy Total: <?php echo htmlspecialchars($material->getCopyCapacity()); ?><br>
        <?php if ($material->getISBN()): ?>ISBN: <?php echo htmlspecialchars($material->getISBN()); ?> <?php endif; ?>
        <?php if ($material->getAuthor()): ?>| Author: <?php echo htmlspecialchars($material->getAuthor()); ?><?php endif; ?><br>
        <?php if ($material->getDescription()): ?>Description: <?php echo htmlspecialchars($material->getDescription()); ?><?php endif; ?>
      </p>

      <!-- TOGGLE -->
      <div class="flex w-full mb-5 rounded-xl overflow-hidden border border-white/20 shadow-md">
        <button type="button" id="tab-checkout" onclick="switchMode('checkout')"
          class="flex-1 py-3 text-sm font-bold tracking-wide transition-all duration-200 bg-[#0d2b8d] text-white">
          📤 Check Out
        </button>
        <button type="button" id="tab-return" onclick="switchMode('return')"
          class="flex-1 py-3 text-sm font-bold tracking-wide transition-all duration-200 bg-white/10 text-white/50">
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
              class="w-full bg-[#0d2b8d] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300 cursor-pointer">
          </div>

          <div id="return-btn" style="display:none;">
            <input type="submit" name="Return" value="Return"
              class="w-full bg-[#9C2007] text-white font-bold py-3 rounded-lg hover:bg-[#7a1905] active:scale-95 transition duration-300 cursor-pointer">
          </div>
        </form>
      </div>

    </div>
  </div>

  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Mellisa Wells
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