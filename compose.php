<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_email = $_POST['recipient'];
    $subject = $_POST['subject'];
    $body = $_POST['body'];
    $sender_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$recipient_email]);
    $recipient = $stmt->fetch();

    if ($recipient) {
        $stmt = $conn->prepare("INSERT INTO emails (sender_id, recipient_id, subject, body) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sender_id, $recipient['id'], $subject, $body]);
        echo "<script>window.location.href='inbox.php?folder=sent';</script>";
    } else {
        $error = "Recipient not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail - Compose</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .compose-container {
            background: white;
            width: 600px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .compose-header {
            background: #f1f3f4;
            padding: 10px;
            font-size: 16px;
            color: #202124;
            border-bottom: 1px solid #dadce0;
        }
        .compose-body {
            padding: 20px;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            width: 100%;
            height: 300px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            resize: none;
        }
        .send-btn {
            background-color: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .send-btn:hover {
            background-color: #1557b0;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="compose-container">
        <div class="compose-header">New Message</div>
        <div class="compose-body">
            <?php if (isset($error)) { echo "<p class='error'>$error</p>"; } ?>
            <form method="POST" action="">
                <input type="email" name="recipient" placeholder="To" required>
                <input type="text" name="subject" placeholder="Subject">
                <textarea name="body" placeholder="Compose email"></textarea>
                <input type="submit" value="Send" class="send-btn">
            </form>
        </div>
    </div>
</body>
</html>
