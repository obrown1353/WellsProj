<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

$badLogin = false;
$errorMsg = '';

// ── Guest login ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guest'])) {
    $_SESSION['access_level'] = 0;
    $_SESSION['_id']          = 'guest';
    $_SESSION['f_name']       = 'Guest';
    $_SESSION['l_name']       = '';
    header('Location: index.php');
    exit();
}

// ── Staff login ────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'] ?? '';

    require_once(__DIR__ . '/database/dbPersons.php');
    require_once(__DIR__ . '/domain/Person.php');

    $dbUser = retrieve_person($username);

    if ($dbUser && password_verify($password, $dbUser->get_password())) {
        $_SESSION['logged_in'] = true;
        $_SESSION['_id']       = $dbUser->get_id();
        $_SESSION['f_name']    = $dbUser->get_first_name();
        $_SESSION['l_name']    = $dbUser->get_last_name();

        // FIX: Person::get_access_level() always returns 1 for non-vmsroot,
        // so we map from type directly instead.
        if ($dbUser->get_id() === 'vmsroot') {
            $_SESSION['access_level'] = 2;
        } elseif ($dbUser->get_type() === 'admin') {
            $_SESSION['access_level'] = 2;
        } else {
            $_SESSION['access_level'] = 1;
        }

        header('Location: index.php');
        exit();
    } else {
        $badLogin = true;
        $errorMsg = $dbUser
            ? 'Incorrect password. Please try again.'
            : 'No account found with that username.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seacobeck Curriculum Lab | Log In</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
            z-index: 0;
        }

        .main-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            max-width: 420px;
            margin: auto;
            padding: 40px 20px;
            color: white;
        }

        .logo {
            width: 160px;
            margin-bottom: 24px;
            border-radius: 8px;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 28px;
            text-align: center;
            text-shadow: 1px 1px 0 black, -1px -1px 0 black,
                         1px -1px 0 black, -1px  1px 0 black;
        }

        .login-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #8DC9F7;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            background: rgba(255,255,255,0.12);
            color: white;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        input::placeholder { color: rgba(255,255,255,0.45); }
        input:focus {
            border-color: #8DC9F7;
            background: rgba(255,255,255,0.18);
        }
        input.input-error {
            border-color: #f87171 !important;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%   { transform: translateX(0); }
            20%  { transform: translateX(-7px); }
            40%  { transform: translateX(7px); }
            60%  { transform: translateX(-5px); }
            80%  { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }

        /* Toast */
        .toast {
            position: fixed;
            top: 28px;
            left: 50%;
            transform: translateX(-50%) translateY(-120px);
            background: rgba(180, 30, 30, 0.95);
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast-icon { font-size: 1.2rem; }
        .toast-close {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.6);
            font-size: 1.2rem;
            cursor: pointer;
            margin-left: 8px;
            line-height: 1;
            transition: color 0.2s;
        }
        .toast-close:hover { color: white; }

        .btn-primary {
            width: 100%;
            padding: 13px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            background: #0067A2;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.25s, transform 0.1s;
            margin-top: 4px;
        }
        .btn-primary:hover  { background: #0a1e61; }
        .btn-primary:active { transform: scale(0.97); }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 12px 0;
            color: rgba(255,255,255,0.4);
            font-size: 0.85rem;
            font-weight: 600;
            width: 100%;
        }
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.2);
        }

        .guest-form { width: 100%; }
        .btn-guest {
            width: 100%;
            padding: 13px;
            font-size: 1rem;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            background: transparent;
            color: #8DC9F7;
            border: 2px solid #8DC9F7;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.25s, transform 0.1s;
        }
        .btn-guest:hover  { background: rgba(141,201,247,0.15); }
        .btn-guest:active { transform: scale(0.97); }

        footer {
            position: relative;
            z-index: 10;
            width: 100%;
            text-align: center;
            color: white;
            background: rgba(0,0,0,0.5);
            padding: 16px;
            font-size: 0.9rem;
        }
        footer a { color: #8DC9F7; text-decoration: underline; }
        footer a:hover { color: #c8e8ff; }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <!-- Toast -->
    <div class="toast" id="errorToast">
        <span class="toast-icon">⚠</span>
        <span id="toastMsg"><?php echo htmlspecialchars($errorMsg); ?></span>
        <button class="toast-close" onclick="hideToast()" aria-label="Dismiss">&times;</button>
    </div>

    <div class="main-content">
        <img src="images/umw_eagle.png" alt="UMW Logo" class="logo">
        <h2>Welcome</h2>

        <form class="login-form" action="login.php" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       class="<?php echo $badLogin ? 'input-error' : ''; ?>"
                       required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password"
                       class="<?php echo $badLogin ? 'input-error' : ''; ?>"
                       required>
            </div>

            <button type="submit" class="btn-primary">Staff Login</button>
        </form>

        <div class="divider">or</div>

        <form class="guest-form" action="login.php" method="POST">
            <button type="submit" name="guest" value="1" class="btn-guest">
                Continue as Guest
            </button>
        </form>
    </div>

    <footer>
        Questions? Contact Dr. Melissa Wells &mdash;
        <a href="mailto:mwells@umw.edu">mwells@umw.edu</a>
    </footer>

    <script>
    <?php if ($badLogin): ?>
    window.addEventListener('DOMContentLoaded', function () {
        const toast = document.getElementById('errorToast');
        setTimeout(() => toast.classList.add('show'), 80);
        setTimeout(() => hideToast(), 4000);
    });
    <?php endif; ?>

    function hideToast() {
        document.getElementById('errorToast').classList.remove('show');
    }

    document.querySelectorAll('.input-error').forEach(function(el) {
        el.addEventListener('input', function() {
            this.classList.remove('input-error');
        });
    });
    </script>
</body>
</html>
