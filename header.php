<?php
date_default_timezone_set('America/New_York');
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;700&family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
<?php if (empty($tailwind_mode)): ?>
* { box-sizing: border-box; margin: 0; padding: 0; }
<?php endif; ?>
body { font-family: 'Quicksand', sans-serif; padding-top: 96px; font-size: 14pt; }
h2 { font-weight: normal; font-size: 30px; }
.extra-info { max-height: 0px; overflow: hidden; transition: max-height 0.3s ease-out; font-size: 14px; color: #444; margin-top: 5px; }
.content-box-test { flex: 1 1 370px; max-width: 470px; padding: 10px 10px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; cursor: pointer; border: 0.1px solid black; transition: border 0.3s; border-radius: 10px; border-bottom-right-radius: 50px; }
.content-box-test:hover { border: 4px solid #fdd05eff; }
.full-width-bar { width: 100%; background: rgb(31,31,33); padding: 17px 5%; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
.full-width-bar-sub { width: 100%; background: white; padding: 17px 5%; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
.content-box { flex: 1 1 280px; max-width: 375px; padding: 10px 2px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; }
.content-box-sub { flex: 1 1 300px; max-width: 470px; padding: 10px 10px; display: flex; flex-direction: column; align-items: center; text-align: center; position: relative; }
.content-box img { width: 100%; height: auto; background: white; border-radius: 5px; border-bottom-right-radius: 50px; border: 0.5px solid #828282; }
.content-box-sub img { width: 105%; height: auto; background: white; border-radius: 5px; border-bottom-right-radius: 50px; border: 1px solid #828282; }
.small-text { position: absolute; top: 20px; left: 30px; font-size: 14px; font-weight: 700; color: #297760ff; }
.large-text { position: absolute; top: 40px; left: 30px; font-size: 22px; font-weight: 700; color: black; max-width: 90%; }
.large-text-sub { position: absolute; top: 60%; left: 10%; font-size: 22px; font-weight: 700; color: black; max-width: 90%; }
.graph-text { position: absolute; top: 75%; left: 10%; font-size: 14px; font-weight: 700; color: #712977ff; max-width: 90%; margin-bottom: 80px; }
.navbar { gap: 10px; width: 100%; height: 100px; position: fixed; top: 0; left: 0; background: rgb(31,31,33); box-shadow: 0px 2px 8px rgba(0,0,0,.25); display: flex; align-items: center; padding: 0 20px; z-index: 1000; }
.left-section { display: flex; align-items: center; gap: 20px; }
.logo-container { background: rgb(31,31,33); padding: 10px 20px; border-radius: 50px; box-shadow: 0px 4px 4px rgba(0,0,0,.25) inset; }
.logo-container img { width: 52px; height: auto; }
.nav-links { display: flex; gap: 2vw; }
.nav-links div { font-size: 1.75vw; font-weight: 700; color: white; cursor: pointer; }
.right-section { margin-left: auto; display: flex; align-items: center; gap: 20px; }
.nav-item { position: relative; cursor: pointer; padding: 0px; transition: color 0.3s, outline 0.3s; }
.dropdown { display: none; position: absolute; top: calc(100% + 6px); right: 0; background-color: rgb(31,31,33); border: 1px solid rgb(31,31,33); box-shadow: 0 2px 5px rgba(0,0,0,.1); border-radius: 5px; padding: 10px; color: white; font-size: 2vw; width: max-content; max-width: 50vw;}
.dropdown div { padding: 8px; white-space: nowrap; transition: background 0.3s; }
.dropdown div:hover { background: rgba(0,0,0,.1); }
.nav-item:hover, .nav-item.active { color: #8DC9F7; outline: 1px solid #8DC9F7; outline-offset: 7px; }
.date-box { background: #2B2B2E; padding: 10px 30px; border-radius: 50px; box-shadow: -4px 4px 4px rgba(0,0,0,.25) inset; color: white; font-size: 24px; font-weight: 700; text-align: center; }
.icon { width: 47px; height: 47px; border-radius: 50%; position: relative; display: flex; align-items: center; justify-content: center;}
.nav-buttons { position: absolute; bottom: 10%; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; justify-content: center; width: 100%; }
.nav-button { background: rgb(201,171,129); border: none; color: white; font-size: 20px; font-family: 'Quicksand', sans-serif; font-weight: 600; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.4s ease-in-out; backdrop-filter: blur(8px); padding: 6px 8px; padding-top: 10px; width: 55px; overflow: hidden; white-space: nowrap; box-shadow: 0 4px 8px rgba(0,0,0,.1); }
.nav-button:hover { width: 160px; padding: 6px 8px; padding-top: 10px; }
.nav-button .text { opacity: 0; transform: translateX(-10px); transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out; }
.nav-button:hover .text { opacity: 1; transform: translateX(0); }
.nav-button .arrow { display: inline-block; transition: transform 0.3s ease; }
.nav-button:hover .arrow { transform: translateX(5px); }
.arrow-button { position: absolute; bottom: 24px; right: 16px; background: transparent; border: none; font-size: 23px; font-weight: bold; color: black; cursor: pointer; transition: transform 0.3s ease; padding: 0; }
.arrow-button:hover { transform: translateX(5px); background: transparent; }
.footer { width: 100%; background: #8DC9F7; display: flex; justify-content: space-between; align-items: flex-start; padding: 30px 50px; flex-wrap: wrap; }
.footer-left { display: flex; flex-direction: column; align-items: center; }
.footer-logo { width: 150px; margin-bottom: 15px; }
.social-icons { display: flex; gap: 15px; }
.social-icons a { color: white; font-size: 20px; transition: color 0.3s ease; }
.social-icons a:hover { color: rgb(31,31,33); }
.footer-right { display: flex; gap: 50px; flex-wrap: wrap; }
.footer-section { display: flex; flex-direction: column; gap: 10px; color: white; font-family: Inter, sans-serif; font-size: 16px; font-weight: 500; }
.footer-topic { font-size: 18px; font-weight: bold; }
.footer a { color: white; text-decoration: none; transition: background 0.2s ease, color 0.2s ease; padding: 5px 10px; border-radius: 5px; }
.footer a:hover { background: rgba(255,255,255,.1); color: #dcdcdc; }
.background-image { width: 100%; border-radius: 17px; }
.icon-overlay { position: absolute; top: 40px; left: 50%; transform: translateX(-50%); background: rgb(31,31,33); padding: 10px; border-radius: 50%; display: flex; justify-content: center; align-items: center; }
.icon-overlay img { width: 40px; height: 40px; opacity: 0.9; filter: invert(1); }
.nav-item img { border-radius: 15px; transition: filter 0.3s, background-color 0.3s; }
.nav-item:hover img, .nav-item.active img { filter: none; }
.icon .dropdown { top: 130%; right: 0; left: auto; }
.in-nav { display: flex; align-items: center; gap: 8px; }
.in-nav span { font-size: 24px; }
.in-nav img { width: 40px; height: 40px; border-radius: 5px; border-bottom-right-radius: 20px; filter: invert(1) !important; }
.icon-butt svg { transition: transform 0.2s ease, fill 0.2s ease; cursor: pointer; }
.icon-butt:hover svg { transform: scale(1.1) rotate(5deg); fill: #7aacf5; }
.font-change { font-size: 30px; font-family: Quicksand; color: white; }
.accessibility-btn { position: fixed; bottom: 18px; right: 18px; width: 70px; height: 70px; border-radius: 80px; background: var(--main-color); border: 3px solid var(--main-color); cursor: pointer; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: 4px; }
.accessibility-btn img { width: 100%; height: 100%; object-fit: contain; filter: invert(1); }
.accessibility-modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 2100; align-items: center; justify-content: center; padding: 20px; }
.accessibility-modal { background: #1f1f21; color: white; max-width: 520px; width: 100%; border-radius: 12px; padding: 20px; box-shadow: 0 8px 30px rgba(0,0,0,.6); }
.accessibility-modal h3 { margin-bottom: 8px; }
.modal-header { display:flex; justify-content:space-between; align-items:center; }
.nav-link { color: white; text-decoration: none; }
.dropdown-link { color: inherit; text-decoration: none; display:block; }
.icon-img { filter: invert(1); }
.modal-close { background:transparent; border:none; color:white; font-size:30px; cursor:pointer; }
.modal-desc { color: rgba(255,255,255,.7); }
.accessibility-row { display:flex; gap:12px; align-items:center; margin:10px 0; }
.accessibility-row label { min-width: 120px; font-weight:600; }
.accessibility-modal select { font-size:16px; }
.accessibility-actions { display:flex; justify-content:flex-end; gap:8px; margin-top:16px; }
.accessibility-actions button { padding:8px 12px; border-radius:8px; cursor:pointer; border:none; }
.accessibility-actions .save { background:var(--wv-accent-color); color:var(--wv-accent-foreground); }
.accessibility-actions .reset { background:transparent; color:#fff; border:1px solid rgba(255,255,255,.12); }
@media (max-width: 900px) {
  .footer { flex-direction: column; align-items: center; text-align: center; }
  .footer-right { flex-direction: column; align-items: center; gap: 30px; margin-top: 20px; }
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".nav-item").forEach(item => {
        item.addEventListener("click", function(event) {
            event.stopPropagation();
            document.querySelectorAll(".nav-item").forEach(nav => {
                if (nav !== item) {
                    nav.classList.remove("active");
                    if (nav.querySelector(".dropdown")) nav.querySelector(".dropdown").style.display = "none";
                }
            });
            this.classList.toggle("active");
            let dropdown = this.querySelector(".dropdown");
            if (dropdown) dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        });
    });
    document.addEventListener("click", function() {
        document.querySelectorAll(".nav-item").forEach(nav => {
            nav.classList.remove("active");
            if (nav.querySelector(".dropdown")) nav.querySelector(".dropdown").style.display = "none";
        });
    });
});
</script>
</head>
<header>
<?php
// Shared navigation links to keep Admin and Worker sections consistent
$common_nav_links = '
      <div class="nav-item"><a href="index.php" class="nav-link">Home</a></div>
      <div class="nav-item"><a href="calendar.php" class="nav-link">Catalog</a></div>
      <div class="nav-item"><a href="checkout.php" class="nav-link">Checkout</a></div>
      <div class="nav-item">Worker</div>';

$showing_login = false;

// ── GUEST NAVBAR ──────────────────────────────────────────────────────────
if (!isset($_SESSION['logged_in']) || $_SESSION['access_level'] === 0) {
echo('
<div class="navbar">
  <div class="left-section">
    <div class="logo-container">
      <a href="index.php"><img src="images/UMW_Eagles-logo.png" alt="Logo"></a>
    </div>
    <div class="nav-links">
      <div class="nav-item"><a href="index.php" class="nav-link">Home</a></div>
      <div class="nav-item"><a href="calendar.php" class="nav-link">Catalog</a></div>
      <div class="nav-item"><a href="checkout.php" class="nav-link">Checkout</a></div>
    </div>
  </div>
  <div class="right-section">
    <div class="nav-links">
      <div class="nav-item">
        <div class="icon">
          <img src="images/usaicon.png" alt="User Icon" class="icon-img in-nav-img">
          <div class="dropdown">
            <a href="login.php" class="dropdown-link"><div>Log in</div></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>');

} else if ($_SESSION['logged_in']) {
// (Permission logic remains exactly the same as provided)
$permission_array['index.php'] = 0; $permission_array['about.php'] = 0; $permission_array['apply.php'] = 0; $permission_array['logout.php'] = 0; $permission_array['volunteerregister.php'] = 0; $permission_array['leaderboard.php'] = 0; $permission_array['help.php'] = 1; $permission_array['dashboard.php'] = 1; $permission_array['calendar.php'] = 0; $permission_array['eventsearch.php'] = 1; $permission_array['changepassword.php'] = 1; $permission_array['editprofile.php'] = 1; $permission_array['inbox.php'] = 1; $permission_array['date.php'] = 1; $permission_array['event.php'] = 0; $permission_array['viewprofile.php'] = 1; $permission_array['viewnotification.php'] = 1; $permission_array['volunteerreport.php'] = 1; $permission_array['viewmyupcomingevents.php'] = 1; $permission_array['volunteerviewgroup.php'] = 1; $permission_array['viewcheckinout.php'] = 1; $permission_array['viewresources.php'] = 1; $permission_array['discussionmain.php'] = 1; $permission_array['viewdiscussions.php'] = 1; $permission_array['discussioncontent.php'] = 1; $permission_array['milestonepoints.php'] = 1; $permission_array['selectvotm.php'] = 1; $permission_array['volunteerviewgroupmembers.php'] = 1; $permission_array['viewallevents.php'] = 0; $permission_array['personsearch.php'] = 2; $permission_array['personedit.php'] = 0; $permission_array['viewschedule.php'] = 2; $permission_array['addweek.php'] = 2; $permission_array['log.php'] = 2; $permission_array['reports.php'] = 2; $permission_array['eventedit.php'] = 2; $permission_array['modifyuserrole.php'] = 2; $permission_array['addevent.php'] = 2; $permission_array['editevent.php'] = 2; $permission_array['report.php'] = 2; $permission_array['reportspage.php'] = 2; $permission_array['resetpassword.php'] = 2; $permission_array['eventsuccess.php'] = 2; $permission_array['viewsignuplist.php'] = 2; $permission_array['vieweventsignups.php'] = 2; $permission_array['viewpendingapps.php'] = 2; $permission_array['resources.php'] = 2; $permission_array['uploadresources.php'] = 2; $permission_array['deleteresources.php'] = 2; $permission_array['creategroup.php'] = 2; $permission_array['showgroups.php'] = 2; $permission_array['groupview.php'] = 2; $permission_array['managemembers.php'] = 2; $permission_array['deleteGroup.php'] = 2; $permission_array['volunteermanagement.php'] = 2; $permission_array['groupmanagement.php'] = 2; $permission_array['eventmanagement.php'] = 2; $permission_array['creatediscussion.php'] = 2; $permission_array['checkedinvolunteers.php'] = 2; $permission_array['deletediscussion.php'] = 2; $permission_array['generatereport.php'] = 2; $permission_array['generateemaillist.php'] = 2; $permission_array['clockoutbulk.php'] = 2; $permission_array['clockOut.php'] = 2; $permission_array['edithours.php'] = 2; $permission_array['eventlist.php'] = 1; $permission_array['eventsignup.php'] = 1; $permission_array['eventfailure.php'] = 1; $permission_array['signupsuccess.php'] = 1; $permission_array['edittimes.php'] = 1; $permission_array['adminviewingevents.php'] = 2; $permission_array['pendingApp.php'] = 1; $permission_array['requestfailed.php'] = 1; $permission_array['settimes.php'] = 1; $permission_array['eventfailurebaddeparturetime.php'] = 1; $permission_array['viewretreatapplications.php'] = 2; $permission_array['viewapplication.php'] = 2; $permission_array['createemail.php'] = 2; $permission_array['viewallapplications.php'] = 2; $permission_array['applicationsuccess.php'] = 2; $permission_array['denyapplication.php'] = 2; $permission_array['viewdrafts.php'] = 2; $permission_array['editdrafts.php'] = 2; $permission_array['logattendees.php'] = 2; $permission_array['processattendees.php'] = 2; $permission_array['viewdata.php'] = 2; $permission_array['deleteusersearch.php'] = 2; $permission_array['noshows.php'] = 2; $permission_array["view_encrypted_gallery.php"] = 2; $permission_array['upload_encrypted_image.php'] = 1; $permission_array['createsuggestion.php'] = 1; $permission_array['viewsuggestion.php'] = 2; $permission_array['create-worker.php'] = 2; $permission_array['delete-worker.php'] = 2;

$current_page = strtolower(substr($_SERVER['PHP_SELF'], strrpos($_SERVER['PHP_SELF'], '/') + 1));
$current_page = substr($current_page, strpos($current_page, "/"));
if (isset($permission_array[$current_page]) && $permission_array[$current_page] > $_SESSION['access_level']) {
    echo "<script type=\"text/javascript\">window.location = \"index.php\";</script>";
    die();
}

// ── ADMIN NAVBAR (access_level >= 2) ──────────────────────────────────────
if ($_SESSION['access_level'] >= 2) {
echo('
<div class="navbar">
  <div class="left-section">
    <div class="logo-container">
      <a href="index.php"><img src="images/UMW_Eagles-logo.png" alt="Logo"></a>
    </div>
    <div class="nav-links">'
      . $common_nav_links .
      '<div class="nav-item">Admin
        <div class="dropdown">
          <a href="create-worker.php" style="text-decoration:none"><div class="in-nav"><img src="images/plus-solid.svg"><span>Create Account</span></div></a>
          <a href="delete-worker.php" style="text-decoration:none"><div class="in-nav"><img src="images/users-solid.svg"><span>Delete Account</span></div></a>
        </div>
      </div>
    </div>
  </div>
  <div class="right-section">
    <div class="nav-links">
      <div class="nav-item">
        <div class="icon">
          <img src="images/usaicon.png" alt="User Icon" class="icon-img in-nav-img">
          <div class="dropdown">
            <a href="changePassword.php" class="dropdown-link"><div>Change Password</div></a>
            <a href="logout.php" class="dropdown-link"><div>Log Out</div></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>');
}

// ── WORKER NAVBAR (access_level == 1) ─────────────────────────────────────
else if ($_SESSION['access_level'] == 1) {
echo('
<div class="navbar">
  <div class="left-section">
    <div class="logo-container">
      <a href="index.php"><img src="images/UMW_Eagles-logo.png" alt="Logo"></a>
    </div>
    <div class="nav-links">'
      . $common_nav_links .
    '</div>
  </div>
  <div class="right-section">
    <div class="nav-links">
      <div class="nav-item" style="outline:none;">
        <div class="icon">
          <img src="images/usaicon.png" alt="User Icon">
          <div class="dropdown">
            <a href="changePassword.php" style="text-decoration:none"><div>Change Password</div></a>
            <a href="logout.php" style="text-decoration:none"><div>Log Out</div></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>');
        }
    }
?>
<script>
function updateDateAndCheckBoxes() {
  const now = new Date();
  const width = window.innerWidth;
  let formatted = "";
  if (width > 1650) {
    formatted = "Today is " + now.toLocaleDateString("en-US", { weekday:"long", year:"numeric", month:"long", day:"numeric" });
  } else if (width >= 1450) {
    formatted = now.toLocaleDateString("en-US", { weekday:"long", year:"numeric", month:"long", day:"numeric" });
  } else {
    formatted = now.toLocaleDateString("en-US");
  }
  document.querySelectorAll(".right-section .date-box").forEach(el => {
    if (width < 1130) { el.style.display = "none"; }
    else { el.style.display = ""; el.textContent = formatted; }
  });
  document.querySelectorAll(".left-section .date-box").forEach(el => {
    if (width < 750) { el.style.display = "none"; }
    else { el.style.display = ""; el.textContent = width < 1130 ? "🔁" : "Check In/Out"; }
  });
  document.querySelectorAll(".icon-butt").forEach(el => {
    if (width < 800) { el.style.display = "none"; }
    else { el.style.display = ""; }
  });
}
window.addEventListener("resize", updateDateAndCheckBoxes);
window.addEventListener("load", updateDateAndCheckBoxes);
</script>
<button class="accessibility-btn" id="accessibilityBtn" aria-haspopup="dialog" aria-controls="accessibilityModal" title="Accessibility settings">
  <img src="images/accessibility-menu.png" alt="Accessibility Menu">
</button>
<div class="accessibility-modal-backdrop" id="accessibilityBackdrop" role="dialog" aria-modal="true" aria-hidden="true">
  <div class="accessibility-modal" id="accessibilityModal">
    <div class="modal-header">
      <h3>Accessibility Settings</h3>
      <button id="accessibilityClose" class="modal-close" style="max-width:22%">&times;</button>
    </div>
    <p class="modal-desc">Adjust font size and font style. Settings persist across pages and visits.</p>
    <div class="accessibility-row">
      <label for="acc-font-size">Font size</label>
      <div style="display:flex;align-items:center;gap:8px">
        <input id="acc-font-size" type="range" min="12" max="24" step="1" value="14">
        <span id="acc-font-size-value">14pt</span>
      </div>
    </div>
    <div class="accessibility-row">
      <label for="acc-font-family">Font style</label>
      <select id="acc-font-family">
        <option value="nunito">Nunito (default)</option>
        <option value="quicksand">Quicksand</option>
        <option value="comic">Comic Sans</option>
        <option value="opendyslexic">OpenDyslexic</option>
        <option value="times">Times New Roman</option>
      </select>
    </div>
    <div class="accessibility-actions">
      <button class="reset" id="accReset">Reset</button>
      <button class="save" id="accSave">Save</button>
    </div>
  </div>
</div>
<script>
(function(){
  const KEY='wv_accessibility_settings';
  const defaults={fontSize:14,fontFamily:'nunito'};
  function getSettings(){try{const r=localStorage.getItem(KEY);return r?JSON.parse(r):Object.assign({},defaults);}catch(e){return Object.assign({},defaults);}}
  function saveSettings(s){try{localStorage.setItem(KEY,JSON.stringify(s));}catch(e){}}
  function applySettings(s){
    var size=Number(s.fontSize)||defaults.fontSize;
    if(size<12)size=12;if(size>24)size=24;
    document.documentElement.style.fontSize=size+'pt';
    var d=document.getElementById('acc-font-size-value');if(d)d.textContent=size+'pt';
    if(s.fontFamily==='nunito')document.body.style.fontFamily='Nunito, Quicksand, sans-serif';
    else if(s.fontFamily==='quicksand')document.body.style.fontFamily='Quicksand, sans-serif';
    else if(s.fontFamily==='comic')document.body.style.fontFamily='"Comic Sans MS","Comic Sans",cursive';
    else if(s.fontFamily==='opendyslexic')document.body.style.fontFamily='OpenDyslexic,"Arial",sans-serif';
    else if(s.fontFamily==='times')document.body.style.fontFamily='"Times New Roman",Times,serif';
  }
  function populateUI(s){
    var sz=document.getElementById('acc-font-size');var sv=document.getElementById('acc-font-size-value');var ff=document.getElementById('acc-font-family');
    if(sz)sz.value=s.fontSize!==undefined?s.fontSize:defaults.fontSize;
    if(sv)sv.textContent=(s.fontSize!==undefined?s.fontSize:defaults.fontSize)+'pt';
    if(ff)ff.value=s.fontFamily||defaults.fontFamily;
  }
  var btn=document.getElementById('accessibilityBtn');
  var backdrop=document.getElementById('accessibilityBackdrop');
  var closeBtn=document.getElementById('accessibilityClose');
  var saveBtn=document.getElementById('accSave');
  var resetBtn=document.getElementById('accReset');
  function openModal(){backdrop.style.display='flex';backdrop.setAttribute('aria-hidden','false');document.getElementById('acc-font-size').focus();}
  function closeModal(){backdrop.style.display='none';backdrop.setAttribute('aria-hidden','true');btn.focus();}
  btn.addEventListener('click',function(e){e.stopPropagation();populateUI(getSettings());openModal();});
  closeBtn.addEventListener('click',closeModal);
  backdrop.addEventListener('click',function(e){if(e.target===backdrop)closeModal();});
  saveBtn.addEventListener('click',function(){
    var s={fontSize:Number(document.getElementById('acc-font-size').value),fontFamily:document.getElementById('acc-font-family').value};
    applySettings(s);saveSettings(s);closeModal();
  });
  var slider=document.getElementById('acc-font-size');
  if(slider)slider.addEventListener('input',function(){document.getElementById('acc-font-size-value').textContent=this.value+'pt';});
  resetBtn.addEventListener('click',function(){localStorage.removeItem(KEY);var s=Object.assign({},defaults);applySettings(s);populateUI(s);});
  document.addEventListener('DOMContentLoaded',function(){applySettings(getSettings());});
})();
</script>
</header>
