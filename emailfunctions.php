<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/PHPMailer/Exception.php';
require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';

function sendEmail($to_email, $to_name, $subject, $html_body, $plain_body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host         = 'smtp.gmail.com';
        $mail->SMTPAuth     = true;
        $mail->Username     = 'fortega112005@gmail.com';
        $mail->Password     = 'bodm kjru kyhp rtmt';
        $mail->SMTPSecure   = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port         = 587;
        $mail->Sender       = 'fortega112005@gmail.com';
        $mail->Hostname     = 'smtp.gmail.com';
        $mail->Helo         = gethostname() ?: 'mail.seacobbecklibrary.umw.edu';
        $mail->SMTPOptions  = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->XMailer      = ' ';
        $mail->addCustomHeader('X-Priority', '3');
        $mail->addCustomHeader('Precedence', 'bulk');
        $mail->setFrom('fortega112005@gmail.com', 'Seacobeck Library');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->CharSet  = 'UTF-8';
        $mail->Encoding = 'quoted-printable';
        $mail->Subject  = $subject;
        $mail->Body     = $html_body;
        $mail->AltBody  = $plain_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error to {$to_email}: " . $mail->ErrorInfo);
        return false;
    }
}


function _emailHeader() {
    return <<<HTML
<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Seacobeck Library</title>
<!--[if mso]><noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#ffffff;font-family:Georgia,'Times New Roman',serif;color:#000000;">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff;padding:30px 0;">
<tr><td align="center">
<table width="620" cellpadding="0" cellspacing="0" role="presentation" style="max-width:620px;width:100%;border-radius:14px;overflow:hidden;border:2px solid #1a3a7c;box-shadow:0 6px 24px rgba(13,31,78,0.15);">

<!-- Header -->
<tr><td style="background-color:#ffffff;padding:32px 40px 24px;text-align:center;">
  <div style="display:inline-block;background-color:#bde0f7;border:3px solid #1a3a7c;border-radius:50%;width:66px;height:66px;line-height:66px;font-size:34px;text-align:center;margin-bottom:14px;">&#128218;</div>
  <h1 style="color:#000000;font-size:22px;letter-spacing:2px;text-transform:uppercase;margin:0 0 6px 0;">Seacobeck Library</h1>
  <p style="color:#000000;font-size:13px;letter-spacing:1px;margin:0;">University of Mary Washington &nbsp;&middot;&nbsp; Fredericksburg, VA</p>
</td></tr>

<tr><td style="background:linear-gradient(90deg,#0d1f4e,#1a3a7c,#5b9bd5,#bde0f7,#5b9bd5,#1a3a7c,#0d1f4e);height:6px;font-size:0;">&nbsp;</td></tr>
HTML;
}

function _emailFooter() {
    return <<<HTML
<tr><td style="background:linear-gradient(90deg,#0d1f4e,#1a3a7c,#5b9bd5,#bde0f7,#5b9bd5,#1a3a7c,#0d1f4e);height:6px;font-size:0;">&nbsp;</td></tr>

<!-- Footer -->
<tr><td style="background-color:#ffffff;padding:24px 40px;text-align:center;border-top:3px solid #1a3a7c;">
  <p style="color:#000000;font-size:12.5px;line-height:2;margin:0;">
    Questions? Contact <strong style="color:#000000;">Dr. Mellisa Wells</strong><br/>
    <a href="mailto:mwells@umw.edu" style="color:#000000;text-decoration:underline;">mwells@umw.edu</a>
  </p>
  <p style="color:#000000;font-size:11.5px;margin:8px 0 0 0;">Seacobeck Library &middot; 1301 College Ave &middot; Fredericksburg, VA 22401</p>
</td></tr>

</table>
</td></tr>
</table>
</body></html>
HTML;
}

function _summaryTable($title, $name, $email_addr) {
    return <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#ffffff;border:1px solid #5b9bd5;border-left:6px solid #0d1f4e;border-radius:8px;margin:20px 0;">
  <tr>
    <td style="padding:11px 14px 11px 18px;width:120px;vertical-align:middle;border-bottom:1px solid #bde0f7;background-color:#bde0f7;">
      <span style="color:#000000;font-weight:bold;white-space:nowrap;font-size:14px;">&#128218; Item</span>
    </td>
    <td style="padding:11px 18px 11px 14px;vertical-align:middle;border-bottom:1px solid #bde0f7;">
      <span style="color:#000000;font-size:14px;">{$title}</span>
    </td>
  </tr>
  <tr>
    <td style="padding:11px 14px 11px 18px;width:120px;vertical-align:middle;border-bottom:1px solid #bde0f7;background-color:#bde0f7;">
      <span style="color:#000000;font-weight:bold;white-space:nowrap;font-size:14px;">&#128100; Name</span>
    </td>
    <td style="padding:11px 18px 11px 14px;vertical-align:middle;border-bottom:1px solid #bde0f7;">
      <span style="color:#000000;font-size:14px;">{$name}</span>
    </td>
  </tr>
  <tr>
    <td style="padding:11px 14px 11px 18px;width:120px;vertical-align:middle;background-color:#bde0f7;">
      <span style="color:#000000;font-weight:bold;white-space:nowrap;font-size:14px;">&#128231; Email</span>
    </td>
    <td style="padding:11px 18px 11px 14px;vertical-align:middle;">
      <span style="color:#000000;font-size:14px;">{$email_addr}</span>
    </td>
  </tr>
</table>
HTML;
}

