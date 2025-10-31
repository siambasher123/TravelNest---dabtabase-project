<?php
/*
------------------------------------------------------------
File: admin_layout.php
Purpose: Shared sidebar layout for all admin pages
Usage:
  $pageTitle = "Admin Users";
  $content = "<h2>Users</h2>";
  include 'admin_layout.php';
------------------------------------------------------------
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($pageTitle ?? "Admin Panel | TravelNest") ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
    }
    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0; left: 0;
      background: #111827;
      color: white;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px 0;
    }
    .sidebar a {
      display: block;
      color: #d1d5db;
      text-decoration: none;
      padding: 12px 20px;
      transition: 0.3s;
    }
    .sidebar a:hover, .sidebar a.active {
      background: #2563eb;
      color: white;
      border-radius: 8px;
    }
    .sidebar h3 {
      text-align: center;
      font-size: 1.4rem;
      font-weight: bold;
      color: #3b82f6;
    }
    .content {
      margin-left: 260px;
      padding: 30px;
    }
  </style>
</head>
<body>

<!-- 🔹 Sidebar -->
<div class="sidebar">
  <div>
    <h3>TravelNest</h3>
    <a href="index.php?page=admin_dashboard" class="<?= $activePage=='dashboard'?'active':'' ?>">📊 Dashboard</a>
    <a href="index.php?page=admin_users" class="<?= $activePage=='users'?'active':'' ?>">👥 Users</a>
    <a href="index.php?page=admin_hotels" class="<?= $activePage=='hotels'?'active':'' ?>">🏨 Hotels</a>
    <a href="index.php?page=admin_rooms"
   class="block py-2 px-4 hover:bg-blue-600 rounded text-white <?= $activePage=='rooms'?'bg-blue-700':'' ?>">
   🛏️ Rooms
</a>

    <a href="index.php?page=admin_destinations" class="block py-2 px-4 hover:bg-blue-600 rounded text-white <?= $activePage=='destinations'?'bg-blue-700':'' ?>">
  🌍 Destinations
</a>

    <a href="index.php?page=admin_bookings" class="<?= $activePage=='bookings'?'active':'' ?>">📘 Bookings</a>
    <a href="index.php?page=admin_reports" class="<?= $activePage=='reports'?'active':'' ?>">📑 Reports</a>
  </div>
  <div class="text-center mb-3">
    <a href="logout.php" class="text-danger">🚪 Logout</a>
  </div>
</div>

<!-- 🔸 Main Content -->
<div class="content">
  <?= $content ?>
</div>

</body>
</html>
