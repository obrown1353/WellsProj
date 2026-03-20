<?php
session_cache_expire(30);
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

$badLogin = false;

// ── Hardcoded test users ───────────────────────────────────────────────────
$hardcodedUsers = [
    'admin' => [
        'password'     => password_hash('admin123', PASSWORD_DEFAULT),
        'access_level' => 2,
        'first_name'   => 'Admin',
        'last_name'    => 'User',
    ],
    'worker' => [
        'password'     => password_hash('worker123', PASSWORD_DEFAULT),
        'access_level' => 1,
        'first_name'   => 'Worker',
        'last_name'    => 'User',
    ],
];

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

    // 1. Check hardcoded users first
    $user = $hardcodedUsers[$username] ?? null;

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['logged_in']    = true;
            $_SESSION['access_level'] = $user['access_level'];
            $_SESSION['_id']          = $username;
            $_SESSION['f_name']       = $user['first_name'];
            $_SESSION['l_name']       = $user['last_name'];
            header('Location: index.php');
            exit();
        } else {
            $badLogin = true;
        }
    } else {
        // 2. Fall back to database
        $dbUserFile = __DIR__ . '/database/dbPersons.php';
        $domainFile = __DIR__ . '/domain/Person.php';
        if (file_exists($dbUserFile) && file_exists($domainFile)) {
            require_once($dbUserFile);
            require_once($domainFile);
            $dbUser = retrieve_person($username);
            if ($dbUser && password_verify($password, $dbUser->get_password())) {
                $_SESSION['logged_in'] = true;
                $_SESSION['_id']       = $dbUser->get_id();
                $_SESSION['f_name']    = $dbUser->get_first_name();
                $_SESSION['l_name']    = $dbUser->get_last_name();

                // vmsroot always gets full admin access
                if ($dbUser->get_id() === 'vmsroot') {
                    $_SESSION['access_level'] = 2;
                } else {
                    $_SESSION['access_level'] = $dbUser->get_access_level();
                }

                header('Location: index.php');
                exit();
            }
        }
        $badLogin = true;
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
            font-family: 'Quicksand', sans-serif;
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
            font-family: 'Quicksand', sans-serif;
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

        .error-msg {
            background: rgba(180,30,30,0.85);
            color: white;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
        }

        .btn-primary {
            width: 100%;
            padding: 13px;
            font-size: 1rem;
            font-family: 'Quicksand', sans-serif;
            font-weight: 700;
            background: #8DC9F7;
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
            font-family: 'Quicksand', sans-serif;
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

    <div class="main-content">
        <img src="images/umw_eagle.png" alt="UMW Logo" class="logo">
        <h2>Welcome</h2>

        <form class="login-form" action="login.php" method="POST">
            <?php if ($badLogin): ?>
                <div class="error-msg">Incorrect username or password. Please try again.</div>
            <?php endif; ?>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       placeholder="Enter your username" required>
            </div>
            <!-- logo -->
            <div class="w-full flex justify-center mb-6">
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="Enter your password" required>
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
</body>
</html>
