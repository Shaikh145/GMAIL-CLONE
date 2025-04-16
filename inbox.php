<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='index.php';</script>";
}

$user_id = $_SESSION['user_id'];
$folder = isset($_GET['folder']) ? $_GET['folder'] : 'inbox';

if ($folder == 'inbox') {
    $stmt = $conn->prepare("SELECT emails.*, users.name as sender_name FROM emails JOIN users ON emails.sender_id = users.id WHERE emails.recipient_id = ? AND emails.starred = 0 ORDER BY emails.created_at DESC");
    $stmt->execute([$user_id]);
} elseif ($folder == 'sent') {
    $stmt = $conn->prepare("SELECT emails.*, users.name as recipient_name FROM emails JOIN users ON emails.recipient_id = users.id WHERE emails.sender_id = ? AND emails.starred = 0 ORDER BY emails.created_at DESC");
    $stmt->execute([$user_id]);
} elseif ($folder == 'starred') {
    $stmt = $conn->prepare("SELECT emails.*, users.name as sender_name FROM emails JOIN users ON emails.sender_id = users.id WHERE emails.recipient_id = ? AND emails.starred = 1 ORDER BY emails.created_at DESC");
    $stmt->execute([$user_id]);
}

$emails = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail - Inbox</title>
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background-color: #f2f2f2;
        }
        .container {
            display: flex;
            height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .logo img {
            width: 120px;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #202124;
            text-decoration: none;
            font-size: 16px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #e8f0fe;
            color: #1a73e8;
        }
        .main {
            flex: 1;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 10px 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header input {
            width: 50%;
            padding: 10px;
            border: 1px solid #dadce0;
            border-radius: 20px;
            font-size: 16px;
        }
        .compose-btn {
            background-color: #1a73e8;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .compose-btn:hover {
            background-color: #1557b0;
        }
        .email-list {
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        .email-item {
            display: flex;
            padding: 15px;
            border-bottom: 1px solid #dadce0;
            cursor: pointer;
        }
        .email-item:hover {
            background-color: #f1f3f4;
        }
        .email-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .email-sender {
            width: 20%;
            font-weight: bold;
        }
        .email-subject {
            flex: 1;
        }
        .email-time {
            width: 15%;
            text-align: right;
            color: #5f6368;
        }
        .star {
            margin-right: 10px;
            cursor: pointer;
        }
        .star.starred {
            color: #f4b400;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="https://ssl.gstatic.com/ui/v1/icons/mail/rfr/logo_gmail_lockup_default_1x_r2.png" alt="Gmail Logo">
            </div>
            <button class="compose-btn" onclick="window.location.href='compose.php'">Compose</button>
            <a href="inbox.php?folder=inbox" <?php if($folder=='inbox') echo 'class="active"'; ?>>Inbox</a>
            <a href="inbox.php?folder=starred" <?php if($folder=='starred') echo 'class="active"'; ?>>Starred</a>
            <a href="inbox.php?folder=sent" <?php if($folder=='sent') echo 'class="active"'; ?>>Sent</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="main">
            <div class="header">
                <input type="text" id="search" placeholder="Search mail" onkeyup="searchEmails()">
                <div></div>
            </div>
            <div class="email-list" id="email-list">
                <?php foreach ($emails as $email): ?>
                    <div class="email-item" onclick="window.location.href='view_email.php?id=<?php echo $email['id']; ?>'">
                        <input type="checkbox">
                        <span class="star <?php if($email['starred']) echo 'starred'; ?>" onclick="toggleStar(<?php echo $email['id']; ?>, event)">★</span>
                        <div class="email-sender"><?php echo $folder == 'sent' ? $email['recipient_name'] : $email['sender_name']; ?></div>
                        <div class="email-subject"><?php echo htmlspecialchars($email['subject']); ?></div>
                        <div class="email-time"><?php echo date('M d', strtotime($email['created_at'])); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function toggleStar(emailId, event) {
            event.stopPropagation();
            fetch('star_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email_id=' + emailId
            }).then(() => location.reload());
        }

        function searchEmails() {
            let input = document.getElementById('search').value.toLowerCase();
            let emails = document.getElementsByClassName('email-item');
            for (let i = 0; i < emails.length; i++) {
                let sender = emails[i].getElementsByClassName('email-sender')[0].innerText.toLowerCase();
                let subject = emails[i].getElementsByClassName('email-subject')[0].innerText.toLowerCase();
                if (sender.includes(input) || subject.includes(input)) {
                    emails[i].style.display = '';
                } else {
                    emails[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
