<?php
session_cache_expire(30);
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Admins only
if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] < 2) {
    header('Location: index.php');
    die();
}

require_once('database/dbPersons.php');
require_once('domain/Person.php');
include_once "database/dbLogs.php";

$success = false;
$error   = '';
$deleted_name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $username = strtolower(trim($_POST['username'] ?? ''));

    if (!$username) {
        $error = 'No username provided.';
    } elseif ($username === 'vmsroot') {
        $error = 'That account cannot be deleted.';
    } else {
        $person = retrieve_person($username);
        if (!$person) {
            $error = "No account found with username \"$username\".";
        } else {
            $deleted_name = $person->get_first_name() . ' ' . $person->get_last_name();
            if (remove_person($username)) {
                $success = true;
                $log = new Log(
                    null, 
                    $log_type = "system", 
                    $message = $username . " has been been removed from staff", 
                    $log_time = date('Y-m-d H:i:s')
                );
                new_log($log);
            } else {
                $error = 'Could not delete the account. Please try again.';
            }
        }
    }
}

// Search for a user before confirming delete
$search_result = null;
$search_error  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $q = strtolower(trim($_POST['username'] ?? ''));
    if ($q) {
        $found = retrieve_person($q);
        if ($found && $found->get_id() !== 'vmsroot') {
            $search_result = $found;
        } else {
            $search_error = "No account found with username \"$q\".";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seacobeck Library | Delete Account</title>
</head>
<body>
<?php require 'header.php'; ?>
<style>
    .page-wrap {
        max-width: 560px;
        margin: 40px auto;
        padding: 0 20px 60px;
        color: white;
    }
    .page-title { margin-bottom: 6px; font-size: 28px; font-weight: 700; color: white; }
    .subtitle { color: #8DC9F7; font-size: 14px; margin-bottom: 30px; }
    .card {
        background: rgb(40,40,43);
        border-radius: 14px;
        padding: 32px;
        border: 1px solid rgba(141,201,247,.2);
    }
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
    .form-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8DC9F7; }
    .form-input {
        width: 100%; padding: 11px 14px; font-size: 15px; font-family: inherit;
        background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 8px; color: white; outline: none; transition: border-color .2s;
        box-sizing: border-box;
    }
    .form-input:focus { border-color: #8DC9F7; }
    .form-input::placeholder { color: rgba(255,255,255,.3); }
    .btn { width: 100%; padding: 13px; font-size: 15px; font-family: inherit; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: background .2s, transform .1s; margin-top: 4px; }
    .btn:active { transform: scale(.97); }
    .btn-search { background: #7b95e9; color: white; }
    .btn-search:hover { background: #0a1e61; }
    .btn-delete { background: #b91c1c; color: white; }
    .btn-delete:hover { background: #7f1d1d; }
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
    .alert-error   { background: rgba(180,30,30,.85); color: white; }
    .alert-success { background: rgba(22,163,74,.85); color: white; }
    .alert-warn    { background: rgba(180,100,0,.85); color: white; }
    .user-card {
        background: rgba(141,201,247,.1);
        border: 1px solid rgba(141,201,247,.3);
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }
    .user-card .name  { font-size: 18px; font-weight: 700; color: white; }
    .user-card .uname { font-size: 13px; color: #8DC9F7; margin-top: 2px; }
    .user-card .role  { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 4px; text-transform: uppercase; letter-spacing: .05em; }
    .form-divider { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 20px 0; }
    .back-link { display: inline-block; margin-top: 16px; color: #8DC9F7; font-size: 14px; text-decoration: none; }
    .back-link:hover { text-decoration: underline; }
</style>

<div class="page-wrap">
    <div class="page-title">Delete Account</div>
    <p class="subtitle">Admin panel &rsaquo; Delete account</p>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ Account for <b><?php echo htmlspecialchars($deleted_name); ?></b> has been deleted.</div>
        <a href="delete-worker.php" class="back-link">Delete another account</a><br>
        <a href="index.php" class="back-link">← Back to dashboard</a>

    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($search_error): ?>
            <div class="alert alert-error">⚠ <?php echo htmlspecialchars($search_error); ?></div>
        <?php endif; ?>

        <div class="card">

            <!-- Step 1: Search -->
            <form method="POST" action="delete-worker.php">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-input" type="text" id="username" name="username"
                           placeholder="e.g. jsmith"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required>
                </div>
                <button type="submit" name="search" value="1" class="btn btn-search">Look Up Account</button>
            </form>

            <!-- Step 2: Confirm delete if found -->
            <?php if ($search_result): ?>
                <hr class="form-divider">
                <div class="user-card">
                    <div class="name"><?php echo htmlspecialchars($search_result->get_first_name() . ' ' . $search_result->get_last_name()); ?></div>
                    <div class="uname">@<?php echo htmlspecialchars($search_result->get_id()); ?></div>
                    <div class="role"><?php echo htmlspecialchars($search_result->get_type()); ?></div>
                </div>
                <div class="alert alert-warn">⚠ This will permanently delete the account. This cannot be undone.</div>
                <form method="POST" action="delete-worker.php">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($search_result->get_id()); ?>">
                    <button type="submit" name="confirm_delete" value="1" class="btn btn-delete">Delete This Account</button>
                </form>
            <?php endif; ?>

        </div>

        <a href="index.php" class="back-link">← Back to dashboard</a>
    <?php endif; ?>
</div>
</body>
</html>