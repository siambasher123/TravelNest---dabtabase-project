<?php
$host = '127.0.0.1';
$port = '3308'; // your MySQL port
$db   = 'travelnest';
$user = 'root';
$pass = ''; // leave blank unless you set a password

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional test line:
    // echo "✅ Database connected successfully!";
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
?>
