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
            // Use the existing update_person_required function from dbPersons.php
            // keeping city, state, phone1, affiliation, branch, email_prefs from current values
            $result = update_person_required(
                $username,
                $first_name,
                $last_name,
                $person->get_city(),
                $person->get_state(),
                $email,
                $person->get_phone1(),
                $person->get_email_prefs(),
                $person->get_affiliation(),
                $person->get_branch()
            );

            // If username changed, do a separate direct update
            if ($result && $new_username !== $username) {
                $con = connect();
                $safe_new = mysqli_real_escape_string($con, $new_username);
                $safe_old = mysqli_real_escape_string($con, $username);
                $q = "UPDATE dbpersons SET id='$safe_new' WHERE id='$safe_old'";
                $result = mysqli_query($con, $q);
                mysqli_close($con);
            }

            if ($result) {
                $edit_success = htmlspecialchars($first_name . ' ' . $last_name);
            } else {
                $error = 'Could not update the account. Please try again.';
            }
        }
    }
}

// Fetch all accounts using the correct function name from dbPersons.php
$workers = [];
$all_persons = getall_persons(); // correct function name from dbPersons.php
if ($all_persons) {
    foreach ($all_persons as $p) {
        // getall_persons() already excludes vmsroot, but just in case:
        if ($p->get_id() === 'vmsroot') continue;
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
    <title>Seacobeck Library | View Accounts</title>
</head>
<body>
<?php require 'header.php'; ?>

<style>
    .page-wrap {
        max-width: 980px;
        margin: 40px auto;
        padding: 0 20px 80px;
        color: white;
    }
    .page-title { margin-bottom: 6px; font-size: 28px; font-weight: 700; color: white; }
    .subtitle   { color: #8DC9F7; font-size: 14px; margin-bottom: 28px; }

    /* Summary pills */
    .summary-bar { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
    .stat-pill {
        background: rgb(40,40,43);
        border: 1px solid rgba(141,201,247,.2);
        border-radius: 50px;
        padding: 8px 20px;
        font-size: 13px;
        font-weight: 700;
        color: #8DC9F7;
    }
    .stat-pill span { color: white; margin-left: 6px; }

    /* Search / filter bar */
    .search-bar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
    .search-input {
        flex: 1;
        min-width: 200px;
        padding: 10px 16px;
        font-size: 14px;
        font-family: inherit;
        background: rgba(255,255,255,.07);
        border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 8px;
        color: white;
        outline: none;
        transition: border-color .2s;
    }
    .search-input:focus { border-color: #8DC9F7; }
    .search-input::placeholder { color: rgba(255,255,255,.3); }
    .filter-select {
        padding: 10px 14px;
        font-size: 14px;
        font-family: inherit;
        background: rgba(255,255,255,.07);
        border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 8px;
        color: white;
        outline: none;
        cursor: pointer;
    }
    .filter-select option { background: rgb(40,40,43); }

    /* Alerts */
    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
    .alert-error   { background: rgba(180,30,30,.85); color: white; }
    .alert-success { background: rgba(22,163,74,.85);  color: white; }

    /* Table */
    .table-wrap {
        background: rgb(40,40,43);
        border-radius: 14px;
        border: 1px solid rgba(141,201,247,.2);
        overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    thead th {
        background: rgba(141,201,247,.1);
        color: #8DC9F7;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        padding: 13px 16px;
        text-align: left;
        border-bottom: 1px solid rgba(141,201,247,.2);
        white-space: nowrap;
    }
    tbody tr { border-bottom: 1px solid rgba(255,255,255,.06); transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: rgba(255,255,255,.04); }
    tbody td { padding: 13px 16px; color: white; vertical-align: middle; }
    .td-username { color: #8DC9F7; font-weight: 700; font-size: 13px; }
    .td-email    { color: rgba(255,255,255,.55); font-size: 13px; }

    .badge { display: inline-block; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .badge-admin  { background: rgba(251,191,36,.15); color: #fbbf24; border: 1px solid rgba(251,191,36,.3); }
    .badge-worker { background: rgba(141,201,247,.12); color: #8DC9F7; border: 1px solid rgba(141,201,247,.25); }

    /* Edit button */
    .btn-edit {
        background: transparent;
        border: 1.5px solid rgba(141,201,247,.35);
        color: #8DC9F7;
        font-size: 12px;
        font-family: inherit;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 7px;
        cursor: pointer;
        transition: background .2s, border-color .2s;
        white-space: nowrap;
    }
    .btn-edit:hover { background: rgba(141,201,247,.12); border-color: #8DC9F7; }

    /* Empty / no-results */
    .empty-state { text-align: center; padding: 60px 20px; color: rgba(255,255,255,.35); font-size: 15px; }
    .no-results-row { display: none; }
    .no-results-row td { text-align: center; padding: 40px; color: rgba(255,255,255,.35); }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.7);
        z-index: 3000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.open { display: flex; }
    .modal {
        background: rgb(40,40,43);
        border-radius: 16px;
        border: 1px solid rgba(141,201,247,.25);
        padding: 32px;
        width: 100%;
        max-width: 480px;
        position: relative;
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-14px) scale(.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1);    }
    }
    .modal-title { font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px; }
    .modal-sub   { font-size: 13px; color: #8DC9F7; margin-bottom: 24px; }
    .modal-close-btn {
        position: absolute; top: 18px; right: 20px;
        background: transparent; border: none;
        color: rgba(255,255,255,.45); font-size: 26px;
        cursor: pointer; line-height: 1; transition: color .2s;
    }
    .modal-close-btn:hover { color: white; }

    /* Modal form */
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px; }
    .form-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #8DC9F7; }
    .form-input {
        width: 100%; padding: 11px 14px; font-size: 15px; font-family: inherit;
        background: rgba(255,255,255,.07); border: 1.5px solid rgba(255,255,255,.15);
        border-radius: 8px; color: white; outline: none; transition: border-color .2s; box-sizing: border-box;
    }
    .form-input:focus { border-color: #8DC9F7; }
    .form-input::placeholder { color: rgba(255,255,255,.3); }
    .name-row { display: flex; gap: 12px; }
    .name-row .form-group { flex: 1; }
    .form-divider { border: none; border-top: 1px solid rgba(255,255,255,.1); margin: 18px 0; }
    .modal-actions { display: flex; gap: 10px; margin-top: 6px; }
    .btn-save {
        flex: 1; padding: 12px; font-size: 15px; font-family: inherit; font-weight: 700;
        background: #7b95e9; color: white; border: none; border-radius: 10px;
        cursor: pointer; transition: background .2s, transform .1s;
    }
    .btn-save:hover  { background: #0a1e61; }
    .btn-save:active { transform: scale(.97); }
    .btn-cancel {
        padding: 12px 20px; font-size: 15px; font-family: inherit; font-weight: 700;
        background: transparent; color: rgba(255,255,255,.5);
        border: 1.5px solid rgba(255,255,255,.15); border-radius: 10px;
        cursor: pointer; transition: background .2s, color .2s;
    }
    .btn-cancel:hover { background: rgba(255,255,255,.07); color: white; }

    .back-link { display: inline-block; margin-top: 16px; color: #8DC9F7; font-size: 14px; text-decoration: none; }
    .back-link:hover { text-decoration: underline; }
</style>

<div class="page-wrap">
    <div class="page-title">View Accounts</div>
    <p class="subtitle">Admin panel &rsaquo; View accounts</p>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($edit_success): ?>
        <div class="alert alert-success">✓ Account for <b><?php echo $edit_success; ?></b> has been updated.</div>
    <?php endif; ?>

    <div class="summary-bar">
        <div class="stat-pill">Total Accounts <span><?php echo $total_count; ?></span></div>
        <div class="stat-pill">Admins <span><?php echo $admin_count; ?></span></div>
        <div class="stat-pill">Student Workers <span><?php echo $worker_count; ?></span></div>
    </div>

    <div class="search-bar">
        <input class="search-input" type="text" id="searchInput" placeholder="Search by name, username, or email…">
        <select class="filter-select" id="roleFilter">
            <option value="all">All Roles</option>
            <option value="admin">Admin</option>
            <option value="worker">Student Worker</option>
        </select>
    </div>

    <div class="table-wrap">
        <?php if (empty($workers)): ?>
            <div class="empty-state">No accounts found.</div>
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
                            <span class="badge badge-admin">🔑 Admin</span>
                        <?php else: ?>
                            <span class="badge badge-worker">👤 Worker</span>
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

    <a href="index.php" class="back-link">← Back to dashboard</a>
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