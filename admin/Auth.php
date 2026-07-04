<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: http://localhost/easykick/admin/login.php");
    exit();
}