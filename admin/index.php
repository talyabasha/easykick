<?php
require_once 'auth.php';
include "../db.php";

$page_title  = 'Dashboard';
$current_page = 'dashboard';

// Stats
$total_bookings  = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'];
$pending         = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];
$confirmed       = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'];
$total_users     = $conn->query("SELECT COUNT(*) AS c FROM users WHERE is_admin=0")->fetch_assoc()['c'];
$total_pitches   = $conn->query("SELECT COUNT(*) AS c FROM pitches")->fetch_assoc()['c'];
$today           = date('Y-m-d');
$today_bookings  = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE booking_date='$today'")->fetch_assoc()['c'];

// Recent bookings
$recent = $conn->query(
    "SELECT bookings.*, users.name AS user_name, pitches.name AS pitch_name
     FROM bookings
     JOIN users  ON bookings.user_id  = users.id
     JOIN pitches ON bookings.pitch_id = pitches.id
     ORDER BY bookings.id DESC
     LIMIT 8"
);

include 'layout.php';
?>

<div class="stats-grid">
    <div class="stat-card accent-green" data-icon="📅">
        <div class="label">Total Bookings</div>
        <div class="value"><?= $total_bookings ?></div>
        <div class="trend">All time</div>
    </div>
    <div class="stat-card accent-yellow" data-icon="🟡">
        <div class="label">Pending</div>
        <div class="value"><?= $pending ?></div>
        <div class="trend">Awaiting action</div>
    </div>
    <div class="stat-card accent-blue" data-icon="👥">
        <div class="label">Users</div>
        <div class="value"><?= $total_users ?></div>
        <div class="trend"><?= $total_pitches ?> pitches</div>
    </div>
    <div class="stat-card accent-red" data-icon="⚽">
        <div class="label">Today's Bookings</div>
        <div class="value"><?= $today_bookings ?></div>
        <div class="trend"><?= date('d M Y') ?></div>
    </div>
</div>

<div class="table-card">
    <div class="table-card-header">
        <h2>📋 Recent Bookings</h2>
        <a href="bookings.php" class="btn btn-ghost btn-sm">View All →</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Pitch</th>
                <th>Date &amp; Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($recent->num_rows > 0): while ($r = $recent->fetch_assoc()): ?>
        <tr>
            <td>#<?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['user_name']) ?></td>
            <td><?= htmlspecialchars($r['pitch_name']) ?></td>
            <td>
                <?= htmlspecialchars(date('d M Y', strtotime($r['booking_date']))) ?>
                <div class="sub"><?= htmlspecialchars(date('g:i A', strtotime($r['booking_time']))) ?></div>
            </td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td>
                <?php if ($r['status'] === 'pending'): ?>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=confirm" class="btn btn-green btn-sm">✓ Confirm</a>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=cancel"
                       class="btn btn-red btn-sm"
                       onclick="return confirm('Cancel this booking?')">✕ Cancel</a>
                <?php elseif ($r['status'] === 'confirmed'): ?>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=cancel"
                       class="btn btn-red btn-sm"
                       onclick="return confirm('Cancel this confirmed booking?')">✕ Cancel</a>
                <?php else: ?>
                    <span style="color:var(--text-muted); font-size:0.8rem;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr class="empty-row"><td colspan="6">No bookings yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
