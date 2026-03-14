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

//accesses database
include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";
$id = 1;
//$id = (int) ($_GET['id'] ?? 0);
$material = fetch_material_by_id($id); 
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
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
}
#toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
#toast.success { background: #002D61; border-left: 5px solid #8DC9F7; }
#toast.error   { background: #7A1905; border-left: 5px solid #f87171; }
.spinner {
  display: inline-block;
  width: 18px; height: 18px;
  border: 3px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  vertical-align: middle;
  margin-right: 8px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
<title>Seacobeck Curriculum Lab | Check Out</title>
</head>
<body class="min-h-screen flex flex-col bg-cover bg-center relative"
  style="background-image: url('images/library.jpg'); padding-top: 95px;">

  <?php require 'header.php'; ?>

  <!-- Blue Overlay -->
  <div class="absolute inset-0 bg-[#002D61]/85" style="top: 95px;"></div>

  <!-- Toast -->
  <div id="toast"></div>

  <!-- Main Content -->
  <div class="flex-grow flex items-center justify-center relative z-10">
    <div class="w-full sm:w-2/3 sm:max-w-md px-6 py-8 flex flex-col items-center text-white bg-[#8DC9F7]/10 backdrop-blur-md rounded-xl shadow-xl">

      <h2 class="text-3xl font-bold mb-2 text-center"
        style="text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black; color: #bfe5ed;">
        SELF SERVICE
      </h2>
      <p class="text-sm text-white mb-4 text-left opacity-80">
        <?php echo ($material->getName()); ?>
      </p>
      <p class="text-xs text-white mb-6 text-left opacity-80">
        Location: <?php echo ($material->getLocation()); ?> | Resource Type: <?php echo ($material->getResourceType()); ?><br>
        Copy Instock: <?php echo ($material->getCopyInstock()); ?> | Copy Total: <?php echo ($material->getCopyCapacity()); ?><br>
        <?php if ($material->getISBN()): ?>
          ISBN: <?php echo ($material->getISBN()); ?>
        <?php endif; ?>
        <?php if ($material->getAuthor()): ?>
          | Author: <?php echo ($material->getAuthor()); ?>
        <?php endif; ?> <br>
        <?php if ($material->getDescription()): ?>
          Description: <?php echo ($material->getDescription()); ?>
        <?php endif; ?>
      </p>

      <div class="w-full space-y-5">
        <form action = "./handle_self_service.php" method="post">
          <input type="text"  name="first_name"   placeholder="First Name" class="input-field" required/> <br>
          <input type="text"  name="last_name"    placeholder="Last Name"  class="input-field" required/> <br>
          <input type="email" name="email"        placeholder="Email"      class="input-field" required/> <br>
          <input type="hidden" name="id" value = <?php echo ($id); ?>/> <br>
        <?php if ($material->canBeCheckedOut()): ?>
          <input type="submit" name="Checkout" value="Checkout"
            class="w-full bg-[#0d2b8d] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300">
        <?php endif; ?>
        <?php if (($material->canBeReturned())): ?>
          <!-- should be updated handle return -->
          <input type="submit" name="Return" value="Return"
            class="w-full bg-[#0d2b8d] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300">
        <?php endif; ?>
        </form>
      </div>

    </div>
  </div>

  <!-- Footer -->
  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Mellisa Wells
    <a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">mwells@umw.edu</a>
  </footer>

















  <script>
    emailjs.init("wyffuz6ZVKFN7dYco");

    function getReturnDate() {
      const date = new Date();
      date.setDate(date.getDate() + 14);
      return date.toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
      });
    }

    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = `show ${type}`;
      setTimeout(() => { toast.className = ''; }, 4500);
    }

    function setLoading(loading) {
      const btn = document.getElementById('submitBtn');
      btn.disabled = loading;
      btn.innerHTML = loading
        ? '<span class="spinner"></span>Sending...'
        : 'Submit';
    }

    function handleCheckout() {
      const name        = document.getElementById('name').value.trim();
      const title       = document.getElementById('materialName').value.trim();
      const email       = document.getElementById('email').value.trim();
      const return_date = getReturnDate();

      if (!name || !title || !email) {
        showToast('⚠️ Please fill in all fields.', 'error');
        return;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showToast('⚠️ Please enter a valid email address.', 'error');
        return;
      }
      setLoading(true);

      emailjs.send("service_ulaa9k9", "template_r1s5j65", {
        name, title, email, return_date
      })
      .then(() => {
        setLoading(false);
        showToast(`✅ Checked out! Confirmation sent to ${email}`, 'success');
        document.getElementById('name').value         = '';
        document.getElementById('materialName').value = '';
        document.getElementById('email').value        = '';
        
      })
      .catch((err) => {
        setLoading(false);
        console.error("EmailJS error:", err);
        showToast('❌ Something went wrong. Please try again.', 'error');
      });
    }

    function handleReturn(){
      const name        = document.getElementById('name').value.trim();
      const title       = document.getElementById('materialName').value.trim();
      const email       = document.getElementById('email').value.trim();
      const return_date = getReturnDate();

      if (!name || !title || !email) {
        showToast('⚠️ Please fill in all fields.', 'error');
        return;
      }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showToast('⚠️ Please enter a valid email address.', 'error');
        return;
      }

      setLoading(true);

      emailjs.send("service_ulaa9k9", "template_r1s5j65", {
        name, title, email, return_date
      })
      .then(() => {
        setLoading(false);
        showToast(`✅ Returned! Confirmation sent to ${email}`, 'success');
        document.getElementById('name').value         = '';
        document.getElementById('materialName').value = '';
        document.getElementById('email').value        = '';
      })
      .catch((err) => {
        setLoading(false);
        console.error("EmailJS error:", err);
        showToast('❌ Something went wrong. Please try again.', 'error');
      });
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Enter') handleCheckout();
    });
  </script>

</body>
</html>