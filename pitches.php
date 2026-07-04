<?php
session_start();
include "db.php";

$location = trim($_GET['location'] ?? '');
$date     = trim($_GET['date']     ?? '');

// Build query — filter by location if provided
if ($location !== '') {
    $stmt = $conn->prepare("SELECT * FROM pitches WHERE location LIKE ? ORDER BY name");
    $like = "%" . $location . "%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM pitches ORDER BY name");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pitches — EasyKick</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <div class="page-header">
        <h2>⚽ Available Pitches</h2>
    </div>

    <!-- Search / Filter -->
    <form method="GET" style="display:flex; gap:0.75rem; margin-bottom:1.75rem; flex-wrap:wrap;">
        <input type="text"  name="location" placeholder="Search by location…"
               value="<?= htmlspecialchars($location) ?>" style="flex:1; min-width:160px;">
        <input type="date"  name="date" value="<?= htmlspecialchars($date) ?>">
        <button type="submit" class="btn btn-sm" style="margin-top:0;">Search</button>
        <?php if ($location || $date): ?>
            <a href="pitches.php" class="btn btn-outline btn-sm" style="margin-top:0;">Clear</a>
        <?php endif; ?>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
    <div class="card-grid">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p>📍 <?= htmlspecialchars($row['location']) ?></p>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <span class="price">$<?= htmlspecialchars($row['price']) ?>/hr</span><br>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?pitch_id=<?= (int)$row['id'] ?>"
                   class="btn btn-sm" style="margin-top:0.75rem;">Book Now</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline btn-sm" style="margin-top:0.75rem;">Login to Book</a>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">🔍</div>
        <p>No pitches found<?= $location ? " in \"" . htmlspecialchars($location) . "\"" : "" ?>.</p>
        <a href="pitches.php" class="btn btn-outline">Show All Pitches</a>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