function _emailBody($badge_bg, $badge_border, $badge_label, $badge_icon, $badge_date, $badge_subtext, $name, $intro, $summary, $outro) {
    return <<<HTML
<tr><td style="background-color:#ffffff;padding:36px 44px;">
  <p style="font-size:22px;color:#000000;font-weight:bold;margin:0 0 14px 0;border-left:5px solid #1a3a7c;padding-left:12px;">Hi {$name}!</p>
  <p style="font-size:15px;color:#000000;line-height:1.8;margin:0 0 4px 0;">{$intro}</p>
  {$summary}
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:{$badge_bg};border-radius:10px;border:2px solid {$badge_border};margin:24px 0 20px 0;">
    <tr><td style="padding:26px;text-align:center;">
      <p style="color:#000000;font-size:12px;letter-spacing:2px;text-transform:uppercase;margin:0 0 10px 0;font-weight:600;">{$badge_icon} &nbsp; {$badge_label}</p>
      <p style="color:#000000;font-size:28px;font-weight:bold;letter-spacing:1px;margin:0 0 10px 0;">{$badge_date}</p>
      <p style="color:#000000;font-size:12px;margin:0;">{$badge_subtext}</p>
    </td></tr>
  </table>
  <p style="font-size:15px;color:#000000;line-height:1.8;margin:0;">{$outro}</p>
  <div style="margin-top:28px;font-size:14.5px;color:#000000;border-top:3px solid #5b9bd5;padding-top:18px;">
    Warm regards,<br/>
    <strong style="color:#000000;">The Seacobeck Library Team</strong><br/>
    University of Mary Washington
  </div>
</td></tr>
HTML;
}


// Checkout confirmation
function checkoutEmailHTML($name, $title, $email_addr, $due_date) {
    $header  = _emailHeader();
    $summary = _summaryTable($title, $name, $email_addr);
    $footer  = _emailFooter();
    $body = _emailBody(
        '#bde0f7', '#1a3a7c',
        'Please Return By', '&#128197;', $due_date,
        'Please return on or before this date. Checkout period is 2 weeks.',
        $name,
        'We have received your confirmation! Thank you for visiting <strong>Seacobeck Library</strong>. Your checkout has been recorded &mdash; here is a summary.',
        $summary,
        'If you have any questions, please do not hesitate to reach out &mdash; we are always happy to help!'
    );
    return "{$header}{$body}{$footer}";
}

function checkoutEmailPlain($name, $title, $email_addr, $due_date) {
    return "Hi {$name},\n\nThank you for checking out from Seacobeck Library!\n\nItem: {$title}\nName: {$name}\nEmail: {$email_addr}\nDue Date: {$due_date}\n\nPlease return the item on or before the due date. The checkout period is 2 weeks.\n\nWarm regards,\nThe Seacobeck Library Team\nUniversity of Mary Washington\nmwells@umw.edu";
}

// One-week reminder
function reminderEmailHTML($name, $title, $email_addr, $due_date) {
    $header  = _emailHeader();
    $summary = _summaryTable($title, $name, $email_addr);
    $footer  = _emailFooter();
    $body = _emailBody(
        '#fff3cc', '#f5c842',
        'Due Date Approaching', '&#9888;&#65039;', $due_date,
        'You have 1 week remaining — please return the item on or before this date.',
        $name,
        'This is a friendly reminder that your borrowed item from <strong>Seacobeck Library</strong> is due in <strong>one week</strong>. Please plan to return it on time!',
        $summary,
        'If you have already returned this item, please disregard this message. If you have any questions, we are happy to help!'
    );
    return "{$header}{$body}{$footer}";
}

