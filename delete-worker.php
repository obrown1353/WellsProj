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

$success      = false;
$error        = '';
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | Delete Account</title>
</head>
<body>
<?php require 'header.php'; ?>

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
}

.overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 45, 97, 0.88);
    z-index: -1;
}

.page-wrapper {
    max-width: 580px;
    margin: 0 auto;
    padding: 40px 24px 80px;
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
}
.alert-error   { background: rgba(180,30,30,0.85); color: white; }
.alert-success { background: rgba(22,163,74,0.85);  color: white; }

/* Card — exact same as table-wrapper from view-worker */
.card {
    background: rgba(141,201,247,0.08);
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.25);
    overflow: hidden;
}

.form-inner {
    padding: 32px;
}

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
    box-sizing: border-box;
}
.form-input:focus { border-color: #8DC9F7; }
.form-input::placeholder { color: rgba(255,255,255,.3); }

.form-divider {
    border: none;
    border-top: 1px solid rgba(141,201,247,0.15);
    margin: 22px 0;
}

/* Search button — same as thead bg */
.btn-search {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    background: #8DC9F7;
    color: #002D61;
    border: none;
    border-radius: 0;
    cursor: pointer;
    transition: background .2s;
    display: block;
    margin-top: 0;
}
.btn-search:hover { background: #0067A2; color: white; }

/* Delete button */
.btn-delete {
    width: 100%;
    padding: 14px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    background: rgba(185,28,28,0.85);
    color: white;
    border: none;
    border-radius: 0;
    cursor: pointer;
    transition: background .2s;
    display: block;
}
.btn-delete:hover { background: #dc2626; }

/* Found user preview — styled like a tbody row */
.user-card {
    background: rgba(141,201,247,0.1);
    border-bottom: 1px solid rgba(141,201,247,0.15);
    padding: 16px 32px;
}
.user-card .uname { font-size: 17px; font-weight: 700; color: white; }
.user-card .uid   { font-size: 13px; color: #8DC9F7; margin-top: 3px; }
.user-card .role  {
    display: inline-block;
    margin-top: 8px;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.role-admin  { background: rgba(251,191,36,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }
.role-worker { background: rgba(141,201,247,.12); color: #8DC9F7; border: 1px solid rgba(141,201,247,.25); }

/* Warning */
.warn-notice {
    background: rgba(185,28,28,.15);
    border-bottom: 1px solid rgba(141,201,247,0.1);
    padding: 12px 32px;
    font-size: 13px;
    color: #fca5a5;
    font-weight: 600;
}

.back-link {
    display: inline-block;
    margin-top: 16px;
    color: #8DC9F7;
    font-size: 14px;
    text-decoration: none;
    font-weight: 600;
}
.back-link:hover { text-decoration: underline; color: white; }

/* Mobile */
@media (max-width: 600px) {
    body { padding-top: 70px; }
    .page-wrapper { padding: 24px 16px 60px; }
    .page-heading { font-size: 22px; }
    .form-inner { padding: 24px 18px; }
    .user-card { padding: 16px 18px; }
    .warn-notice { padding: 12px 18px; }
}
</style>

<div class="overlay"></div>

<div class="page-wrapper">
    <h1 class="page-heading">Delete Account</h1>
    <p class="page-subheading">Admin panel &rsaquo; Delete account</p>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ Account for <b><?php echo htmlspecialchars($deleted_name); ?></b> has been deleted.</div>
        <a href="delete-worker.php" class="back-link" style="display:block">Delete another account</a>
        <a href="staffPage.php" class="back-link">← Back to dashboard</a>

    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($search_error): ?>
            <div class="alert alert-error">⚠ <?php echo htmlspecialchars($search_error); ?></div>
        <?php endif; ?>

        <!-- Step 1: Search -->
        <div class="card">
            <form method="POST" action="delete-worker.php">
                <div class="form-inner">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label" for="username">Search by Username</label>
                        <input class="form-input" type="text" id="username" name="username"
                               placeholder="e.g. jsmith"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required>
                    </div>
                </div>
                <button type="submit" name="search" class="btn-search">Search</button>
            </form>
        </div>

        <?php if ($search_result): ?>
            <!-- Step 2: Confirm delete -->
            <div class="card" style="margin-top:20px">
                <div class="user-card">
                    <div class="uname"><?php echo htmlspecialchars($search_result->get_first_name() . ' ' . $search_result->get_last_name()); ?></div>
                    <div class="uid">@<?php echo htmlspecialchars($search_result->get_id()); ?></div>
                    <span class="role <?php echo $search_result->get_type() === 'admin' ? 'role-admin' : 'role-worker'; ?>">
                        <?php echo $search_result->get_type() === 'admin' ? '🔑 Admin' : '👤 Worker'; ?>
                    </span>
                </div>
                <div class="warn-notice">⚠ This action is permanent and cannot be undone.</div>
                <form method="POST" action="delete-worker.php">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($search_result->get_id()); ?>">
                    <button type="submit" name="confirm_delete" class="btn-delete">Delete Account</button>
                </form>
            </div>
        <?php endif; ?>

        <a href="staffPage.php" class="back-link">← Back to dashboard</a>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
</body>
</html>