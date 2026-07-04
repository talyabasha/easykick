<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$booking_id = (int)($_GET['id'] ?? 0);
$user_id    = (int)$_SESSION['user_id'];

if ($booking_id > 0) {
    // Only delete if it belongs to this user AND is a future booking
    $stmt = $conn->prepare(
        "DELETE FROM bookings WHERE id = ? AND user_id = ? AND booking_date >= CURDATE()"
    );
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
}

header("Location: my_bookings.php");
exit();
