<?php
    session_cache_expire(30);
    session_start();
    require_once('include/api.php');
    ini_set("display_errors",1);
    error_reporting(E_ALL);
    $loggedIn = false;
    $accessLevel = 0;
    $userID = null;
    if (isset($_SESSION['_id'])) {
        $loggedIn = true;
        $accessLevel = $_SESSION['access_level'];
        $userID = $_SESSION['_id'];
    }
    $forced = false;
    if (isset($_SESSION['change-password']) && $_SESSION['change-password']) {
        $forced = true;
    } else if (!$loggedIn) {
        header('Location: login.php');
        die();
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_once('include/input-validation.php');
        require_once('domain/Person.php');
        require_once('database/dbPersons.php');
        if ($forced) {
            if (!wereRequiredFieldsSubmitted($_POST, array('new-password'))) { echo "Args missing"; die(); }
            $newPassword = $_POST['new-password'];
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            change_password($userID, $hash);
            if ($userID == 'vmsroot') {
                $_SESSION['access_level'] = 3;
            } else {
                $user = retrieve_person($userID);
                $_SESSION['access_level'] = $user->get_access_level();
            }
            $_SESSION['logged_in'] = true;
            unset($_SESSION['change-password']);
            header('Location: index.php?pcSuccess');
            die();
        } else {
            if (!wereRequiredFieldsSubmitted($_POST, array('password', 'new-password'))) { echo "Args missing"; die(); }
            $password = $_POST['password'];
            $newPassword = $_POST['new-password'];
            $securePassword = isSecurePassword($_POST['new-password']);
            $user = retrieve_person($userID);
            if (!password_verify($password, $user->get_password())) {
                $error1 = true;
            } else if($password == $newPassword) {
                $error2 = true;
            } else if (!$securePassword) {
                $error3 = true;
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                change_password($userID, $hash);
                header('Location: index.php?pcSuccess');
                die();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Change Password</title>
    <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }

    body {
        min-height: 100vh;
        padding-top: 95px;
        color: white;
        background-image: url('images/library.jpg');
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 45, 97, 0.88);
        z-index: -1;
    }

    .page-wrapper {
        width: 100%;
        max-width: 580px;
        margin: 0 auto;
        padding: 40px 24px 80px;
        flex: 1;
    }

    .page-heading {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 6px;
        color: white;
    }

    .page-subheading {
        font-size: 14px;
        color: rgba(255,255,255,0.65);
        margin-bottom: 32px;
    }

    .alert {
        padding: 13px 18px;
        border-radius: 10px;
        margin-bottom: 22px;
        font-size: 14px;
        font-weight: 600;
        width: 100%;
    }
    .alert-error   { background: rgba(180,30,30,0.85); color: white; }
    .alert-success { background: rgba(22,163,74,0.85); color: white; }

    .card {
        background: rgba(141,201,247,0.08);
        border-radius: 14px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.25);
        overflow: hidden;
        width: 100%;
    }

    .form-inner { padding: 32px 40px; }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 18px;
    }

    .form-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #8DC9F7;
    }

    .form-input {
        width: 100%;
        padding: 11px 14px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        background: rgba(141,201,247,0.07);
        border: 1.5px solid rgba(141,201,247,0.2);
        border-radius: 8px;
        color: white;
        outline: none;
        transition: border-color .2s;
    }
    .form-input:focus { border-color: #8DC9F7; }
    .form-input::placeholder { color: rgba(255,255,255,.3); }

    .form-divider {
        border: none;
        border-top: 1px solid rgba(141,201,247,0.15);
        margin: 22px 0;
    }

    .btn-submit {
        width: 100%;
        padding: 15px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        background: #8DC9F7;
        color: #002D61;
        border: none;
        border-radius: 0 0 14px 14px;
        cursor: pointer;
        transition: background .2s;
        display: block;
    }
    .btn-submit:hover { background: #0067A2; color: white; }

    .btn-cancel {
        width: 100%;
        padding: 14px;
        font-size: 15px;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        background: rgba(141,201,247,0.07);
        color: white;
        border: none;
        border-top: 1px solid rgba(141,201,247,0.15);
        border-radius: 0 0 14px 14px;
        cursor: pointer;
        transition: background .2s;
        text-align: center;
        text-decoration: none;
        display: block;
        margin-top: -1px;
    }
    .btn-cancel:hover { background: rgba(141,201,247,0.15); }

    .back-link {
        display: inline-block;
        margin-top: 16px;
        color: #8DC9F7;
        font-size: 14px;
        text-decoration: none;
        font-weight: 600;
    }
    .back-link:hover { text-decoration: underline; color: white; }

    .error-msg { display: none; font-size: 13px; color: #f87171; margin-top: 4px; }
    .error-msg.visible { display: block; }

    @media (max-width: 600px) {
        body { padding-top: 70px; }
        .page-wrapper { padding: 24px 16px 60px; }
        .page-heading { font-size: 22px; }
        .form-inner { padding: 24px 18px; }
    }
    </style>
</head>
<body>
<?php require_once('header.php') ?>
<div class="overlay"></div>

<div class="page-wrapper">
    <h1 class="page-heading">Change Password</h1>
    <p class="page-subheading">Update your account password below.</p>

    <?php if (isset($error1)): ?>
        <div class="alert alert-error">⚠ Your entry for Current Password was incorrect.</div>
    <?php elseif (isset($error2)): ?>
        <div class="alert alert-error">⚠ New password must be different from current password.</div>
    <?php elseif (isset($error3)): ?>
        <div class="alert alert-error">⚠ New password must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number.</div>
    <?php endif; ?>

    <div class="card">
        <form id="password-change" method="post">
            <div class="form-inner">
                <?php if ($forced): ?>
                    <p style="color:rgba(255,255,255,0.7); font-size:14px; margin-bottom:18px;">You must change your password before continuing.</p>
                <?php else: ?>
                    <div class="form-group">
                        <label class="form-label" for="password">Current Password</label>
                        <input class="form-input" type="password" id="password" name="password" placeholder="Enter current password" required>
                    </div>
                    <hr class="form-divider">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="new-password">New Password</label>
                    <input class="form-input" type="password" id="new-password" name="new-password" placeholder="Min 8 characters" required>
                    <p id="password-error" class="error-msg">Password needs at least 8 characters, one number, one uppercase, and one lowercase letter.</p>
                </div>

                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label" for="new-password-reenter">Confirm New Password</label>
                    <input class="form-input" type="password" id="new-password-reenter" placeholder="Re-enter new password" required>
                    <p id="password-match-error" class="error-msg">Passwords must match!</p>
                </div>
            </div>

            <input type="submit" id="submit" name="submit" value="Change Password" class="btn-submit">

        </form>
    </div>

    <a href="staffPage.php" class="back-link">← Back to dashboard</a>
</div>

<script>
document.getElementById('password-change').addEventListener('submit', function(e) {
    const np  = document.getElementById('new-password').value;
    const rep = document.getElementById('new-password-reenter').value;
    const pwErr    = document.getElementById('password-error');
    const matchErr = document.getElementById('password-match-error');
    let valid = true;
    const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(np);
    if (!strong) { pwErr.classList.add('visible'); valid = false; }
    else { pwErr.classList.remove('visible'); }
    if (np !== rep) { matchErr.classList.add('visible'); valid = false; }
    else { matchErr.classList.remove('visible'); }
    if (!valid) e.preventDefault();
});
</script>

<?php require 'footer.php'; ?>
</body>
</html>