<?php
// db.php — database connection
// Change $pass and port to match your local MySQL setup
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "easykick";
$port   = 3308; // change to 3306 if using default MySQL port

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Database connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

$conn->set_charset("utf8mb4");
?>
