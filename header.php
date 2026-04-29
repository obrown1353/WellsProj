<?php
date_default_timezone_set('America/New_York');
?>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
<style>
<?php if (empty($tailwind_mode)): ?>
* { box-sizing: border-box; margin: 0; padding: 0; }
<?php endif; ?>

body { 
	font-family: 'Inter', sans-serif; 
	padding-top: 96px; 
	font-size: 14pt; 
}

h2 { 
	font-weight: normal; 
	font-size: 30px; 
}

.extra-info { 
	max-height: 0px; 
	overflow: hidden; 
	transition: max-height 0.3s ease-out; 
	font-size: 14px; color: #444; 
	margin-top: 5px; 
}

.content-box-test { 
	flex: 1 1 370px; 
	max-width: 470px; 
	padding: 10px 10px; 
	display: flex; 
	flex-direction: column; 
	align-items: center; 
	text-align: center; 
	position: relative; 
	cursor: pointer; 
	border: 0.1px solid black; 
	transition: border 0.3s; 
	border-radius: 10px; 
	border-bottom-right-radius: 50px; 
}

.content-box-test:hover { 
	border: 4px solid #fdd05eff; 
}

.full-width-bar { 
	width: 100%; 
	background: rgb(31,31,33); 
	padding: 17px 5%; 
	display: flex; 
	flex-wrap: wrap; 
	justify-content: center; 
	gap: 20px; 
}

.full-width-bar-sub { 
	width: 100%; 
	background: white; 
	padding: 17px 5%; 
	display: flex; 
	flex-wrap: wrap; 
	justify-content: center; 
	gap: 20px; 
}

.content-box { 
	flex: 1 1 280px; 
	max-width: 375px; 
	padding: 10px 2px; 
	display: flex; 
	flex-direction: column; 
	align-items: center; 
	text-align: center; 
	position: relative; 
}

.content-box-sub { 
	flex: 1 1 300px; 
	max-width: 470px; 
	padding: 10px 10px; 
	display: flex; 
	flex-direction: column; 
	align-items: center; 
	text-align: center; 
	position: relative; 
}

.content-box img { 
	width: 100%; 
	height: auto; 
	background: white; 
	border-radius: 5px; 
	border-bottom-right-radius: 50px; 
	border: 0.5px solid #828282; 
}

.content-box-sub img { 
	width: 105%; 
	height: auto; 
	background: white; 
	border-radius: 5px; 
	border-bottom-right-radius: 
	50px; border: 1px solid #828282; 
}

.small-text { 
	position: absolute; 
	top: 20px; 
	left: 30px; 
	font-size: 14px; 
	font-weight: 700; 
	color: #297760ff; 
}

.large-text { 
	position: absolute; 
	top: 40px; 
	left: 30px; 
	font-size: 22px; 
	font-weight: 700; 
	color: black; 
	max-width: 90%; 
}

.large-text-sub { 
	position: absolute; 
	top: 60%; 
	left: 10%; 
	font-size: 22px; 
	font-weight: 700; 
	color: black; 
	max-width: 90%; 
}

.graph-text { 
	position: absolute; 
	top: 75%; 
	left: 10%; 
	font-size: 14px; 
	font-weight: 700; 
	color: #712977ff; 
	max-width: 90%; 
	margin-bottom: 80px; 
}

.navbar {
    width: 100%;
    height: 100px;
    position: fixed;
    top: 0;
    left: 0;
    background: #1a1a1a !important;
    box-shadow: 0px 2px 10px rgba(0,0,0,0.3) !important;
    display: flex !important;
    align-items: center;
    padding: 0 24px;
    z-index: 1000;
    border-bottom: none !important;
    flex-direction: row !important;
}

.left-section { 
	display: flex; 
	align-items: center; 
	gap: 28px; 
}

.logo-container { 
	display: flex; 
	align-items: center; 
}

.logo-container img { 
	width: 64px; 
	height: auto; 
	display: block; 
}

.center-section { 
	display: none !important; 
}

.nav-links { 
	display: flex; 
	gap: 28px; 
}

.nav-links div { 
	font-size: 28px; 
	font-weight: 700; 
	color: white; 
	cursor: pointer; 
}

.right-section { 
	margin-left: auto; 
	display: flex; 
	align-items: center; 
	gap: 16px; 
}

.navbar-row1 { 
	display: flex; 
	align-items: center; 
	width: 100%; 
}

