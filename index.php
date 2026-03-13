<?php
// index.php - Entry Point
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: member/dashboard.php");
}
exit();
?>
