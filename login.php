<?php
    // Comment for assignment -Madi
    // Template for new VMS pages. Base your new page on this one

    // Make session information accessible, allowing us to associate
    // data with the logged-in user.
    session_cache_expire(30);
    session_start();
    
    ini_set("display_errors",1);
    error_reporting(E_ALL);

    // redirect to index if already logged in
    if (isset($_SESSION['_id'])) {
        header('Location: index.php');
        die();
    }
    $badLogin = false;
    $archivedAccount = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once('include/input-validation.php');
        $ignoreList = array('password');
        $args = sanitize($_POST, $ignoreList);
        $required = array('username', 'password');
        if (wereRequiredFieldsSubmitted($args, $required)) {
            require_once('domain/Person.php');
            require_once('database/dbPersons.php');
            /*@require_once('database/dbMessages.php');*/
            /*@dateChecker();*/
            $username = strtolower($args['username']);
            $password = $args['password'];
            $user = retrieve_person($username);
            if (!$user) {
                $badLogin = true;
            } /*else if ($user->get_status() === "Inactive") {
                // If the user is archived, block login
                $archivedAccount = true;
            }*/ else if (password_verify($password, $user->get_password())) {
                $_SESSION['logged_in'] = true;

                $_SESSION['access_level'] = $user->get_access_level();
                $_SESSION['f_name'] = $user->get_first_name();
                $_SESSION['l_name'] = $user->get_last_name();

                
                $_SESSION['type'] = 'admin';
                $_SESSION['_id'] = $user->get_id();
                
                 //hard code root privileges
                 if ($user->get_id() == 'vmsroot') {
                    $_SESSION['access_level'] = 3;
		    $_SESSION['locked'] = false;
                    header('Location: index.php');
               }
            
                //if ($changePassword) {
                //    $_SESSION['access_level'] = 0;
                //    $_SESSION['change-password'] = true;
                //    header('Location: changePassword.php');
                //    die();
                //} 
                else {
                    header('Location: index.php');
                    die();
                }
                die();
            } else {
                $badLogin = true;
            }
        }
    }
    //<p>Or <a href="register.php">register as a new volunteer</a>!</p>
    //Had this line under login button, took user to register page
?>
<!DOCTYPE html>
<html>
    <head>
	<script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/login.css">
    	<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
	<style>

* { font-family: StromaBold, 'Lucida Sans'; }
	</style>
        <title>Seacobeck Library | Log In</title>
    </head>
    <body>
<div class="h-screen flex">

  <!-- Left: Image Section (Hidden on small screens) -->
  <div class="hidden md:block md:w-1/2 bg-center rounded-r-[50px] bg-[#1F1F21]">
      <img src="images/bookstock.jpg"
            alt="Books"
            style="height: 100%;">
  </div>

  <!-- Main Content (Centered) -->
  <div class="relative z-10 w-2/3 max-w-md flex flex-col items-center text-white mx-auto mt-auto mb-auto">

    <!-- Logo -->
    <div class="w-full flex justify-center mb-6">
      <img src="images/umw.jpg" alt="Logo" class="w-40 mx-auto">
    </div>

    <h2 class="text-3xl font-bold mb-6 text-center" 
      style="text-shadow: 2px 2px 0 black, -1px -1px 0 black, 2px -1px 0 black, -1px 1px 0 black;">
      Welcome
    </h2>

    <!-- Buttons -->
    <div class="w-full flex flex-col items-center gap-4">
      <button class="w-full bg-[#8d0e0e] text-white font-bold py-3 rounded-lg hover:bg-blue-600 transition duration-300">
        Staff Login
      </button>
      <button class="w-full bg-[#8d0e0e] text-white font-bold py-3 rounded-lg hover:bg-blue-600 transition duration-300">
        Continue as Guest
      </button>
    </div>

  </div>

  <!-- Footer -->
  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Mellisa Wells <a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">mwells@umw.edu</a>
  </footer>

</body>

 

<!-- What used to be used to verify username and password, not needed right now but may be useful for staff login-->
                <?php
                    if ($badLogin) {
                        echo '<span class="text-white bg-red-700 text-center block p-2 rounded-lg mb-2">No login with that username and password combination currently exists.</span>';
                    }
                    if ($archivedAccount) {
                        echo '<span class="text-white bg-red-700 block p-2 rounded-lg mb-2">This account has either been archived or not yet approved by managment. For help, notify <a href="mailto:volunteer@fredspca.org">volunteer@fredspca.org</a>.</span>';
                    }
		    if (isset($_GET['registerSuccess'])) {
                        echo '<span class="text-white text-center bg-green-700 block p-2 rounded-lg mb-2">Registration Successful! Please login below.</span>';
		    } 
                ?>


  
</html>
