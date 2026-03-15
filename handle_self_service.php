<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================
// TEST MODE: set to true to skip database calls
// and just test email sending. Set to false for live.
$TEST_MODE = true;
// ============================================

include_once "database/dbMaterials.php";
include_once "database/dbCheckout.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

$id         = $_POST["id"];
$first_name = $_POST["first_name"] ?? '';
$last_name  = $_POST["last_name"]  ?? '';
$email      = $_POST["email"]      ?? '';
$full_name  = trim($first_name . ' ' . $last_name);

$checkout_date = date('Y-m-d H:i:s');
$due_date      = date('Y-m-d', strtotime($checkout_date . ' + 14 days'));
$due_date_nice = date('l, F j, Y', strtotime($due_date));

$material = fetch_material_by_id($id);
$title    = $material ? $material->getName() : 'Unknown Item';

$LOGO_URL = "https://umwathletics.com/images/logos/site/site.png";

function sendEmail($to_email, $to_name, $subject, $html_body, $plain_body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'fortega112005@gmail.com';
        $mail->Password   = 'bodm kjru kyhp rtmt';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('fortega112005@gmail.com', 'Seacobeck Library');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Subject  = $subject;
        $mail->Body     = $html_body;
        $mail->AltBody  = $plain_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}

function checkoutEmailHTML($name, $title, $email_addr, $due_date, $logo_url) {
    return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#e8eef5;font-family:Georgia,'Times New Roman',serif;color:#2a2a2a;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#e8eef5;padding:30px 0;">
    <tr><td align="center">
      <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.18);">
        <tr><td style="background:linear-gradient(160deg,#0d1f4e,#1a3a7c);padding:36px 40px 28px;text-align:center;">
          <img src="{$logo_url}" alt="UMW Logo" height="80" style="margin-bottom:14px;display:block;margin-left:auto;margin-right:auto;" />
          <h1 style="color:#ffffff;font-size:22px;letter-spacing:2px;text-transform:uppercase;margin:0 0 4px 0;">Seacobeck Library</h1>
          <p style="color:#a8c4e0;font-size:13px;letter-spacing:1px;margin:0;">University of Mary Washington &nbsp;&middot;&nbsp; Fredericksburg, VA</p>
        </td></tr>
        <tr><td style="background:linear-gradient(90deg,#0d1f4e,#5b9bd5,#bde0f7,#5b9bd5,#0d1f4e);height:5px;font-size:0;">&nbsp;</td></tr>
        <tr><td style="background-color:#ffffff;padding:40px 44px;">
          <p style="font-size:22px;color:#1a3a7c;font-weight:bold;margin:0 0 14px 0;">Hi {$name}!</p>
          <p style="font-size:15px;color:#444444;line-height:1.8;margin:0 0 16px 0;">We have received your confirmation! Thank you for visiting <strong>Seacobeck Library</strong>. Your checkout has been recorded &mdash; here is a summary.</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5fc;border:1px solid #c2d4ed;border-left:6px solid #1a3a7c;border-radius:8px;margin:24px 0;">
            <tr><td style="padding:12px 26px;border-bottom:1px dashed #c2d4ed;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128218; Item</span>
              <span style="color:#333333;">{$title}</span>
            </td></tr>
            <tr><td style="padding:12px 26px;border-bottom:1px dashed #c2d4ed;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128100; Name</span>
              <span style="color:#333333;">{$name}</span>
            </td></tr>
            <tr><td style="padding:12px 26px;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128231; Email</span>
              <span style="color:#333333;">{$email_addr}</span>
            </td></tr>
          </table>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#0d1f4e,#1a3a7c);border-radius:10px;border:2px solid #5b9bd5;margin:28px 0 20px 0;">
            <tr><td style="padding:26px;text-align:center;">
              <p style="color:#a8c4e0;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 10px 0;">&#128197; &nbsp; Please Return By</p>
              <p style="color:#ffffff;font-size:28px;font-weight:bold;letter-spacing:1px;margin:0 0 10px 0;">{$due_date}</p>
              <p style="color:#f5c4bb;font-size:12px;margin:0;">Please return on or before this date.</p>
            </td></tr>
          </table>
          <p style="font-size:15px;color:#444444;line-height:1.8;margin:0 0 16px 0;">If you have any questions, please do not hesitate to reach out &mdash; we are always happy to help!</p>
          <div style="margin-top:28px;font-size:14.5px;color:#555555;border-top:1px solid #dde8f5;padding-top:20px;">
            Warm regards,<br/>
            <strong style="color:#1a3a7c;">The Seacobeck Library Team</strong><br/>
            University of Mary Washington
          </div>
        </td></tr>
        <tr><td style="background:linear-gradient(90deg,#0d1f4e,#5b9bd5,#bde0f7,#5b9bd5,#0d1f4e);height:5px;font-size:0;">&nbsp;</td></tr>
        <tr><td style="background:linear-gradient(160deg,#0d1f4e,#1a3a7c);padding:24px 40px;text-align:center;">
          <p style="color:#a8c4e0;font-size:12.5px;line-height:2;margin:0;">
            Questions? Contact <strong style="color:#bde0f7;">Dr. Mellisa Wells</strong><br/>
            <a href="mailto:mwells@umw.edu" style="color:#bde0f7;text-decoration:underline;">mwells@umw.edu</a>
          </p>
          <p style="color:#a8c4e0;font-size:11.5px;margin:10px 0 0 0;">Seacobeck Library &middot; 1301 College Ave &middot; Fredericksburg, VA 22401</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
HTML;
}

