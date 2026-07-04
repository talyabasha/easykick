<?php
session_start();
include "db.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyKick — Book Football Pitches</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<section class="hero">
    <div class="hero-content">
        <h1>Find &amp; Book<br><span>Football Pitches</span></h1>
        <p>Book the best pitches near you in seconds</p>

        <form class="search-box" action="pitches.php" method="GET">
            <input type="text"  name="location" placeholder="City or Area">
            <input type="date"  name="date">
            <button type="submit">Search ⚽</button>
        </form>
    </div>
</section>

<div class="features">
    <div class="feat"><div class="feat-icon">⚡</div> Fast Booking</div>
    <div class="feat"><div class="feat-icon">📍</div> Nearby Pitches</div>
    <div class="feat"><div class="feat-icon">💳</div> Easy Payments</div>
    <div class="feat"><div class="feat-icon">🏆</div> Top Quality</div>
</div>

<div class="container">
    <div class="page-header">
        <h2>⚽ Featured Pitches</h2>
        <a href="pitches.php" class="btn btn-outline btn-sm">View All</a>
    </div>

    <div class="card-grid">
    <?php
    $result = $conn->query("SELECT * FROM pitches LIMIT 3");
    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
    ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p>📍 <?= htmlspecialchars($row['location']) ?></p>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <span class="price">$<?= htmlspecialchars($row['price']) ?>/hr</span><br>
            <a href="book.php?pitch_id=<?= (int)$row['id'] ?>" class="btn btn-sm mt-2">Book Now</a>
        </div>
    <?php endwhile; else: ?>
        <p class="text-muted">No pitches available yet.</p>
    <?php endif; ?>
    </div>
</div>

</body>
</html>
