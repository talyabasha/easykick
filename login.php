<?php
session_start();

// Already logged in — redirect
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

include "db.php";

$error   = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["is_admin"]  = (int)$user["is_admin"];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EasyKick</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="auth-page">
    <div style="width:100%; max-width:420px;">
        <div class="auth-logo">Easy<span>Kick</span> ⚽</div>
        <div class="auth-subtitle">Sign in to book your pitch</div>

        <div class="form-card">
            <h2>Welcome back</h2>

            <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

            <form method="POST" novalidate>
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required autofocus>

                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>

                <button type="submit" class="btn">Sign In</button>
            </form>

            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

</body>
</html>