<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_cache_expire(30);
session_start();

date_default_timezone_set("America/New_York");

if (!isset($_SESSION['access_level'])) {
    header('Location: login.php');
    die();
}

include_once('database/dbPersons.php');
include_once('domain/Person.php');

$accessLevel = (int) $_SESSION['access_level'];
$isGuest  = ($accessLevel === 0);
$isWorker = ($accessLevel === 1);
$isAdmin  = ($accessLevel >= 2);

if (!$isGuest && isset($_SESSION['_id'])) {
    $person = retrieve_person($_SESSION['_id']);
}
?>
<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
* {
  font-family: Quicksand, 'Lucida Sans', sans-serif;
  box-sizing: border-box;
}

.input-field {
  width: 100%;
  background: rgba(255,255,255,0.88);
  color: #111;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 15px;
  outline: none;
  transition: box-shadow 0.2s, border-color 0.2s;
}

.input-field:focus {
  box-shadow: 0 0 0 3px rgba(156,32,7,0.25);
  border-color: #9C2007;
}

.input-field::placeholder {
  color: #6b7280;
}

#toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  color: white;
  padding: 14px 28px;
  border-radius: 10px;
  font-size: 15px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
  transition: transform 0.4s ease, opacity 0.4s ease;
  opacity: 0;
  z-index: 9999;
  text-align: center;
}

#toast.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

#toast.success {
  background: #002D61;
  border-left: 5px solid #8DC9F7;
}

#toast.error {
  background: #7A1905;
  border-left: 5px solid #f87171;
}

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 3px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  vertical-align: middle;
  margin-right: 8px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.glass-card {
  background: rgba(141,201,247,0.10);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.25);
  border: 1px solid rgba(255,255,255,0.15);
}

.table-scroll {
  width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
}

.status-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-current {
  background: rgba(59,130,246,0.2);
  color: #bfdbfe;
  border: 1px solid rgba(191,219,254,0.35);
}

.status-overdue {
  background: rgba(239,68,68,0.2);
  color: #fecaca;
  border: 1px solid rgba(254,202,202,0.35);
}

.section-heading {
  text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black;
  color: #bfe5ed;
}

.mini-stat {
  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 14px;
  padding: 16px;
  text-align: center;
}

.mini-stat-number {
  font-size: 28px;
  font-weight: 700;
  color: white;
}

.mini-stat-label {
  font-size: 13px;
  color: #dbeafe;
  margin-top: 6px;
}

table {
  width: 100%;
  min-width: 760px;
  border-collapse: collapse;
}

thead th {
  background: rgba(13,43,141,0.75);
  color: #dbeafe;
  text-align: left;
  font-size: 14px;
  padding: 14px 12px;
}

tbody td {
  padding: 14px 12px;
  color: white;
  border-top: 1px solid rgba(255,255,255,0.08);
  font-size: 14px;
}

tbody tr:nth-child(even) {
  background: rgba(255,255,255,0.03);
}

