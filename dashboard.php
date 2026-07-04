<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = (int)$_SESSION['user_id'];

// Count bookings
$res           = $conn->query("SELECT COUNT(*) AS total FROM bookings WHERE user_id = $user_id");
$total_bookings = $res->fetch_assoc()['total'] ?? 0;

// Upcoming bookings (today or future)
$today = date('Y-m-d');
$res2  = $conn->query("SELECT COUNT(*) AS upcoming FROM bookings WHERE user_id = $user_id AND booking_date >= '$today'");
$upcoming = $res2->fetch_assoc()['upcoming'] ?? 0;

// Most recent booking
$stmt = $conn->prepare(
    "SELECT bookings.*, pitches.name AS pitch_name
     FROM bookings
     JOIN pitches ON bookings.pitch_id = pitches.id
     WHERE bookings.user_id = ?
     ORDER BY booking_date DESC, booking_time DESC
     LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — EasyKick</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <div class="page-header">
        <h2>👋 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
        <a href="book.php" class="btn btn-sm">+ Book a Pitch</a>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-value"><?= $total_bookings ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Upcoming</div>
            <div class="stat-value"><?= $upcoming ?></div>
        </div>
    </div>

    <?php if ($recent): ?>
    <div class="page-header" style="margin-top:2rem;">
        <h2>📅 Recent Booking</h2>
        <a href="my_bookings.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="booking-row">
        <div>
            <div class="pitch-name"><?= htmlspecialchars($recent['pitch_name']) ?></div>
            <div class="booking-meta">
                📅 <?= htmlspecialchars($recent['booking_date']) ?>
                &nbsp;⏰ <?= htmlspecialchars($recent['booking_time']) ?>
            </div>
        </div>
        <div class="booking-actions">
            <span class="badge"><?= $recent['booking_date'] >= $today ? '🟢 Upcoming' : '✅ Past' ?></span>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">⚽</div>
        <p>You haven't made any bookings yet.</p>
        <a href="book.php" class="btn">Book Your First Pitch</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
