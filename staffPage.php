<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    exit();
}

$accessLevel = (int) $_SESSION['access_level'];
?>
<!DOCTYPE html>
<html>
<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    * { font-family: 'Inter', sans-serif; }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      padding-top: 100px;
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

    .columnContainer {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
      padding: 1rem;
      width: 100%;
      max-width: 1100px;
      margin: 0 auto;
      align-content: start;
      align-items: stretch;
    }

    @media (min-width: 640px) {
      .columnContainer { grid-template-columns: repeat(2, 1fr); }
    }

    @media (min-width: 1024px) {
      .columnContainer {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        justify-content: center;
        gap: 1.5rem;
      }
    }

    .column {
      text-align: center;
      padding: 1.5rem;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      min-height: 260px;
    }

  .title {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #8DC9F7;
    min-height: 2.5rem; 
    display: flex;
    align-items: center;
    justify-content: center;
  }

    .button-group {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
    }

    .button {
      width: 100%;
      max-width: 260px;
      padding: 0.75rem 1rem;
      background-color: #0067A2;
      color: white;
      border: 2px solid #8DC9F7;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      text-align: center;
      transition: 0.2s ease;
    }

    .button:hover {
      background-color: #002D61;
      transform: scale(1.03);
    }

    @media (min-width: 768px) {
      .title { font-size: 1.875rem; }
    }

    .footer,
    .footer-section,
    .footer-topic,
    .footer a,
    .footer p,
    .footer span,
    .footer div {
      font-family: Inter, sans-serif !important;
      font-size: 16px !important;
      font-weight: 500 !important;
    }
    .footer-topic { font-size: 18px !important; font-weight: 700 !important; color: white !important; }
  </style>

  <title>Staff Dashboard</title>
</head>

<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="columnContainer">

    <div class="column">
        <div class="title">Manage Checkouts</div>
        <div class="button-group">
            <a href="viewCheckouts.php" class="button">View Checkouts</a>
            <a href="importMaterials.php" class="button">Import Materials</a>
        </div>
    </div>

    <div class="column">
        <div class="title">Manage Inventory</div>
        <div class="button-group">
            <a href="viewMaterials.php" class="button">View Catalog</a>
            <a href="viewLogs.php" class="button">View Logs</a>
        </div>
    </div>

    <?php if ($accessLevel >= 2): ?>
    <div class="column">
        <div class="title">Manage Accounts</div>
        <div class="button-group">
            <a href="view-worker.php" class="button">View Accounts</a>
            <a href="create-worker.php" class="button">Create Account</a>
            <a href="delete-worker.php" class="button">Delete Account</a>
        </div>
    </div>
    <?php endif; ?>

</div>

<?php require 'footer.php'; ?>
</body>
</html>