.empty-message {
  color: #dbeafe;
  text-align: center;
  padding: 20px 10px;
  font-size: 14px;
  opacity: 0.9;
}
</style>
<title>Seacobeck Curriculum Lab | Check Out</title>
</head>
<body class="min-h-screen flex flex-col bg-cover bg-center relative"
  style="background-image: url('images/library.jpg'); padding-top: 95px;">

  <?php require 'header.php'; ?>

  <div class="absolute inset-0 bg-[#002D61]/85" style="top: 95px;"></div>

  <div id="toast"></div>

  <div class="relative z-10 w-full flex-grow px-4 py-10">
    <div class="max-w-6xl mx-auto flex flex-col gap-8">

      <div class="w-full max-w-md mx-auto px-6 py-8 flex flex-col items-center text-white glass-card">
        <h2 class="text-3xl font-bold mb-2 text-center section-heading">
          Thank you for using the Curriculum Library!
        </h2>

        <p class="text-sm text-white mb-6 text-center opacity-80">
          A confirmation email with your due date will be sent to you.
        </p>

        <form style="display: flex; gap: 15px; margin-bottom: 20px;">
          <input type="radio" id="checkout" name="action" value="Checkout" checked>
          <label for="checkout" style="color: white;">Checking Out</label>
          <input type="radio" id="return" name="action" value="Return">
          <label for="return" style="color: white;">Returning</label>
        </form>

        <div class="w-full space-y-5">
          <input type="text" id="firstName" placeholder="First Name" class="input-field" />
          <input type="text" id="lastName" placeholder="Last Name" class="input-field" />
          <input type="text" id="materialName" placeholder="Name of Item" class="input-field" />
          <input type="email" id="email" placeholder="Email" class="input-field" />

          <button id="submitBtn" onclick="handleCheckout()"
            class="w-full bg-[#0d2b8d] text-white font-bold py-3 rounded-lg hover:bg-[#0a1e61] active:scale-95 transition duration-300">
            Submit
          </button>
        </div>
      </div>

      <div class="glass-card p-6 md:p-8 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div>
            <h2 class="text-3xl font-bold section-heading">Current Checkouts & Overdue Items</h2>
            <p class="text-blue-100 text-sm mt-2 opacity-90">
              Front-end display of checked out materials and overdue items.
            </p>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="mini-stat">
              <div class="mini-stat-number" id="totalCount">0</div>
              <div class="mini-stat-label">Total Checkouts</div>
            </div>
            <div class="mini-stat">
              <div class="mini-stat-number" id="currentCount">0</div>
              <div class="mini-stat-label">Current</div>
            </div>
            <div class="mini-stat">
              <div class="mini-stat-number" id="overdueCount">0</div>
              <div class="mini-stat-label">Overdue</div>
            </div>
            <div class="mini-stat">
              <div class="mini-stat-number" id="criticalCount">0</div>
              <div class="mini-stat-label">7+ Days Overdue</div>
            </div>
          </div>
        </div>

        <div class="mb-6">
          <input
            type="text"
            id="searchInput"
            placeholder="Search borrower, email, or item..."
            class="input-field"
            oninput="renderTables()"
          />
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
          <div>
            <h3 class="text-xl font-bold mb-3 text-blue-100">Current Checkouts</h3>
            <div class="w-full overflow-hidden rounded-xl border border-white/10">
              <div class="table-scroll">
                <table>
                  <thead>
                    <tr>
                      <th>Borrower</th>
                      <th>Item</th>
                      <th>Email</th>
                      <th>Due Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="currentTableBody"></tbody>
                </table>
              </div>
            </div>
            <div id="currentEmpty" class="empty-message hidden">No current checkouts found.</div>
          </div>

          <div>
            <h3 class="text-xl font-bold mb-3 text-red-200">Overdue Items</h3>
            <div class="w-full overflow-hidden rounded-xl border border-white/10">
              <div class="table-scroll">
                <table>
                  <thead>
                    <tr>
                      <th>Borrower</th>
                      <th>Item</th>
                      <th>Email</th>
                      <th>Due Date</th>
                      <th>Days Late</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody id="overdueTableBody"></tbody>
                </table>
              </div>
            </div>
            <div id="overdueEmpty" class="empty-message hidden">No overdue items found.</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Mellisa Wells
    <a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">mwells@umw.edu</a>
  </footer>

  <script>
    emailjs.init("wyffuz6ZVKFN7dYco");

    const checkoutData = [
      {
        name: "Jordan Smith",
        item: "Intro to Databases",
        email: "jordansmith@umw.edu",
        dueDate: "2026-03-28"
      },
      {
        name: "Avery Johnson",
        item: "Discrete Mathematics",
        email: "averyj@umw.edu",
        dueDate: "2026-03-16"
      },
      {
        name: "Taylor Brown",
        item: "Laptop Charger Kit",
        email: "tbrown@umw.edu",
        dueDate: "2026-03-25"
      },
      {
        name: "Morgan Lee",
        item: "Web Development Essentials",
        email: "morganlee@umw.edu",
        dueDate: "2026-03-10"
      },
      {
        name: "Casey Wilson",
        item: "Scientific Calculator",
        email: "caseyw@umw.edu",
        dueDate: "2026-03-20"
      }
    ];

    function getReturnDate() {
      const date = new Date();
      date.setDate(date.getDate() + 14);
      return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    }

    function getDueDateISO() {
      const date = new Date();
      date.setDate(date.getDate() + 14);
      return date.toISOString().split('T')[0];
    }

    function showToast(message, type = 'success') {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = `show ${type}`;
      setTimeout(() => { toast.className = ''; }, 4500);
    }

    function setLoading(loading) {
      const btn = document.getElementById('submitBtn');
      btn.disabled = loading;
      btn.innerHTML = loading
        ? '<span class="spinner"></span>Sending...'
        : 'Submit';
    }

    function formatDisplayDate(dateStr) {
      const date = new Date(dateStr + "T00:00:00");
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });
    }

    function getDaysLate(dueDateStr) {
      const today = new Date();
      const dueDate = new Date(dueDateStr + "T00:00:00");

      today.setHours(0, 0, 0, 0);
      dueDate.setHours(0, 0, 0, 0);

      const diffMs = today - dueDate;
      return Math.floor(diffMs / (1000 * 60 * 60 * 24));
    }

    function escapeHtml(str) {
      return str.replace(/[&<>"']/g, function(match) {
        const escapes = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#39;'
        };
        return escapes[match];
      });
    }

    function renderTables() {
      const searchValue = document.getElementById('searchInput').value.trim().toLowerCase();

      const filtered = checkoutData.filter(entry => {
        return (
          entry.name.toLowerCase().includes(searchValue) ||
          entry.item.toLowerCase().includes(searchValue) ||
          entry.email.toLowerCase().includes(searchValue)
        );
      });

      const currentItems = [];
      const overdueItems = [];

      filtered.forEach(entry => {
        const daysLate = getDaysLate(entry.dueDate);
        if (daysLate > 0) {
          overdueItems.push({ ...entry, daysLate });
        } else {
          currentItems.push(entry);
        }
      });

      const currentBody = document.getElementById('currentTableBody');
      const overdueBody = document.getElementById('overdueTableBody');
      currentBody.innerHTML = '';
      overdueBody.innerHTML = '';

      if (currentItems.length === 0) {
        document.getElementById('currentEmpty').classList.remove('hidden');
      } else {
        document.getElementById('currentEmpty').classList.add('hidden');

        currentItems.forEach(entry => {
          currentBody.innerHTML += `
            <tr>
              <td>${escapeHtml(entry.name)}</td>
              <td>${escapeHtml(entry.item)}</td>
              <td>${escapeHtml(entry.email)}</td>
              <td>${formatDisplayDate(entry.dueDate)}</td>
              <td><span class="status-pill status-current">Current</span></td>
            </tr>
          `;
        });
      }

      if (overdueItems.length === 0) {
        document.getElementById('overdueEmpty').classList.remove('hidden');
      } else {
        document.getElementById('overdueEmpty').classList.add('hidden');

        overdueItems
          .sort((a, b) => b.daysLate - a.daysLate)
          .forEach(entry => {
            const statusText = entry.daysLate >= 7 ? 'Critical' : 'Overdue';

            overdueBody.innerHTML += `
              <tr>
                <td>${escapeHtml(entry.name)}</td>
                <td>${escapeHtml(entry.item)}</td>
                <td>${escapeHtml(entry.email)}</td>
                <td>${formatDisplayDate(entry.dueDate)}</td>
                <td>${entry.daysLate}</td>
                <td><span class="status-pill status-overdue">${statusText}</span></td>
              </tr>
            `;
          });
      }

      document.getElementById('totalCount').textContent = filtered.length;
      document.getElementById('currentCount').textContent = currentItems.length;
      document.getElementById('overdueCount').textContent = overdueItems.length;
      document.getElementById('criticalCount').textContent =
        overdueItems.filter(item => item.daysLate >= 7).length;
    }

    function handleCheckout() {
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const name = `${firstName} ${lastName}`.trim();
      const title = document.getElementById('materialName').value.trim();
      const email = document.getElementById('email').value.trim();
      const return_date = getReturnDate();

      if (!firstName || !lastName || !title || !email) {
        showToast('⚠️ Please fill in all fields.', 'error');
        return;
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showToast('⚠️ Please enter a valid email address.', 'error');
        return;
      }

      setLoading(true);

      emailjs.send("service_ulaa9k9", "template_r1s5j65", {
        name,
        title,
        email,
        return_date
      })
      .then(() => {
        checkoutData.push({
          name,
          item: title,
          email,
          dueDate: getDueDateISO()
        });

        setLoading(false);
        showToast(`✅ Checked out! Confirmation sent to ${email}`, 'success');

        document.getElementById('firstName').value = '';
        document.getElementById('lastName').value = '';
        document.getElementById('materialName').value = '';
        document.getElementById('email').value = '';

        renderTables();
      })
      .catch((err) => {
        setLoading(false);
        console.error("EmailJS error:", err);
        showToast('❌ Something went wrong. Please try again.', 'error');
      });
    }

    document.addEventListener('keydown', e => {
      if (e.key === 'Enter') handleCheckout();
    });

    renderTables();
  </script>

</body>
</html>