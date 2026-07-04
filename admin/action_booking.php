<?php
session_start();
include '../db.php';

$id     = (int)($_GET['id']     ?? 0);
$action = $_GET['action'] ?? '';

if ($id <= 0) {
    header("Location: http://localhost/easykick/admin/bookings.php");
    exit();
}

switch ($action) {
    case 'confirm':
        $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: http://localhost/easykick/admin/bookings.php?msg=confirmed");
        exit();

    case 'cancel':
        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: http://localhost/easykick/admin/bookings.php?msg=cancelled");
        exit();

    case 'delete':
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: http://localhost/easykick/admin/bookings.php?msg=deleted");
        exit();

    default:
        header("Location: http://localhost/easykick/admin/bookings.php");
        exit();
}