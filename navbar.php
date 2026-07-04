<?php
// navbar.php - must be included AFTER session_start()
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a class="navbar-brand" href="/easykick/index.php">Easy<span>Kick</span> ⚽</a>
    <div class="nav-links">
        <a href="/easykick/pitches.php" class="<?= $current === 'pitches.php' ? 'active' : '' ?>">Pitches</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/easykick/book.php"        class="<?= $current === 'book.php'        ? 'active' : '' ?>">Book</a>
            <a href="/easykick/my_bookings.php" class="<?= $current === 'my_bookings.php' ? 'active' : '' ?>">My Bookings</a>
            <a href="/easykick/dashboard.php"   class="<?= $current === 'dashboard.php'   ? 'active' : '' ?>">Dashboard</a>
            <a href="/easykick/logout.php" class="btn-logout">Logout</a>
        <?php else: ?>
            <a href="/easykick/login.php"    class="<?= $current === 'login.php'    ? 'active' : '' ?>">Login</a>
            <a href="/easykick/register.php" class="<?= $current === 'register.php' ? 'active' : '' ?>">Register</a>
        <?php endif; ?>
    </div>
</nav>
