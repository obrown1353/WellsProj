<?php
session_cache_expire(30);
session_start();
// Clear and destroy session
session_unset();
session_destroy();
session_write_close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3; url=index.php">
    <title>Seacobeck Curriculum Lab | Logging Out</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
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
            background: rgba(0, 45, 97, 0.88);
            z-index: 0;
        }

        .main-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 420px;
            margin: auto;
            padding: 40px 20px;
            color: white;
            text-align: center;
        }

        .logo {
            width: 160px;
            margin-bottom: 32px;
            border-radius: 8px;
        }

        .goodbye-heading {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 16px;
            text-shadow: 1px 1px 0 black, -1px -1px 0 black,
                         1px -1px 0 black, -1px  1px 0 black;
        }

        .goodbye-sub {
            font-size: 1rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 36px;
            line-height: 1.5;
        }

        /* Spinner */
        .spinner-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            margin-bottom: 36px;
        }
        .spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(141,201,247,0.25);
            border-top-color: #8DC9F7;
            border-radius: 50%;
            animation: spin 0.9s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .redirect-text {
            font-size: 0.9rem;
            color: #8DC9F7;
            font-weight: 600;
        }

        .btn-login {
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
            text-decoration: none;
            display: inline-block;
            transition: background 0.25s, transform 0.1s;
        }
        .btn-login:hover  { background: #8DC9F7; color: #002D61; }
        .btn-login:active { transform: scale(0.97); }

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

        /* Mobile */
        @media (max-width: 480px) {
            .goodbye-heading { font-size: 2.2rem; }
            .goodbye-sub     { font-size: 1.1rem; }
            .redirect-text   { font-size: 1rem; }
            .btn-login       { font-size: 1.1rem; padding: 15px; }
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="main-content">
        <img src="images/umw_eagle.png" alt="UMW Logo" class="logo">

        <h2 class="goodbye-heading">You've been logged out</h2>
        <p class="goodbye-sub">Thank you for using the Seacobeck Curriculum Lab.<br>See you next time!</p>

        <div class="spinner-wrap">
            <div class="spinner"></div>
            <span class="redirect-text">Redirecting to login…</span>
        </div>

        <a href="index.php" class="btn-login">Return to Login</a>
    </div>

    <footer>
        Questions? Contact Dr. Melissa Wells &mdash;
        <a href="mailto:mwells@umw.edu">mwells@umw.edu</a>
    </footer>
</body>
</html>