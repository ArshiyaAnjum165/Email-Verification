<?php
require_once 'functions.php';
session_start();

$message = '';
$emailValue = '';
$showVerification = false;
$unsubscribed = false;

// Pre-fill email from URL
if (isset($_GET['email'])) {
    $emailValue = trim($_GET['email']);
    $_SESSION['unsubscribe_email'] = $emailValue;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['unsubscribe_email'] = $email;
            $emailValue = $email;

            $code = generateVerificationCode();
            $_SESSION['unsubscribe_code'] = $code;

            $subject = 'Confirm Your Unsubscription';
            $body = "<p>To confirm your unsubscription, use the verification code below:</p>
                     <h2 style='color:#d32f2f;'>$code</h2>
                     <p>If you didn’t request this, you can ignore this email.</p>";
            $headers = "From: no-reply@example.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";

            mail($email, $subject, $body, $headers);
            $message = "📩 A 6-digit code was sent to <strong>$email</strong>.";
            $showVerification = true;
        } else {
            $message = "❌ Invalid email format.";
        }
    }

    if (isset($_POST['unsubscribe_verification_code'])) {
        $codeEntered = trim($_POST['unsubscribe_verification_code']);
        $emailValue = $_SESSION['unsubscribe_email'] ?? '';
        $showVerification = true;

        if (isset($_SESSION['unsubscribe_code']) && $codeEntered === $_SESSION['unsubscribe_code']) {
            unsubscribeEmail($emailValue);
            unset($_SESSION['unsubscribe_code']);
            $unsubscribed = true;
            $showVerification = false;
        } else {
            $message = "❌ Incorrect verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe - GitHub Timeline</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ffebee, #e1f5fe);
            background-image: url('https://www.transparenttextures.com/patterns/stardust.png');
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            animation: fadeIn 1.5s ease-in;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 440px;
            text-align: center;
            animation: slideUp 0.8s ease-out;
        }
        .form-container h2 {
            color: #c62828;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 40px;
            color: #e53935;
            margin-bottom: 15px;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: border 0.3s ease;
        }
        input:focus {
            border-color: #e53935;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #e53935, #d32f2f);
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #c62828, #b71c1c);
            transform: scale(1.02);
        }
        .message {
            margin-top: 15px;
            font-weight: 600;
            color: #2e2e2e;
            animation: fadeIn 1s ease-in-out;
        }
        .thankyou {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2e7d32;
        }
        a.button-link {
            display: inline-block;
            margin-top: 10px;
            background: linear-gradient(90deg, #42a5f5, #1e88e5);
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 10px;
            transition: 0.3s;
        }
        a.button-link:hover {
            background: linear-gradient(90deg, #1e88e5, #1565c0);
        }
        .btn-group {
            margin-top: 10px;
        }
        @keyframes slideUp {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
<div class="form-container">
    <div class="icon">📤</div>
    <h2>Unsubscribe</h2>

    <?php if ($unsubscribed): ?>
        <div class="thankyou">✅ You have been unsubscribed.<br>Thanks for your time! 😊</div>
        <div class="btn-group">
            <a class="button-link" href="index.php">Subscribe Again</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <input type="email" name="unsubscribe_email" placeholder="Enter your email"
                   value="<?= htmlspecialchars($emailValue) ?>" <?= $showVerification ? 'readonly' : '' ?> required>
            <?php if (!$showVerification): ?>
                <button type="submit">Send Verification Code</button>
            <?php endif; ?>
        </form>

        <?php if ($showVerification): ?>
            <form method="POST">
                <input type="text" name="unsubscribe_verification_code" placeholder="Enter 6-digit code" maxlength="6" required>
                <button type="submit">Confirm Unsubscribe</button>
            </form>
        <?php endif; ?>

        <div class="btn-group">
            <a class="button-link" href="index.php">⬅️ Back to Subscribe</a>
        </div>

        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>
</body>
</html>