function returnEmailHTML($name, $title, $email_addr, $logo_url) {
    return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#e8eef5;font-family:Georgia,'Times New Roman',serif;color:#2a2a2a;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#e8eef5;padding:30px 0;">
    <tr><td align="center">
      <table width="620" cellpadding="0" cellspacing="0" style="max-width:620px;border-radius:14px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.18);">
        <tr><td style="background:linear-gradient(160deg,#0d1f4e,#1a3a7c);padding:36px 40px 28px;text-align:center;">
          <img src="{$logo_url}" alt="UMW Logo" height="80" style="margin-bottom:14px;display:block;margin-left:auto;margin-right:auto;" />
          <h1 style="color:#ffffff;font-size:22px;letter-spacing:2px;text-transform:uppercase;margin:0 0 4px 0;">Seacobeck Library</h1>
          <p style="color:#a8c4e0;font-size:13px;letter-spacing:1px;margin:0;">University of Mary Washington &nbsp;&middot;&nbsp; Fredericksburg, VA</p>
        </td></tr>
        <tr><td style="background:linear-gradient(90deg,#0d1f4e,#5b9bd5,#bde0f7,#5b9bd5,#0d1f4e);height:5px;font-size:0;">&nbsp;</td></tr>
        <tr><td style="background-color:#ffffff;padding:40px 44px;">
          <p style="font-size:22px;color:#1a3a7c;font-weight:bold;margin:0 0 14px 0;">Hi {$name}!</p>
          <p style="font-size:15px;color:#444444;line-height:1.8;margin:0 0 16px 0;">Thank you for returning your item to <strong>Seacobeck Library</strong>! We have recorded your return &mdash; here is a summary.</p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f5fc;border:1px solid #c2d4ed;border-left:6px solid #1a3a7c;border-radius:8px;margin:24px 0;">
            <tr><td style="padding:12px 26px;border-bottom:1px dashed #c2d4ed;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128218; Item</span>
              <span style="color:#333333;">{$title}</span>
            </td></tr>
            <tr><td style="padding:12px 26px;border-bottom:1px dashed #c2d4ed;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128100; Name</span>
              <span style="color:#333333;">{$name}</span>
            </td></tr>
            <tr><td style="padding:12px 26px;">
              <span style="color:#1a3a7c;font-weight:bold;display:inline-block;width:120px;">&#128231; Email</span>
              <span style="color:#333333;">{$email_addr}</span>
            </td></tr>
          </table>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:linear-gradient(135deg,#0d1f4e,#1a3a7c);border-radius:10px;border:2px solid #5b9bd5;margin:28px 0 20px 0;">
            <tr><td style="padding:26px;text-align:center;">
              <p style="color:#ffffff;font-size:22px;font-weight:bold;margin:0;">&#10003; Item Successfully Returned</p>
            </td></tr>
          </table>
          <p style="font-size:15px;color:#444444;line-height:1.8;margin:0 0 16px 0;">If you have any questions, please do not hesitate to reach out &mdash; we are always happy to help!</p>
          <div style="margin-top:28px;font-size:14.5px;color:#555555;border-top:1px solid #dde8f5;padding-top:20px;">
            Warm regards,<br/>
            <strong style="color:#1a3a7c;">The Seacobeck Library Team</strong><br/>
            University of Mary Washington
          </div>
        </td></tr>
        <tr><td style="background:linear-gradient(90deg,#0d1f4e,#5b9bd5,#bde0f7,#5b9bd5,#0d1f4e);height:5px;font-size:0;">&nbsp;</td></tr>
        <tr><td style="background:linear-gradient(160deg,#0d1f4e,#1a3a7c);padding:24px 40px;text-align:center;">
          <p style="color:#a8c4e0;font-size:12.5px;line-height:2;margin:0;">
            Questions? Contact <strong style="color:#bde0f7;">Dr. Mellisa Wells</strong><br/>
            <a href="mailto:mwells@umw.edu" style="color:#bde0f7;text-decoration:underline;">mwells@umw.edu</a>
          </p>
          <p style="color:#a8c4e0;font-size:11.5px;margin:10px 0 0 0;">Seacobeck Library &middot; 1301 College Ave &middot; Fredericksburg, VA 22401</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
HTML;
}

// Handle Checkout
if (isset($_POST['Checkout'])) {
    if ($material->canBeCheckedOut()){
      $success = new_checkout($id, $first_name, $last_name, $email, $checkout_date, $due_date);
      if ($success) {
          self_service_update($id, true);
          $html  = checkoutEmailHTML($full_name, $title, $email, $due_date_nice, $LOGO_URL);
          $plain = "Hi $full_name,\n\nWe have received your confirmation!\n\nItem: $title\nReturn By: $due_date_nice\n\nWarm regards,\nThe Seacobeck Library Team";
          $emailSent = sendEmail($email, $full_name, 'Seacobeck Library - Checkout Confirmation', $html, $plain);
          $status = $emailSent ? 'checkout_success' : 'checkout_no_email';
      } else {
          $status = 'checkout_fail';
      } 
    } else {
      $status = 'cant_checkout';
    }
    
// Handle Return
} else if (isset($_POST['Return'])) {
    if ($material->canBeReturned()){
      $success = remove_checkout($id, $email);
      if ($success) {
          self_service_update($id, false);
          $html  = returnEmailHTML($full_name, $title, $email, $LOGO_URL);
          $plain = "Hi $full_name,\n\nThank you for returning: $title.\n\nWarm regards,\nThe Seacobeck Library Team";
          $emailSent = sendEmail($email, $full_name, 'Seacobeck Library - Return Confirmation', $html, $plain);
          $status = $emailSent ? 'return_success' : 'return_no_email';
      } else {
          $status = 'return_fail';
      }
    } else {
      $status = 'cant_return';
    }
}

header('Location: self_service.php?status=' . ($status ?? 'unknown'));
die();
?>