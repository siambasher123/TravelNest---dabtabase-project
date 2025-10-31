<?php
/*
------------------------------------------------------------
File: login.php
Purpose: Authenticate users and redirect based on their role
SQL Concepts Used:
  1️⃣ SELECT + WHERE (verify user credentials)
  2️⃣ Conditional logic (redirect to admin or user dashboard)
------------------------------------------------------------
*/
require_once __DIR__ . '/config/db.php';
session_start();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  // ✅ Validate input
  if (empty($email) || empty($password)) {
    $msg = "<div class='alert alert-danger text-center'>❌ Please fill in all fields.</div>";
  } else {
    // ✅ Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND password = MD5(:password)");
    $stmt->execute([':email' => $email, ':password' => $password]);
    $user = $stmt->fetch();

    if ($user) {
      // ✅ Start session
      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['name'] = $user['name'];
      $_SESSION['role'] = $user['role'];

      // ✅ Redirect based on role
      if ($user['role'] === 'admin') {
        header('Location: index.php?page=admin_dashboard');
      } else {
        header('Location: index.php?page=home');
      }
      exit;
    } else {
      $msg = "<div class='alert alert-danger text-center'>⚠️ Invalid email or password!</div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - TravelNest</title>

  <!-- Bootstrap + Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      background: linear-gradient(to right, #2563eb, #14b8a6);
      font-family: 'Poppins', sans-serif;
    }
    .card {
      border-radius: 1rem;
      backdrop-filter: blur(15px);
      background-color: rgba(255, 255, 255, 0.95);
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="card shadow-lg mx-auto p-4" style="max-width: 450px;">
      <h2 class="text-center text-primary mb-3">Welcome Back 👋</h2>
      <?= $msg ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <button class="btn btn-primary w-100 py-2">Login</button>
      </form>

      <p class="text-center mt-3 text-muted">
        Don’t have an account? 
        <a href="signup.php" class="text-blue-600 font-semibold hover:underline">Sign up</a>
      </p>
    </div>
  </div>
</body>
</html>