function reminderEmailPlain($name, $title, $email_addr, $due_date) {
    return "Hi {$name},\n\nThis is a friendly reminder that your item from Seacobeck Library is due in ONE WEEK.\n\nItem: {$title}\nName: {$name}\nEmail: {$email_addr}\nDue Date: {$due_date}\n\nPlease return the item on or before the due date.\n\nWarm regards,\nThe Seacobeck Library Team\nUniversity of Mary Washington\nmwells@umw.edu";
}


// Due today
function dueTodayEmailHTML($name, $title, $email_addr, $due_date) {
    $header  = _emailHeader();
    $summary = _summaryTable($title, $name, $email_addr);
    $footer  = _emailFooter();
    $body = _emailBody(
        '#ffe8d0', '#f97316',
        'Due Today', '&#128197;', $due_date,
        'Please return this item to the library today to avoid an overdue notice.',
        $name,
        'This is a reminder that your borrowed item from <strong>Seacobeck Library</strong> is <strong>due today</strong>. Please return it before the library closes!',
        $summary,
        'If you have already returned this item, please disregard this message. If you have any questions, we are happy to help!'
    );
    return "{$header}{$body}{$footer}";
}

function dueTodayEmailPlain($name, $title, $email_addr, $due_date) {
    return "Hi {$name},\n\nThis is a reminder that your item from Seacobeck Library is DUE TODAY.\n\nItem: {$title}\nName: {$name}\nEmail: {$email_addr}\nDue Date: {$due_date}\n\nPlease return the item before the library closes today.\n\nWarm regards,\nThe Seacobeck Library Team\nUniversity of Mary Washington\nmwells@umw.edu";
}


// Overdue 
function overdueEmailHTML($name, $title, $email_addr, $due_date) {
    $header  = _emailHeader();
    $summary = _summaryTable($title, $name, $email_addr);
    $footer  = _emailFooter();
    $body = _emailBody(
        '#ffe0e0', '#f87171',
        'Item Overdue', '&#128680;', "Was Due: {$due_date}",
        'Please return this item to the library immediately.',
        $name,
        'Our records show that the following item from <strong>Seacobeck Library</strong> was due on <strong>' . $due_date . '</strong> and has not yet been returned. Please return it as soon as possible.',
        $summary,
        'If you have already returned this item, please disregard this notice. Otherwise, please return it at your earliest convenience or contact us with any concerns.'
    );
    return "{$header}{$body}{$footer}";
}

function overdueEmailPlain($name, $title, $email_addr, $due_date) {
    return "Hi {$name},\n\nOur records show that the following item was due on {$due_date} and has not yet been returned.\n\nItem: {$title}\nName: {$name}\nEmail: {$email_addr}\nDue Date: {$due_date}\n\nPlease return the item to Seacobeck Library as soon as possible.\n\nWarm regards,\nThe Seacobeck Library Team\nUniversity of Mary Washington\nmwells@umw.edu";
}


// Return confirmation
function returnEmailHTML($name, $title, $email_addr) {
    $header  = _emailHeader();
    $summary = _summaryTable($title, $name, $email_addr);
    $footer  = _emailFooter();
    return <<<HTML
{$header}
<tr><td style="background-color:#ffffff;padding:36px 44px;">
  <p style="font-size:22px;color:#000000;font-weight:bold;margin:0 0 14px 0;border-left:5px solid #1a3a7c;padding-left:12px;">Hi {$name}!</p>
  <p style="font-size:15px;color:#000000;line-height:1.8;margin:0 0 4px 0;">
    Thank you for returning your item to <strong>Seacobeck Library</strong>!
    We have recorded your return &mdash; here is a summary.
  </p>
  {$summary}
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#bde0f7;border-radius:10px;border:2px solid #1a3a7c;margin:24px 0 20px 0;">
    <tr><td style="padding:26px;text-align:center;">
      <p style="color:#000000;font-size:22px;font-weight:bold;margin:0;">&#10003; Item Successfully Returned</p>
    </td></tr>
  </table>
  <p style="font-size:15px;color:#000000;line-height:1.8;margin:0;">
    If you have any questions, please do not hesitate to reach out &mdash; we are always happy to help!
  </p>
  <div style="margin-top:28px;font-size:14.5px;color:#000000;border-top:3px solid #5b9bd5;padding-top:18px;">
    Warm regards,<br/>
    <strong style="color:#000000;">The Seacobeck Library Team</strong><br/>
    University of Mary Washington
  </div>
</td></tr>
{$footer}
HTML;
}

function returnEmailPlain($name, $title, $email_addr) {
    return "Hi {$name},\n\nThank you for returning your item to Seacobeck Library!\n\nItem: {$title}\nName: {$name}\nEmail: {$email_addr}\n\nYour return has been successfully recorded.\n\nWarm regards,\nThe Seacobeck Library Team\nUniversity of Mary Washington\nmwells@umw.edu";
}