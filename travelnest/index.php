<?php
/*
------------------------------------------------------------
File: index.php
Purpose: Central router and layout for TravelNest
Features:
  ✅ Loads all pages dynamically
  ✅ Shows different nav items based on login status & role
  ✅ Restricts admin pages to admin users only
------------------------------------------------------------
*/

// ✅ Start session
session_start();

// ✅ Connect to the database
require_once __DIR__ . '/config/db.php';

// ✅ Determine which page to load
$page = $_GET['page'] ?? 'home';

// ✅ Define all allowed pages
$allowed = [
  'home', 'destinations', 'hotels', 'rooms',
  'book', 'my_bookings', 'reviews','admin_rooms',
  'admin_dashboard', 'admin_users', 'admin_hotels','admin_destinations',
  'admin_bookings', 'admin_reports'
];

// ✅ Protect admin pages
$adminPages = [
  'admin_dashboard', 'admin_users', 'admin_hotels',
  'admin_bookings', 'admin_reports'
];

// If the page is not allowed, go to home
if (!in_array($page, $allowed)) $page = 'home';

// Prevent normal users from accessing admin pages
if (in_array($page, $adminPages) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
  echo "<script>alert('Access Denied! Admins only.'); window.location='index.php?page=home';</script>";
  exit;
}

$pagePath = __DIR__ . '/' . $page . '.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TravelNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">TravelNest</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $page=='home'?'active':'' ?>" href="index.php?page=home">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $page=='destinations'?'active':'' ?>" href="index.php?page=destinations">Destinations</a></li>
        <li class="nav-item"><a class="nav-link <?= $page=='hotels'?'active':'' ?>" href="index.php?page=hotels">Hotels</a></li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'user'): ?>
          <li class="nav-item"><a class="nav-link <?= $page=='my_bookings'?'active':'' ?>" href="index.php?page=my_bookings">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link <?= $page=='reviews'?'active':'' ?>" href="index.php?page=reviews">Reviews</a></li>
        <?php endif; ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= str_contains($page,'admin')?'active':'' ?>" href="#" role="button" data-bs-toggle="dropdown">
              Admin Panel
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="index.php?page=admin_dashboard">Dashboard</a></li>
              <li><a class="dropdown-item" href="index.php?page=admin_users">Users</a></li>
              <li><a class="dropdown-item" href="index.php?page=admin_hotels">Hotels</a></li>
              
              <li><a class="dropdown-item" href="index.php?page=admin_bookings">Bookings</a></li>
              <li><a class="dropdown-item" href="index.php?page=admin_reports">Reports</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <!-- 🔐 Right side: Login / User / Logout -->
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link disabled">👋 <?= htmlspecialchars($_SESSION['name']) ?> (<?= htmlspecialchars($_SESSION['role']) ?>)</a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger fw-bold" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link fw-bold" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link fw-bold" href="signup.php">Signup</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Main Content -->
<div class="container mt-4">
  <?php
  if (file_exists($pagePath)) {
    include $pagePath;
  } else {
    echo "<div class='alert alert-warning text-center mt-5'>
            <h4>⚠️ Page Not Found</h4>
            <p>The page <strong>{$page}</strong> does not exist.</p>
          </div>";
  }
  ?>
</div>

<footer class="text-center mt-5 p-3 bg-light border-top">
  <p class="mb-0">© 2025 <strong>TravelNest</strong> | Built with ❤️ using PHP + MySQL</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
