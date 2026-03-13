<?php
// includes/auth_check.php - Middleware for session validation
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

function checkAdmin() {
    if ($_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
}

function checkMember() {
    if ($_SESSION['role'] !== 'member') {
        header("Location: ../index.php");
        exit();
    }
}
?>
