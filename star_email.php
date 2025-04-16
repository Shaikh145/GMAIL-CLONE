<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['email_id'])) {
    exit;
}

$email_id = $_POST['email_id'];
$stmt = $conn->prepare("UPDATE emails SET starred = NOT starred WHERE id = ?");
$stmt->execute([$email_id]);
?>
