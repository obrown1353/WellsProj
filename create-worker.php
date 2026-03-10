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

$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = strtolower(trim($_POST['username'] ?? ''));
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = trim($_POST['email']      ?? '');
    $password   = $_POST['password']        ?? '';
    $confirm    = $_POST['confirm']         ?? '';
    $role       = $_POST['role']            ?? 'worker';

    if (!$username || !$first_name || !$last_name || !$password) {
        $error = 'Username, first name, last name, and password are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (retrieve_person($username)) {
        $error = "Username \"$username\" is already taken.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $type   = ($role === 'admin') ? 'admin' : 'worker';
        $today  = date('Y-m-d');

        $person = new Person(
            $username,   // id
            $today,      // start_date
            $first_name, // first_name
            $last_name,  // last_name
            '',          // street_address
            '',          // city
            '',          // state
            '',          // zip_code
            '',          // phone1
            '0',         // over21
            '',          // phone1type
            '',          // emergency_contact_phone
            '',          // emergency_contact_phone_type
            '',          // birthday
            $email,      // email
            'false',     // email_prefs
            '',          // emergency_contact_first_name
            '',          // contact_num
            '',          // emergency_contact_relation
            '',          // contact_method
            $type,       // type
            'Active',    // status
            '',          // notes
            $hashed,     // password
            '',          // affiliation
            '',          // branch
            0,           // archived
            ''           // emergency_contact_last_name
        );

        if (add_person($person)) {
            $success = true;
        } else {
            $error = 'Could not save to database. The username may already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seacobeck Library | Create Account</title>
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
        background: rgb(40, 40, 43);
        border-radius: 14px;
        padding: 32px;
        border: 1px solid rgba(141,201,247,.2);
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
        font-family: inherit;
        background: rgba(255,255,255,.07);
        border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 8px;
        color: white;
        outline: none;
        transition: border-color .2s;
        box-sizing: border-box;
    }
    .form-input:focus { border-color: #8DC9F7; }
    .form-input::placeholder { color: rgba(255,255,255,.3); }
    .form-input option { background: rgb(40,40,43); }

    .name-row { display: flex; gap: 14px; }
    .name-row .form-group { flex: 1; }

    .role-row { display: flex; gap: 12px; }
    .role-option {
        flex: 1;
        border: 2px solid rgba(255,255,255,.15);
        border-radius: 10px;
        padding: 12px;
        cursor: pointer;
        text-align: center;
        transition: border-color .2s, background .2s;
        user-select: none;
        color: white;
    }
    .role-option input[type="radio"] { display: none; }
    .role-title { font-weight: 700; font-size: 15px; }
    .role-desc  { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 3px; }
    .role-option.selected { border-color: #8DC9F7; background: rgba(141,201,247,.1); }

    .form-divider {
        border: none;
        border-top: 1px solid rgba(255,255,255,.1);
        margin: 22px 0;
    }

    .btn-submit {
        width: 100%;
        padding: 13px;
        font-size: 15px;
        font-family: inherit;
        font-weight: 700;
        background: #7b95e9;
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background .2s, transform .1s;
        margin-top: 4px;
    }
    .btn-submit:hover  { background: #0a1e61; }
    .btn-submit:active { transform: scale(.97); }

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        font-weight: 600;
    }
    .alert-error   { background: rgba(180,30,30,.85); color: white; }
    .alert-success { background: rgba(22,163,74,.85);  color: white; }

    .back-link {
        display: inline-block;
        margin-top: 16px;
        color: #8DC9F7;
        font-size: 14px;
        text-decoration: none;
    }
    .back-link:hover { text-decoration: underline; }
    .req { color: #e87; }
</style>

<div class="page-wrap">
    <div class="page-title">Create Staff Account</div>
    <p class="subtitle">Admin panel &rsaquo; New account</p>

    <?php if ($success): ?>
        <div class="alert alert-success">
            ✓ Account created! The user can now log in at <a href="login.php" style="color:white;text-decoration:underline">login.php</a>.
        </div>
        <a href="create-worker.php" class="back-link">+ Create another account</a><br>
        <a href="index.php" class="back-link">← Back to dashboard</a>

    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="create-worker.php">

                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="req">*</span></label>
                    <input class="form-input" type="text" id="username" name="username"
                           placeholder="e.g. jsmith"
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           required>
                </div>

                <div class="name-row">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name <span class="req">*</span></label>
                        <input class="form-input" type="text" id="first_name" name="first_name"
                               placeholder="Jane"
                               value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name <span class="req">*</span></label>
                        <input class="form-input" type="text" id="last_name" name="last_name"
                               placeholder="Smith"
                               value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email <span style="color:rgba(255,255,255,.4)">(optional)</span></label>
                    <input class="form-input" type="email" id="email" name="email"
                           placeholder="jsmith@umw.edu"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <hr class="form-divider">

                <div class="form-group">
                    <label class="form-label">Role <span class="req">*</span></label>
                    <div class="role-row">
                        <label class="role-option <?php echo (($_POST['role'] ?? 'worker') === 'worker') ? 'selected' : ''; ?>">
                            <input type="radio" name="role" value="worker"
                                   <?php echo (($_POST['role'] ?? 'worker') === 'worker') ? 'checked' : ''; ?>>
                            <div class="role-title">👤 Student Worker</div>
                            <div class="role-desc">Access level 1</div>
                        </label>
                        <label class="role-option <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>">
                            <input type="radio" name="role" value="admin"
                                   <?php echo (($_POST['role'] ?? '') === 'admin') ? 'checked' : ''; ?>>
                            <div class="role-title">🔑 Admin</div>
                            <div class="role-desc">Access level 2</div>
                        </label>
                    </div>
                </div>

                <hr class="form-divider">

                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="req">*</span></label>
                    <input class="form-input" type="password" id="password" name="password"
                           placeholder="Min 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm">Confirm Password <span class="req">*</span></label>
                    <input class="form-input" type="password" id="confirm" name="confirm"
                           placeholder="Re-enter password" required>
                </div>

                <button type="submit" class="btn-submit">Create Account</button>
            </form>
        </div>

        <a href="index.php" class="back-link">← Back to dashboard</a>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.role-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.role-option').forEach(l => l.classList.remove('selected'));
        radio.closest('.role-option').classList.add('selected');
    });
});
</script>

</body>
</html>