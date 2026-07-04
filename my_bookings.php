<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id = (int)$_SESSION['user_id'];
$today   = date('Y-m-d');

$stmt = $conn->prepare(
    "SELECT bookings.*, pitches.name AS pitch_name, pitches.location AS pitch_location
     FROM bookings
     JOIN pitches ON bookings.pitch_id = pitches.id
     WHERE bookings.user_id = ?
     ORDER BY booking_date DESC, booking_time DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings — EasyKick</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <div class="page-header">
        <h2>📅 My Bookings</h2>
        <a href="book.php" class="btn btn-sm">+ New Booking</a>
    </div>

    <?php if ($result->num_rows > 0): while ($row = $result->fetch_assoc()):
        $is_upcoming = $row['booking_date'] >= $today;
        $status = $row['status'] ?? 'pending';
    ?>
    <div class="booking-row" style="border-left: 4px solid
        <?= $status === 'confirmed' ? '#16a34a' : ($status === 'cancelled' ? '#dc2626' : '#f59e0b') ?>;">

        <div>
            <div class="pitch-name"><?= htmlspecialchars($row['pitch_name']) ?></div>
            <div class="booking-meta">
                📍 <?= htmlspecialchars($row['pitch_location']) ?>
                &nbsp;|&nbsp;
                📅 <?= htmlspecialchars(date('D, d M Y', strtotime($row['booking_date']))) ?>
                &nbsp;⏰ <?= htmlspecialchars(date('g:i A', strtotime($row['booking_time']))) ?>
            </div>

            <!-- STATUS MESSAGE -->
            <?php if ($status === 'confirmed'): ?>
                <div style="margin-top:0.5rem; background:#dcfce7; color:#14532d; padding:0.4rem 0.8rem; border-radius:6px; font-size:0.85rem; font-weight:600; display:inline-block;">
                    ✅ Your booking has been confirmed by the admin!
                </div>
            <?php elseif ($status === 'cancelled'): ?>
                <div style="margin-top:0.5rem; background:#fee2e2; color:#7f1d1d; padding:0.4rem 0.8rem; border-radius:6px; font-size:0.85rem; font-weight:600; display:inline-block;">
                    ❌ Your booking has been cancelled by the admin.
                </div>
            <?php else: ?>
                <div style="margin-top:0.5rem; background:#fef3c7; color:#92400e; padding:0.4rem 0.8rem; border-radius:6px; font-size:0.85rem; font-weight:600; display:inline-block;">
                    🕐 Waiting for admin confirmation...
                </div>
            <?php endif; ?>
        </div>

        <div class="booking-actions">
            <?php if ($is_upcoming && $status !== 'cancelled'): ?>
                <a href="cancel_booking.php?id=<?= (int)$row['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Cancel this booking?')">Cancel</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; else: ?>
    <div class="empty-state">
        <div class="empty-icon">⚽</div>
        <p>You haven't made any bookings yet.</p>
        <a href="book.php" class="btn">Book a Pitch Now</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>