<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
.footer { 
    width: 100%; 
    background: #8DC9F7; 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    padding: 20px 50px; 
    flex-wrap: wrap; 
}

.footer-right { 
    display: flex; 
    gap: 50px; 
    flex-wrap: wrap; 
    justify-content: flex-start;
}


.footer-left { 
    display: flex; 
    flex-direction: column; 
    align-items: flex-end; 
    margin-left: auto;
}

.footer-logo { 
    width: 115px; 
    margin-bottom: 0px; 
}

.social-icons { 
    display: flex; 
    gap: 15px; 
}

.social-icons a { 
    color: white; 
    font-size: 20px; 
    transition: color 0.3s ease;
}

.social-icons a:hover { 
    color: #dcdcdc; 
}


.footer-section { 
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
    color: white; 
    font-family: Inter, sans-serif; 
    font-size: 16px; 
    font-weight: 500; 
}

.footer-topic { 
    font-size: 18px; 
    font-weight: bold; 
}


.footer a {
    color: white; 
    text-decoration: none; 
    transition: background 0.2s ease, color 0.2s ease; 
    padding: 5px 10px; 
    border-radius: 5px; 
}

.footer a:hover { 
    background: rgba(255, 255, 255, 0.1); 
    color: #dcdcdc; 
}


@media (max-width: 768px) {
    .footer {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .footer-left {
        margin-left: 0;
        align-items: center;
    }

    .footer-right {
        justify-content: center;
        margin-bottom: 20px;
        gap: 30px;
    }
}
</style>
</head>

<body>

<footer class="footer" style="margin-top: 60px;">
    
    
    <div class="footer-right">
        
        <div class="footer-section">
            <div class="footer-topic">Connect</div>
            <a href="https://www.facebook.com/profile.php?id=100086673730177#">Facebook</a>
            <a href="https://www.instagram.com/umw_coe/">Instagram</a>
            <a href="https://education.umw.edu/">Main Website</a>
        </div>

        <div class="footer-section">
            <div class="footer-topic">Contact Us</div>
            <a href="mailto:mwells@umw.edu">mwells@umw.edu</a>
            <a href="tel:5406541290">(540) 654-1290</a>
        </div>

    </div>

    
    <div class="footer-left">
        <img src="images/UMW_Eagles-logo.png" alt="Logo" class="footer-logo">
        
        <div class="social-icons">
            <a href="https://www.facebook.com/profile.php?id=100086673730177#"><i class="fab fa-facebook"></i></a>
            <a href="https://www.instagram.com/umw_coe/"><i class="fab fa-instagram"></i></a>
            <a href="https://education.umw.edu/"><i class="fas fa-globe"></i></a>
        </div>
    </div>

</footer>

</body>
</html>