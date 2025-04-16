<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
}

$email_id = $_GET['id'];
$stmt = $conn->prepare("SELECT emails.*, users.name as sender_name FROM emails JOIN users ON emails.sender_id = users.id WHERE emails.id = ?");
$stmt->execute([$email_id]);
$email = $stmt->fetch();

if (!$email) {
    echo "<script>window.location.href='inbox.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail - View Email</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background-color: #f2f2f2;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .email-header {
            border-bottom: 1px solid #dadce0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .email-header h2 {
            margin: 0;
            font-size: 20px;
            color: #202124;
        }
        .email-meta {
            color: #5f6368;
            font-size: 14px;
            margin-top: 10px;
        }
        .email-body {
            font-size: 16px;
            color: #202124;
            line-height: 1.5;
        }
        .back-btn {
            background-color: #1a73e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .back-btn:hover {
            background-color: #1557b0;
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="back-btn" onclick="window.location.href='inbox.php'">Back to Inbox</button>
        <div class="email-header">
            <h2><?php echo htmlspecialchars($email['subject']); ?></h2>
            <div class="email-meta">
                From: <?php echo htmlspecialchars($email['sender_name']); ?> <br>
                Date: <?php echo date('M d, Y H:i', strtotime($email['created_at'])); ?>
            </div>
        </div>
        <div class="email-body">
            <?php echo nl2br(htmlspecialchars($email['body'])); ?>
        </div>
    </div>
</body>
</html>
