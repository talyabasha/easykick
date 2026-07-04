<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "db.php";

$user_id  = (int)$_SESSION['user_id'];
$error    = '';
$success  = '';

// Pre-select pitch from query string (e.g. from pitches page)
$preselect_pitch = (int)($_GET['pitch_id'] ?? 0);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $pitch_id    = (int)($_POST['pitch_id']     ?? 0);
    $booking_date = trim($_POST['booking_date'] ?? '');
    $booking_time = trim($_POST['booking_time'] ?? '');

    if (!$pitch_id || !$booking_date || !$booking_time) {
        $error = "Please fill in all fields.";
    } elseif ($booking_date < date('Y-m-d')) {
        $error = "You cannot book a pitch in the past.";
    } else {
        // Check for double-booking: same pitch, date, time
        $check = $conn->prepare(
            "SELECT id FROM bookings WHERE pitch_id = ? AND booking_date = ? AND booking_time = ?"
        );
        $check->bind_param("iss", $pitch_id, $booking_date, $booking_time);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Sorry, that pitch is already booked at that time. Please choose a different slot.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO bookings (user_id, pitch_id, booking_date, booking_time) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iiss", $user_id, $pitch_id, $booking_date, $booking_time);
            if ($stmt->execute()) {
                $success = "Booking confirmed! See you on the pitch 🎉";
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    }
}

// Load pitches for dropdown
$pitches = $conn->query("SELECT * FROM pitches ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Pitch — EasyKick</title>
    <link rel="stylesheet" href="/easykick/assets/style.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <h2>⚽ Book a Pitch</h2>

    <div class="form-card" style="max-width:520px; margin:0;">

        <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?> <a href="my_bookings.php">View bookings →</a></div><?php endif; ?>

        <form method="POST" novalidate>

            <label for="pitch_id">Select Pitch</label>
            <select name="pitch_id" id="pitch_id" required>
                <option value="">— Choose a pitch —</option>
                <?php if ($pitches): while ($row = $pitches->fetch_assoc()): ?>
                <option value="<?= (int)$row['id'] ?>"
                    <?= ($preselect_pitch === (int)$row['id'] || (int)($_POST['pitch_id'] ?? 0) === (int)$row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['name']) ?> — $<?= htmlspecialchars($row['price']) ?>/hr
                </option>
                <?php endwhile; endif; ?>
            </select>

            <label for="booking_date">Date</label>
            <input type="date" id="booking_date" name="booking_date"
                   min="<?= date('Y-m-d') ?>"
                   value="<?= htmlspecialchars($_POST['booking_date'] ?? '') ?>" required>

            <label for="booking_time">Time</label>
            <input type="time" id="booking_time" name="booking_time"
                   value="<?= htmlspecialchars($_POST['booking_time'] ?? '') ?>" required>

            <button type="submit" class="btn">Confirm Booking</button>
        </form>
    </div>
</div>

</body>
</html>
