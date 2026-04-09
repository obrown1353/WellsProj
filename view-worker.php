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

$error        = '';
$edit_success = '';

// Handle inline edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_worker'])) {
    $username     = strtolower(trim($_POST['username']     ?? ''));
    $new_username = strtolower(trim($_POST['new_username'] ?? ''));
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name']  ?? '');
    $email        = trim($_POST['email']      ?? '');

    if (!$username || !$first_name || !$last_name || !$new_username) {
        $error = 'First name, last name, and username are required.';
    } elseif ($new_username === 'vmsroot') {
        $error = 'That username is reserved.';
    } elseif ($new_username !== $username && retrieve_person($new_username)) {
        $error = "Username \"" . htmlspecialchars($new_username) . "\" is already taken.";
    } else {
        $person = retrieve_person($username);
        if (!$person) {
            $error = 'Account not found.';
        } else {
            $current_type = $person->get_type();
            $result = update_person_required(
                $username, $first_name, $last_name,
                $person->get_city(), $person->get_state(), $email,
                $person->get_phone1(), $person->get_email_prefs(),
                $person->get_affiliation(), $person->get_branch()
            );
            if ($result) { update_type($username, $current_type); }
            if ($result && $new_username !== $username) {
                $con = connect();
                $safe_new = mysqli_real_escape_string($con, $new_username);
                $safe_old = mysqli_real_escape_string($con, $username);
                $q = "UPDATE dbpersons SET id='$safe_new' WHERE id='$safe_old'";
                $result = mysqli_query($con, $q);
                mysqli_close($con);
                if ($result && isset($_SESSION['id']) && $_SESSION['id'] === $username) {
                    $_SESSION['id'] = $new_username;
                }
            }
            if ($result) {
                $edit_success = htmlspecialchars($first_name . ' ' . $last_name);
            } else {
                $error = 'Could not update the account. Please try again.';
            }
        }
    }
}

$workers = [];
$all_persons = getall_persons();
if ($all_persons) {
    foreach ($all_persons as $p) {
        if ($p->get_id() === 'vmsroot') continue;
        if (!in_array($p->get_type(), ['admin', 'worker'])) continue;
        $workers[] = $p;
    }
}
usort($workers, fn($a, $b) => strcasecmp($a->get_last_name(), $b->get_last_name()));

