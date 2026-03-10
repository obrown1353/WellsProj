<?php
/*
// Comment for assignment - Madi
// Login page for Seacobeck Library system

session_cache_expire(30);
session_start();

ini_set("display_errors",1);
error_reporting(E_ALL);

$badLogin = false;
$archivedAccount = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    require_once('include/input-validation.php');

    $ignoreList = array('password');
    $args = sanitize($_POST, $ignoreList);

    $required = array('username','password');

    if (wereRequiredFieldsSubmitted($args,$required)) {

        require_once('domain/Person.php');
        require_once('database/dbPersons.php');

        $username = strtolower($args['username']);
        $password = $args['password'];

        $user = retrieve_person($username);

        if (!$user) {
            $badLogin = true;
        }

        else if (password_verify($password, $user->get_password())) {

            $_SESSION['logged_in'] = true;
            $_SESSION['access_level'] = $user->get_access_level();
            $_SESSION['f_name'] = $user->get_first_name();
            $_SESSION['l_name'] = $user->get_last_name();
            $_SESSION['type'] = 'admin';
            $_SESSION['_id'] = $user->get_id();

            // Root privileges
            if ($user->get_id() == 'vmsroot') {
                $_SESSION['access_level'] = 3;
                $_SESSION['locked'] = false;
            }

            header('Location: index.php');
            exit();
        }

        else {
            $badLogin = true;
        }
    }
}
  */
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

<body class="min-h-screen flex flex-col justify-between bg-cover bg-center"
style="background-image: url('images/library.jpg');">

<!-- overlay -->
<div class="absolute inset-0 bg-[#0d2b8d]/90"></div>

<!-- main content -->
<div class="relative z-10 w-2/3 max-w-md flex flex-col items-center text-white mx-auto mt-auto mb-auto">

<!-- logo -->
<div class="w-full flex justify-center mb-6">
<img src="images/umw_eagle.png" alt="Logo" class="w-40 mx-auto">
</div>

<h2 class="text-3xl font-bold mb-6 text-center"
style="text-shadow: 1px 1px 0 black,-1px -1px 0 black,1px -1px 0 black,-1px 1px 0 black;">
Welcome
</h2>

<?php 
/*
if ($badLogin) {
echo '<span class="text-white bg-red-700 text-center block p-2 rounded-lg mb-4">
No login with that username and password combination currently exists.
</span>';
}

if ($archivedAccount) {
echo '<span class="text-white bg-red-700 block p-2 rounded-lg mb-4">
This account has either been archived or not yet approved by management.
</span>';
}

if (isset($_GET['registerSuccess'])) {
echo '<span class="text-white text-center bg-green-700 block p-2 rounded-lg mb-4">
Registration Successful! Please login below.
</span>';
}
*/
?>

<button
type="submit"
class="w-full bg-[#7b95e9] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300">
Staff Login
</button>

</form>

<a href="index.php"
class="block w-full text-center bg-[#7b95e9] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300 mt-3">
Continue as Guest
</a>

</div>

<!-- footer -->
<footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
Questions? Contact Dr. Mellisa Wells
<a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">
mwells@umw.edu
</a>
</footer>
</body>
</html>
