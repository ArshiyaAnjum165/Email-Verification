<?php

function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $filtered = array_filter($emails, fn($e) => trim($e) !== trim($email));

    file_put_contents($file, implode(PHP_EOL, $filtered) . PHP_EOL);
}

function sendVerificationEmail($email, $code) {
    $subject = 'Your Verification Code';
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html\r\n";

    return mail($email, $subject, $message, $headers);
}

function sendXKCDComicToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (empty($emails)) return;

    // Get latest XKCD comic
    $xkcd = @file_get_contents("https://xkcd.com/info.0.json");
    if (!$xkcd) return;

    $data = json_decode($xkcd, true);
    if (!$data) return;

    $title = $data['safe_title'];
    $img = $data['img'];
    $alt = $data['alt'];
    $url = "https://xkcd.com/" . $data['num'];

    $html = "<h2>$title</h2>
             <img src='$img' alt='$alt' style='max-width:100%;'><p>$alt</p>
             <p>View on <a href='$url'>$url</a></p>";

    $subject = "🗞️ Your Daily XKCD Comic";

    foreach ($emails as $email) {
        $unsubscribe = "http://yourdomain.com/src/unsubscribe.php?email=" . urlencode($email);
        $body = $html . "<p><a href='$unsubscribe'>Unsubscribe</a></p>";

        $headers = "From: no-reply@example.com\r\n";
        $headers .= "Content-Type: text/html\r\n";

        mail($email, $subject, $body, $headers);
    }
}