$total_count  = count($workers);
$admin_count  = count(array_filter($workers, fn($p) => $p->get_type() === 'admin'));
$worker_count = $total_count - $admin_count;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>Seacobeck Curriculum Lab | View Accounts</title>
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
            position: fixed;
            inset: 0;
            background: rgba(0, 45, 97, 0.88);
            z-index: -1;
        }

        .page-wrapper {
            max-width: 1100px;
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

        /* Alert banners */
        .alert {
            padding: 13px 18px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error   { background: rgba(180,30,30,0.85); color: white; }
        .alert-success { background: rgba(22,163,74,0.85);  color: white; }

        /* Summary pills */
        .summary-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .stat-pill {
            background: rgba(141,201,247,0.12);
            border: 1.5px solid rgba(141,201,247,0.35);
            border-radius: 50px;
            padding: 7px 18px;
            font-size: 13px;
            font-weight: 700;
            color: #8DC9F7;
        }
        .stat-pill span { color: white; margin-left: 5px; }

        /* Search & filter row */
        .controls-row {
            display: flex;
            gap: 10px;
            margin-bottom: 22px;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-wrapper {
            display: flex;
            justify-content: center;
            flex: 1;
            min-width: 200px;
        }
        .search-box {
            width: 100%;
            max-width: 700px;
            border: 3px solid #0067A2;
            border-radius: 16px;
            padding: 14px 20px;
            background-color: #8DC9F7;
            display: flex;
        }
        .search-inner {
            position: relative;
            width: 100%;
        }
        .search-input {
            width: 100%;
            padding: 10px 120px 10px 16px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 20px;
            outline: none;
            color: #0067A2;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
        }
        .search-input::placeholder { color: #5aa5d4; }
        .filter-select {
            padding: 10px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            color: white;
            outline: none;
            cursor: pointer;
            height: fit-content;
            align-self: center;
        }
        .filter-select option { background: #002D61; }

        /* Section heading */
        .section-heading {
            font-size: 20px;
            font-weight: 700;
            color: #8DC9F7;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-heading .badge {
            background: #8DC9F7;
            color: #002D61;
            font-size: 13px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 20px;
        }

        /* Table */
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
            margin-bottom: 48px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.25);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(141,201,247,0.08);
            font-size: 14px;
            min-width: 540px;
        }
        thead tr {
            background: #8DC9F7;
            color: #002D61;
        }
        thead th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }
        tbody tr {
            border-bottom: 1px solid rgba(141,201,247,0.15);
            transition: background 0.15s;
        }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: rgba(141,201,247,0.1); }
        tbody td {
            padding: 13px 16px;
            color: rgba(255,255,255,0.88);
            vertical-align: middle;
        }
        .td-username { color: #8DC9F7; font-weight: 700; font-size: 13px; }
        .td-email    { color: rgba(255,255,255,0.55); font-size: 13px; }

        .badge-role { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .badge-admin  { background: rgba(251,191,36,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }
        .badge-worker { background: rgba(141,201,247,.12); color: #8DC9F7; border: 1px solid rgba(141,201,247,.25); }

        .btn-edit {
            background: transparent;
            border: 1.5px solid rgba(141,201,247,.4);
            color: #8DC9F7;
            font-size: 12px;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .2s, border-color .2s;
            white-space: nowrap;
        }
        .btn-edit:hover { background: rgba(141,201,247,.15); border-color: #8DC9F7; }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: rgba(255,255,255,0.35);
            font-size: 15px;
        }
        .empty-state svg { width: 44px; height: 44px; margin-bottom: 10px; opacity: 0.3; display: block; margin-left: auto; margin-right: auto; }

        .no-results-row { display: none; }
        .no-results-row td { text-align: center; padding: 40px; color: rgba(255,255,255,.35); }

        /* Modal */
        .modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.65);
            z-index: 3000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-backdrop.open { display: flex; }
        .modal {
            background: #001f45;
            border-radius: 16px;
            border: 2px solid rgba(141,201,247,.3);
            padding: 32px;
            width: 100%;
            max-width: 480px;
            position: relative;
            animation: modalIn .2s ease;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-12px) scale(.97); }
            to   { opacity: 1; transform: translateY(0)    scale(1);    }
        }
        .modal-title { font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px; }
        .modal-sub   { font-size: 13px; color: #8DC9F7; margin-bottom: 24px; }
        .modal-close-btn {
            position: absolute; top: 18px; right: 20px;
            background: transparent; border: none;
            color: rgba(255,255,255,.4); font-size: 24px;
            cursor: pointer; line-height: 1; transition: color .2s;
        }
        .modal-close-btn:hover { color: white; }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
        .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #8DC9F7; }
        .form-input {
            width: 100%; padding: 11px 14px; font-size: 15px; font-family: 'Inter', sans-serif;
            background: rgba(141,201,247,.07); border: 1.5px solid rgba(141,201,247,.2);
            border-radius: 8px; color: white; outline: none; transition: border-color .2s; box-sizing: border-box;
        }
        .form-input:focus { border-color: #8DC9F7; }
        .form-input::placeholder { color: rgba(255,255,255,.3); }
        .name-row { display: flex; gap: 12px; }
        .name-row .form-group { flex: 1; }
        .form-divider { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 18px 0; }
        .modal-actions { display: flex; gap: 10px; margin-top: 6px; }
        .btn-save {
            flex: 1; padding: 12px; font-size: 14px; font-family: 'Inter', sans-serif; font-weight: 700;
            background: #0067A2; color: white; border: none; border-radius: 10px;
            cursor: pointer; transition: background .2s, transform .1s;
        }
        .btn-save:hover  { background: #8DC9F7; color: #002D61; }
        .btn-save:active { transform: scale(.97); }
        .btn-cancel {
            padding: 12px 20px; font-size: 14px; font-family: 'Inter', sans-serif; font-weight: 700;
            background: transparent; color: rgba(255,255,255,.5);
            border: 1.5px solid rgba(255,255,255,.15); border-radius: 10px;
            cursor: pointer; transition: background .2s, color .2s;
        }
        .btn-cancel:hover { background: rgba(255,255,255,.07); color: white; }

        .back-link { display: inline-block; margin-top: 16px; color: #8DC9F7; font-size: 14px; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; color: white; }

        /* Mobile */
        @media (max-width: 600px) {
            body { padding-top: 70px; }
            .page-wrapper { padding: 24px 16px 60px; }
            .page-heading { font-size: 22px; }
            .controls-row { flex-direction: column; }
            .search-wrapper { width: 100%; }
            .filter-select { width: 100%; }
            .name-row { flex-direction: column; gap: 0; }
            .modal { padding: 24px 20px; }
        }
    </style>
</head>
<body>
<?php require 'header.php'; ?>
<div class="overlay"></div>

<div class="page-wrapper">
    <h1 class="page-heading">View Accounts</h1>
    <p class="page-subheading">Admin panel › Browse and edit staff and admin accounts.</p>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($edit_success): ?>
        <div class="alert alert-success">✓ Account for <strong><?php echo $edit_success; ?></strong> has been updated.</div>
    <?php endif; ?>

    <div class="summary-bar">
        <div class="stat-pill">Total Accounts <span><?php echo $total_count; ?></span></div>
        <div class="stat-pill">Admins <span><?php echo $admin_count; ?></span></div>
        <div class="stat-pill">Student Workers <span><?php echo $worker_count; ?></span></div>
    </div>

    <div class="controls-row">
        <div class="search-wrapper">
            <div class="search-box">
                <div class="search-inner">
                    <input class="search-input" type="text" id="searchInput" placeholder="Search by name, username, or email…">
                </div>
            </div>
        </div>
        <select class="filter-select" id="roleFilter">
            <option value="all">All Roles</option>
            <option value="admin">Admin</option>
            <option value="worker">Student Worker</option>
        </select>
    </div>

    <h2 class="section-heading">
        👥 Staff Accounts
        <span class="badge"><?php echo $total_count; ?></span>
    </h2>

    <div class="table-wrapper">
        <?php if (empty($workers)): ?>
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p>No accounts found.</p>
            </div>
        <?php else: ?>
        <table id="workersTable">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workers as $w): ?>
                <tr
                    data-name="<?php echo strtolower(htmlspecialchars($w->get_first_name() . ' ' . $w->get_last_name())); ?>"
                    data-username="<?php echo strtolower(htmlspecialchars($w->get_id())); ?>"
                    data-email="<?php echo strtolower(htmlspecialchars($w->get_email())); ?>"
                    data-role="<?php echo strtolower(htmlspecialchars($w->get_type())); ?>"
                >
                    <td><?php echo htmlspecialchars($w->get_first_name()); ?></td>
                    <td><?php echo htmlspecialchars($w->get_last_name()); ?></td>
                    <td class="td-email"><?php echo htmlspecialchars($w->get_email() ?: '—'); ?></td>
                    <td class="td-username">@<?php echo htmlspecialchars($w->get_id()); ?></td>
                    <td>
                        <?php if ($w->get_type() === 'admin'): ?>
                            <span class="badge-role badge-admin">🔑 Admin</span>
                        <?php else: ?>
                            <span class="badge-role badge-worker">👤 Worker</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn-edit" onclick="openEditModal(
                            '<?php echo htmlspecialchars($w->get_id(),         ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($w->get_first_name(), ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($w->get_last_name(),  ENT_QUOTES); ?>',
                            '<?php echo htmlspecialchars($w->get_email(),      ENT_QUOTES); ?>'
                        )">✏ Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr class="no-results-row" id="noResultsRow">
                    <td colspan="6">No accounts match your search.</td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <a href="staffPage.php" class="back-link">← Back to dashboard</a>
</div>

<!-- Edit Modal -->
<div class="modal-backdrop" id="editModalBackdrop">
    <div class="modal">
        <button class="modal-close-btn" onclick="closeEditModal()" aria-label="Close">&times;</button>
        <div class="modal-title">Edit Account</div>
        <div class="modal-sub" id="modalSub">Editing @username</div>

        <form method="POST" action="view-worker.php">
            <input type="hidden" name="edit_worker" value="1">
            <input type="hidden" name="username" id="modal_username_orig">

            <div class="name-row">
                <div class="form-group">
                    <label class="form-label" for="modal_first_name">First Name</label>
                    <input class="form-input" type="text" id="modal_first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="modal_last_name">Last Name</label>
                    <input class="form-input" type="text" id="modal_last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="modal_email">Email</label>
                <input class="form-input" type="email" id="modal_email" name="email" placeholder="optional">
            </div>

            <hr class="form-divider">

            <div class="form-group">
                <label class="form-label" for="modal_new_username">Username</label>
                <input class="form-input" type="text" id="modal_new_username" name="new_username" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(username, firstName, lastName, email) {
    document.getElementById('modal_username_orig').value = username;
    document.getElementById('modal_new_username').value  = username;
    document.getElementById('modal_first_name').value    = firstName;
    document.getElementById('modal_last_name').value     = lastName;
    document.getElementById('modal_email').value         = email;
    document.getElementById('modalSub').textContent      = 'Editing @' + username;
    document.getElementById('editModalBackdrop').classList.add('open');
}
function closeEditModal() {
    document.getElementById('editModalBackdrop').classList.remove('open');
}
document.getElementById('editModalBackdrop').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

const searchInput  = document.getElementById('searchInput');
const roleFilter   = document.getElementById('roleFilter');
const noResultsRow = document.getElementById('noResultsRow');

function filterTable() {
    const q    = searchInput.value.toLowerCase().trim();
    const role = roleFilter.value;
    const rows = document.querySelectorAll('#workersTable tbody tr:not(.no-results-row)');
    let visible = 0;
    rows.forEach(row => {
        const matchSearch = !q ||
            row.dataset.name.includes(q) ||
            row.dataset.username.includes(q) ||
            row.dataset.email.includes(q);
        const matchRole = role === 'all' || row.dataset.role === role;
        if (matchSearch && matchRole) { row.style.display = ''; visible++; }
        else { row.style.display = 'none'; }
    });
    if (noResultsRow) noResultsRow.style.display = visible === 0 ? 'table-row' : 'none';
}

searchInput.addEventListener('input', filterTable);
roleFilter.addEventListener('change', filterTable);
</script>

<?php require 'footer.php'; ?>
</body>
</html>