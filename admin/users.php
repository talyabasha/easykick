<?php
require_once 'auth.php';
include "../db.php";

$page_title   = 'Manage Users';
$current_page = 'users';

$success = '';
$error   = '';
$my_id   = (int)$_SESSION['user_id'];

// ── ACTIONS ────────────────────────────────────────────────
$action = $_GET['action'] ?? '';
$target = (int)($_GET['id'] ?? 0);

if ($action && $target > 0 && $target !== $my_id) {
    switch ($action) {
        case 'make_admin':
            $conn->prepare("UPDATE users SET is_admin=1 WHERE id=?")->bind_param("i",$target);
            $stmt = $conn->prepare("UPDATE users SET is_admin=1 WHERE id=?");
            $stmt->bind_param("i", $target); $stmt->execute();
            header("Location: http://localhost/easykick/admin/users.php?msg=promoted"); exit();

        case 'remove_admin':
            $stmt = $conn->prepare("UPDATE users SET is_admin=0 WHERE id=?");
            $stmt->bind_param("i", $target); $stmt->execute();
            header("Location: http://localhost/easykick/admin/users.php?msg=demoted"); exit();

        case 'delete':
            $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND is_admin=0");
            $stmt->bind_param("i", $target); $stmt->execute();
           header("Location: http://localhost/easykick/admin/users.php?msg=deleted"); exit();
    }
}

// Flash
if (!empty($_GET['msg'])) {
    $success = match($_GET['msg']) {
        'promoted' => '⭐ User promoted to admin.',
        'demoted'  => 'Admin rights removed.',
        'deleted'  => '🗑️ User deleted.',
        default    => ''
    };
}

// Search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC");
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $users = $stmt->get_result();
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY id DESC");
}

include 'layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="table-card">
    <div class="table-card-header">
        <h2>👥 All Users</h2>
        <form method="GET" class="filter-bar">
            <input type="text" name="search" placeholder="Search name or email…"
                   value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-green btn-sm">Search</button>
            <?php if ($search): ?><a href="users.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Bookings</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($users->num_rows > 0): while ($u = $users->fetch_assoc()):
            $bc = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE user_id={$u['id']}")->fetch_assoc()['c'];
            $is_me = ((int)$u['id'] === $my_id);
        ?>
        <tr>
            <td style="color:var(--text-muted);">#<?= $u['id'] ?></td>
            <td style="font-weight:600;">
                <?= htmlspecialchars($u['name']) ?>
                <?php if ($is_me): ?><span class="badge badge-admin" style="margin-left:6px;">You</span><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td>
                <span class="badge <?= $u['is_admin'] ? 'badge-admin' : 'badge-user' ?>">
                    <?= $u['is_admin'] ? '⭐ Admin' : '👤 User' ?>
                </span>
            </td>
            <td><?= $bc ?></td>
            <td><?= isset($u['created_at']) ? htmlspecialchars(date('d M Y', strtotime($u['created_at']))) : '—' ?></td>
            <td style="display:flex; gap:0.4rem; flex-wrap:wrap; padding:0.6rem 1.1rem;">
                <?php if (!$is_me): ?>
                    <?php if (!$u['is_admin']): ?>
                        <a href="users.php?action=make_admin&id=<?= $u['id'] ?>"
                           class="btn btn-yellow btn-sm"
                           onclick="return confirm('Grant admin rights to <?= htmlspecialchars($u['name']) ?>?')">⭐ Make Admin</a>
                        <a href="users.php?action=delete&id=<?= $u['id'] ?>"
                           class="btn btn-red btn-sm"
                           onclick="return confirm('Delete user <?= htmlspecialchars($u['name']) ?>? This will also delete their bookings.')">🗑 Delete</a>
                    <?php else: ?>
                        <a href="users.php?action=remove_admin&id=<?= $u['id'] ?>"
                           class="btn btn-ghost btn-sm"
                           onclick="return confirm('Remove admin rights?')">Remove Admin</a>
                    <?php endif; ?>
                <?php else: ?>
                    <span style="color:var(--text-muted); font-size:0.8rem;">—</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr class="empty-row"><td colspan="7">No users found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
