<?php
// admin/layout.php
// Usage: include at top of page AFTER setting $page_title and $current_page
$current = $current_page ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Admin') ?> — EasyKick Admin</title>
    <link rel="stylesheet" href="http://localhost/easykick/admin/admin.css">
</head>
<body>
<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <a href="/easykick/admin/index.php" class="sidebar-logo">
        EasyKick <span>⚽ Admin Panel</span>
    </a>

    <div class="sidebar-section">Main</div>
    <a href="/easykick/admin/index.php"    class="<?= $current==='dashboard' ?'active':'' ?>"><span class="icon">📊</span> Dashboard</a>

    <div class="sidebar-section">Bookings</div>
    <a href="/easykick/admin/bookings.php"              class="<?= $current==='bookings'         ?'active':'' ?>"><span class="icon">📅</span> All Bookings</a>
    <a href="/easykick/admin/bookings.php?status=pending"   class="<?= $current==='pending'          ?'active':'' ?>"><span class="icon">🟡</span> Pending</a>
    <a href="/easykick/admin/bookings.php?status=confirmed" class="<?= $current==='confirmed'        ?'active':'' ?>"><span class="icon">🟢</span> Confirmed</a>
    <a href="/easykick/admin/bookings.php?status=cancelled" class="<?= $current==='cancelled_list'   ?'active':'' ?>"><span class="icon">🔴</span> Cancelled</a>

    <div class="sidebar-section">Manage</div>
    <a href="/easykick/admin/pitches.php"   class="<?= $current==='pitches' ?'active':'' ?>"><span class="icon">🏟️</span> Pitches</a>
    <a href="/easykick/admin/users.php"     class="<?= $current==='users'   ?'active':'' ?>"><span class="icon">👥</span> Users</a>

    <div class="sidebar-footer">
        <a href="/easykick/index.php">← Back to Site</a><br><br>
        <a href="/easykick/admin/logout.php">🚪 Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="admin-main">
    <div class="admin-topbar">
        <h1><?= htmlspecialchars($page_title ?? '') ?></h1>
        <div class="topbar-right">
            👤 <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></strong>
            &nbsp;|&nbsp;
            <a href="/easykick/admin/logout.php" style="color:var(--text-muted); text-decoration:none;">Logout</a>
        </div>
    </div>
    <div class="admin-body">