.nav-item { 
	position: relative; 
	cursor: pointer; 
	padding: 4px; 
	transition: color 0.2s; 
}

.dropdown {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    background: #1a1a1a;
    border: 1px solid #0067A2;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border-radius: 10px;
    padding: 8px;
    width: max-content;
    min-width: 160px;
}

.dropdown div {
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 600;
    color: white;
    border-radius: 6px;
    white-space: nowrap;
    transition: background 0.2s;
}

.dropdown div:hover { 
	background: rgba(141,201,247,0.15); 
}

.nav-item:hover .nav-links div,
.nav-item.active .nav-links div { 
	color: #8DC9F7; 
}

.icon { 
	width: 44px; 
	height: 44px; 
	border-radius: 50%; 
	border: 2px solid rgba(141,201,247,0.5); 
	display: flex; 
	align-items: center; 
	justify-content: center; 
	transition: border-color 0.2s, 
	background 0.2s; 
	background: rgba(141,201,247,0.1);
	 cursor: pointer;
}

.icon:hover { 
	border-color: #8DC9F7; 
	background: rgba(141,201,247,0.2); 
}

.icon svg { 
	width: 22px; 
	height: 22px; 
	fill: white; 
}

.nav-link { 
	color: white; 
	text-decoration: none; 
	font-size: 28px; 
	font-weight: 700; 
	transition: color 0.2s; 
}

.nav-link:hover { 
	color: #8DC9F7; 
}

.dropdown-link { 
	color: inherit; 
	text-decoration: none; 
	display: block; 
}



@media (max-width: 900px) {
    body { 
	padding-top: 100px !important; 
    }
    .navbar {
        height: 100px !important;
        flex-direction: row !important;
        align-items: center !important;
        padding: 0 14px !important;
    }

    .navbar-row1 { 
	display: flex !important; 
	align-items: center !important; 
	width: 100% !important; 
    }
    .left-section { 
	gap: 10px; 
	flex: 1; 
    }
    .right-section { 
	margin-left: auto; 
    }
    .nav-links { 
	gap: 16px; 
    }
    .nav-links div { 
	font-size: 22px; 
    }
    .nav-link { 
	font-size: 22px; 
    }
    .logo-container img { 
	width: 46px !important; 
	height: auto !important; 
    }
}

@media (max-width: 900px) {
    .footer { 
	flex-direction: column; 
	align-items: center; 
	text-align: center; 
	padding: 24px 20px; 
	gap: 24px; 
    }
    .footer-left { 
	margin-left: 0 !important; 
	align-items: center; 
    }
    .footer-right { 
	flex-direction: column; 
	align-items: center; 
	gap: 30px; 
	width: 100%; 
    }
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
$common_nav_links = '
    <div class="nav-item"><a href="index.php" class="nav-link">Home</a></div>
    <div class="nav-item"><a href="staffPage.php" class="nav-link">Staff</a></div>';

if (!isset($_SESSION['logged_in']) || $_SESSION['access_level'] === 0) {
echo('
<div class="navbar">
    <div class="navbar-row1">
        <div class="left-section">
            <div class="logo-container">
                <a href="index.php"><img src="images/UMW_Eagles-logo.png" alt="Logo"></a>
            </div>
            <div class="nav-links">
                <div class="nav-item"><a href="index.php" class="nav-link">Home</a></div>
                <div class="nav-item"><a href="results.php" class="nav-link">Search</a></div>
            </div>
        </div>
        <div class="center-section">
            <span class="center-title">Seacobeck Curriculum Lab</span>
            <span class="center-subtitle">University of Mary Washington</span>
        </div>
        <div class="right-section">
            <div class="nav-item">
                <div class="icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
                    <div class="dropdown">
                        <a href="login.php" class="dropdown-link"><div>Log In</div></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>');

} else if ($_SESSION['logged_in'] && $_SESSION['access_level'] >= 1) {
echo('
<div class="navbar">
    <div class="navbar-row1">
        <div class="left-section">
            <div class="logo-container">
                <a href="index.php"><img src="images/UMW_Eagles-logo.png" alt="Logo"></a>
            </div>
            <div class="nav-links">'
        . $common_nav_links .
        '</div>
        </div>
        <div class="center-section">
            <span class="center-title">Seacobeck Curriculum Lab</span>
            <span class="center-subtitle">University of Mary Washington</span>
        </div>
        <div class="right-section">
            <div class="nav-item">
                <div class="icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
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
?>

</header>
