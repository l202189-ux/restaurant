<?php
include 'db.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ? AND role = 'rider'");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // We add 'deleted=1' to the URL so the main page knows to show the message
        header("Location: rider_accounts.php?deleted=1");
    } else {
        header("Location: rider_accounts.php?error=1");
    }
    exit();
}
?>