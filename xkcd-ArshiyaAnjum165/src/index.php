<?php
require_once 'functions.php';
session_start();

$message = '';
$emailValue = '';
$showUnsubscribeOption = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: User entered email
    if (isset($_POST['send_code']) && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailValue = $email;
            $_SESSION['email'] = $email;

            // Check if already registered
            $file = __DIR__ . '/registered_emails.txt';
            $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

            if (in_array($email, $emails)) {
                $message = "🔁 Email <strong>$email</strong> is already registered.<br>Would you like to unsubscribe?";
                $showUnsubscribeOption = true;
            } else {
                $code = generateVerificationCode();
                $_SESSION['verification_code'] = $code;
                if (sendVerificationEmail($email, $code)) {
                    $message = "📩 Verification code sent to <strong>$email</strong>.";
                } else {
                    $message = "❌ Failed to send email. (Check SMTP setup)";
                }
            }
        } else {
            $message = "❌ Invalid email format.";
        }
    }

    // Step 2: User entered code
    if (isset($_POST['verify_code']) && isset($_POST['verification_code'])) {
        $codeEntered = trim($_POST['verification_code']);
        $emailValue = $_SESSION['email'] ?? '';
        if (isset($_SESSION['verification_code']) && $codeEntered === $_SESSION['verification_code']) {
            registerEmail($emailValue);
            unset($_SESSION['verification_code']);
            header("Location: unsubscribe.php?email=" . urlencode($emailValue));
            exit();
        } else {
            $message = "❌ Incorrect verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>📬 Email Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #fce4ec);
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
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .icon {
            font-size: 40px;
            color: #00acc1;
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
            border-color: #00acc1;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #00bcd4, #2196f3);
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        button:hover {
            background: linear-gradient(90deg, #039be5, #00acc1);
            transform: scale(1.02);
        }
        .message {
            margin-top: 15px;
            font-weight: 600;
            color: #4a148c;
            animation: fadeIn 1s ease-in-out;
        }
        .unsubscribe-btn {
            display: inline-block;
            margin-top: 12px;
            background: linear-gradient(90deg, #ef5350, #e53935);
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
        }
        .unsubscribe-btn:hover {
            background: linear-gradient(90deg, #e53935, #c62828);
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
    <div class="icon">📧</div>
    <h2>Email Verification</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($emailValue) ?>" required>
        <button type="submit" name="send_code">Send Code</button>
    </form>

    <form method="POST">
        <input type="text" name="verification_code" placeholder="Enter verification code" maxlength="6" required>
        <button type="submit" name="verify_code">Verify</button>
    </form>

    <div class="message"><?= $message ?></div>

    <?php if ($showUnsubscribeOption && $emailValue): ?>
        <a class="unsubscribe-btn" href="unsubscribe.php?email=<?= urlencode($emailValue) ?>">Yes, Unsubscribe</a>
    <?php endif; ?>
</div>
</body>
</html>
