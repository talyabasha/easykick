<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Already logged in as admin
if (isset($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header("Location: /easykick/admin/index.php");
    exit();
}

include "../db.php";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_admin']) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['is_admin']  = 1;
            header("Location: /easykick/admin/index.php");
            exit();
        } else {
            $error = "Your account does not have admin access.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — EasyKick</title>
    <link rel="stylesheet" href="/easykick/admin/admin.css">
</head>
<body>
<div class="admin-login-wrap">
    <div class="login-box">
        <div class="logo">Easy<span>Kick</span></div>
        <div class="sub">Admin Panel — Sign in to continue</div>

        <?php if ($error): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   placeholder="admin@easykick.com" required autofocus>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" required>

            <button type="submit" class="btn btn-green" style="width:100%; margin-top:1.25rem; justify-content:center;">
                Sign In to Admin
            </button>
        </form>

        <p style="text-align:center; margin-top:1rem; font-size:0.82rem; color:var(--text-muted);">
            <a href="/easykick/index.php" style="color:var(--green);">← Back to site</a>
        </p>
    </div>
</div>
</body>
</html>
