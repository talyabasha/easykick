<?php
require_once 'auth.php';
include "../db.php";

$page_title   = 'Manage Pitches';
$current_page = 'pitches';

$success = '';
$error   = '';

// ── HANDLE ACTIONS ─────────────────────────────────────────
$action = $_GET['action'] ?? '';
$edit_id = (int)($_GET['edit'] ?? 0);

// DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];
    $conn->prepare("DELETE FROM pitches WHERE id = ?")->bind_param("i", $del_id) ;
    $stmt = $conn->prepare("DELETE FROM pitches WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
   header("Location: http://localhost/easykick/admin/pitches.php?msg=deleted");
    exit();
}

// Flash message
if (!empty($_GET['msg'])) {
    $success = match($_GET['msg']) {
        'added'   => '✅ Pitch added successfully.',
        'updated' => '✅ Pitch updated.',
        'deleted' => '🗑️ Pitch deleted.',
        default   => ''
    };
}

// ADD / EDIT submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']        ?? '');
    $location    = trim($_POST['location']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = floatval($_POST['price']   ?? 0);
    $pid         = (int)($_POST['pitch_id']   ?? 0);

    if (!$name || !$location || $price <= 0) {
        $error = "Name, location and a valid price are required.";
    } elseif ($pid > 0) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE pitches SET name=?, location=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("sssdi", $name, $location, $description, $price, $pid);
        $stmt->execute();
        header("Location: http://localhost/easykick/admin/pitches.php?msg=updated");
        exit();
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO pitches (name, location, description, price) VALUES (?,?,?,?)");
        $stmt->bind_param("sssd", $name, $location, $description, $price);
        $stmt->execute();
        header("Location: http://localhost/easykick/admin/pitches.php?msg=added");
        exit();
    }
}

// Load pitch for editing
$edit_pitch = null;
if ($edit_id > 0) {
    $s = $conn->prepare("SELECT * FROM pitches WHERE id = ?");
    $s->bind_param("i", $edit_id);
    $s->execute();
    $edit_pitch = $s->get_result()->fetch_assoc();
}

// Load all pitches
$pitches = $conn->query("SELECT * FROM pitches ORDER BY name");

include 'layout.php';
?>

<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<!-- ADD / EDIT FORM -->
<div class="form-card" style="margin-bottom:1.75rem; max-width:100%;">
    <h2 style="font-size:1rem; font-weight:600; color:var(--green-dark); margin-bottom:1.25rem;">
        <?= $edit_pitch ? '✏️ Edit Pitch' : '➕ Add New Pitch' ?>
    </h2>
    <form method="POST">
        <?php if ($edit_pitch): ?>
            <input type="hidden" name="pitch_id" value="<?= $edit_pitch['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div>
                <label for="name">Pitch Name</label>
                <input type="text" id="name" name="name" required
                       value="<?= htmlspecialchars($edit_pitch['name'] ?? $_POST['name'] ?? '') ?>"
                       placeholder="e.g. Green Arena">
            </div>
            <div>
                <label for="location">Location</label>
                <input type="text" id="location" name="location" required
                       value="<?= htmlspecialchars($edit_pitch['location'] ?? $_POST['location'] ?? '') ?>"
                       placeholder="e.g. Downtown">
            </div>
        </div>

        <div class="form-row">
            <div>
                <label for="price">Price per Hour ($)</label>
                <input type="number" id="price" name="price" required min="0" step="0.01"
                       value="<?= htmlspecialchars($edit_pitch['price'] ?? $_POST['price'] ?? '') ?>"
                       placeholder="25.00">
            </div>
            <div>
                <label for="description">Description</label>
                <input type="text" id="description" name="description"
                       value="<?= htmlspecialchars($edit_pitch['description'] ?? $_POST['description'] ?? '') ?>"
                       placeholder="Short description…">
            </div>
        </div>

        <div style="display:flex; gap:0.75rem; margin-top:1.25rem;">
            <button type="submit" class="btn btn-green">
                <?= $edit_pitch ? '💾 Save Changes' : '➕ Add Pitch' ?>
            </button>
            <?php if ($edit_pitch): ?>
                <a href="pitches.php" class="btn btn-ghost">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- PITCHES TABLE -->
<div class="table-card">
    <div class="table-card-header">
        <h2>🏟️ All Pitches</h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Location</th>
                <th>Description</th>
                <th>Price/hr</th>
                <th>Bookings</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($pitches->num_rows > 0): while ($p = $pitches->fetch_assoc()):
            $bc = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE pitch_id={$p['id']}")->fetch_assoc()['c'];
        ?>
        <tr>
            <td style="color:var(--text-muted);">#<?= $p['id'] ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($p['name']) ?></td>
            <td>📍 <?= htmlspecialchars($p['location']) ?></td>
            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?= htmlspecialchars($p['description']) ?>
            </td>
            <td style="font-weight:600; color:var(--green-dark);">$<?= number_format($p['price'],2) ?></td>
            <td><span class="badge badge-user"><?= $bc ?> bookings</span></td>
            <td style="display:flex; gap:0.4rem; padding:0.6rem 1.1rem;">
                <a href="pitches.php?edit=<?= $p['id'] ?>" class="btn btn-blue btn-sm">✏️ Edit</a>
                <a href="pitches.php?action=delete&id=<?= $p['id'] ?>"
                   class="btn btn-red btn-sm"
                   onclick="return confirm('Delete this pitch? All its bookings will also be removed.')">🗑 Delete</a>
            </td>
        </tr>
        <?php endwhile; else: ?>
        <tr class="empty-row"><td colspan="7">No pitches added yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layout_end.php'; ?>
