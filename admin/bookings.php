<?php
require_once 'auth.php';
include "../db.php";

$page_title   = 'Bookings';
$current_page = 'bookings';

$success = '';
$error   = '';

// Flash messages from redirect
if (!empty($_GET['msg'])) {
    $success = match($_GET['msg']) {
        'confirmed' => '✅ Booking confirmed successfully.',
        'cancelled' => '🚫 Booking cancelled.',
        'deleted'   => '🗑️ Booking deleted.',
        default     => ''
    };
}

// Filters
$filter_status = $_GET['status'] ?? '';
$filter_search = trim($_GET['search'] ?? '');
$filter_date   = $_GET['date'] ?? '';

// Build WHERE
$where   = [];
$params  = [];
$types   = '';

if ($filter_status !== '') {
    $where[]  = "bookings.status = ?";
    $params[] = $filter_status;
    $types   .= 's';
}
if ($filter_search !== '') {
    $where[]  = "(users.name LIKE ? OR pitches.name LIKE ?)";
    $like     = "%$filter_search%";
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
if ($filter_date !== '') {
    $where[]  = "bookings.booking_date = ?";
    $params[] = $filter_date;
    $types   .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT bookings.*, users.name AS user_name, users.email AS user_email,
               pitches.name AS pitch_name, pitches.location AS pitch_location
        FROM bookings
        JOIN users   ON bookings.user_id  = users.id
        JOIN pitches ON bookings.pitch_id = pitches.id
        $where_sql
        ORDER BY bookings.id DESC";

if ($types) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

include 'layout.php';
?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="table-card">
    <div class="table-card-header">
        <h2>📅 All Bookings</h2>
        <form method="GET" class="filter-bar">
            <input type="text"  name="search" placeholder="Search user or pitch…"
                   value="<?= htmlspecialchars($filter_search) ?>">
            <input type="date"  name="date"   value="<?= htmlspecialchars($filter_date) ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="pending"   <?= $filter_status==='pending'   ?'selected':'' ?>>Pending</option>
                <option value="confirmed" <?= $filter_status==='confirmed' ?'selected':'' ?>>Confirmed</option>
                <option value="cancelled" <?= $filter_status==='cancelled' ?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-green btn-sm">Filter</button>
            <?php if ($filter_status || $filter_search || $filter_date): ?>
                <a href="bookings.php" class="btn btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>User</th>
                <th>Pitch</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): while ($r = $result->fetch_assoc()): ?>
        <tr>
            <td style="font-weight:600; color:var(--text-muted);">#<?= $r['id'] ?></td>
            <td>
                <?= htmlspecialchars($r['user_name']) ?>
                <div class="sub"><?= htmlspecialchars($r['user_email']) ?></div>
            </td>
            <td>
                <?= htmlspecialchars($r['pitch_name']) ?>
                <div class="sub">📍 <?= htmlspecialchars($r['pitch_location']) ?></div>
            </td>
            <td><?= htmlspecialchars(date('d M Y', strtotime($r['booking_date']))) ?></td>
            <td><?= htmlspecialchars(date('g:i A', strtotime($r['booking_time']))) ?></td>
            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td style="display:flex; gap:0.4rem; flex-wrap:wrap; padding:0.6rem 1.1rem;">
                <?php if ($r['status'] === 'pending'): ?>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=confirm<?= $filter_status ? '&back_status='.$filter_status : '' ?>"
                       class="btn btn-green btn-sm">✓ Confirm</a>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=cancel<?= $filter_status ? '&back_status='.$filter_status : '' ?>"
                       class="btn btn-red btn-sm"
                       onclick="return confirm('Cancel this booking?')">✕ Cancel</a>
                <?php elseif ($r['status'] === 'confirmed'): ?>
                    <a href="action_booking.php?id=<?= $r['id'] ?>&action=cancel"
                       class="btn btn-red btn-sm"
                       onclick="return confirm('Cancel this confirmed booking?')">✕ Cancel</a>
                <?php endif; ?>
                <a href="action_booking.php?id=<?= $r['id'] ?>&action=delete"
                   class="btn btn-ghost btn-sm"
                   onclick="return confirm('Permanently delete this booking? This cannot be undone.')">🗑</a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr class="empty-row"><td colspan="7">No bookings found matching your filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
