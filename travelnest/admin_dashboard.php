<?php
require_once __DIR__ . '/config/db.php';

/* ------------------------------------------------------------
   ADMIN DASHBOARD DATA
   ✅ Includes SQL View (v_hotel_details)
   ------------------------------------------------------------
   SQL Concepts Used:
   1️⃣ VIEW (v_hotel_details joining hotels + destinations)
   2️⃣ AGGREGATE FUNCTIONS (COUNT, SUM, AVG)
   3️⃣ GROUP BY + HAVING
   4️⃣ ORDER BY
   5️⃣ LEFT JOIN (hotels + bookings)
------------------------------------------------------------ */

// ✅ Summary counts
$counts = [
  'Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
  'Destinations' => $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn(),
  'Hotels' => $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn(),
  'Bookings' => $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn(),
  'Reviews' => $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
];

// ✅ Revenue and booking stats
$revenue = $pdo->query("
  SELECT 
    COUNT(*) AS total_bookings,
    SUM(p.amount) AS total_revenue,
    AVG(p.amount) AS avg_payment
  FROM payments p
  JOIN bookings b ON b.booking_id = p.booking_id
")->fetch();

// ✅ Top destinations by rating (GROUP BY + HAVING)
$topDest = $pdo->query("
  SELECT destination, country,
         COUNT(hotel_id) AS total_hotels,
         AVG(rating) AS avg_rating
  FROM v_hotel_details
  GROUP BY destination, country
  HAVING COUNT(hotel_id) > 0
  ORDER BY avg_rating DESC
  LIMIT 5
")->fetchAll();

// ✅ Top hotels by total bookings (JOIN view + bookings)
$topHotels = $pdo->query("
  SELECT v.hotel_name, v.country, COUNT(b.booking_id) AS total_bookings
  FROM v_hotel_details v
  LEFT JOIN rooms r ON v.hotel_id = r.hotel_id
  LEFT JOIN bookings b ON r.room_id = b.room_id
  GROUP BY v.hotel_name, v.country
  ORDER BY total_bookings DESC
  LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard | TravelNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
      overflow-x: hidden;
    }
    /* 🖤 Sidebar */
    .sidebar {
      width: 260px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background: #0b0b0b;
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 25px 0;
      box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
      border-right: 2px solid #1f2937;
      transition: all 0.3s ease;
    }
    .sidebar-header { text-align: center; margin-bottom: 25px; }
    .sidebar-header h3 { font-weight: 700; font-size: 1.6rem; color: #facc15; }
    .sidebar-header p { color: #9ca3af; font-size: 0.85rem; }
    .sidebar-nav a {
      display: flex; align-items: center; gap: 12px;
      color: #d1d5db; text-decoration: none;
      padding: 12px 25px; font-weight: 500;
      transition: 0.3s; border-left: 4px solid transparent;
    }
    .sidebar-nav a:hover {
      background: rgba(250, 204, 21, 0.1);
      color: #facc15; border-left: 4px solid #facc15;
      transform: translateX(3px);
    }
    .sidebar-nav a.active {
      background: rgba(250, 204, 21, 0.15);
      border-left: 4px solid #facc15;
      color: #facc15;
    }
    .sidebar-footer { text-align: center; padding-top: 10px; border-top: 1px solid #1f2937; }
    .sidebar-footer a { color: #f87171; text-decoration: none; font-weight: 600; transition: 0.3s; }
    .sidebar-footer a:hover { color: #fca5a5; }
    .content { margin-left: 270px; padding: 30px; transition: all 0.4s ease; }
    .card-hover { transition: transform .3s ease, box-shadow .3s ease; }
    .card-hover:hover { transform: translateY(-6px); box-shadow: 0 10px 25px rgba(0,0,0,.15); }
  </style>
</head>
<body>

<!-- 🖤 Sidebar -->
<div class="sidebar">
  <div>
    <div class="sidebar-header">
      <h3>TravelNest</h3>
      <p>Admin Panel</p>
    </div>

    <nav class="sidebar-nav">
      <a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="index.php?page=admin_users"><i class="bi bi-people"></i> Users</a>
      <a href="index.php?page=admin_hotels"><i class="bi bi-building"></i> Hotels</a>
      <a href="index.php?page=admin_rooms"><i class="bi bi-door-open"></i> Rooms</a>
      <a href="index.php?page=admin_bookings"><i class="bi bi-journal-bookmark"></i> Bookings</a>
      <a href="index.php?page=admin_reports"><i class="bi bi-bar-chart"></i> Reports</a>
    </nav>
  </div>
  <div class="sidebar-footer">
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>

<!-- 🌍 Main Content -->
<div class="content">
  <h2 class="fw-bold text-3xl text-gray-800 mb-4">Welcome Back, Admin 👋</h2>

  <!-- Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <?php 
      $colors = ['from-indigo-500 to-blue-500', 'from-green-500 to-emerald-500', 'from-pink-500 to-rose-500', 'from-yellow-400 to-amber-500', 'from-purple-500 to-indigo-500'];
      $i = 0;
      foreach ($counts as $label => $count): 
    ?>
    <div class="card-hover p-5 rounded-xl text-white bg-gradient-to-r <?= $colors[$i % count($colors)] ?> shadow-lg text-center">
      <h6 class="uppercase text-sm opacity-90"><?= $label ?></h6>
      <h3 class="text-3xl font-bold mt-1"><?= $count ?></h3>
    </div>
    <?php $i++; endforeach; ?>
  </div>

  <!-- Revenue -->
  <div class="bg-white p-5 rounded-xl shadow mb-8 border">
    <h4 class="fw-semibold text-gray-800 mb-3">💰 Booking & Payment Overview</h4>
    <div class="grid sm:grid-cols-3 gap-4 text-center">
      <div class="p-4 bg-blue-50 rounded-lg">
        <h6 class="text-gray-600">Total Bookings</h6>
        <h3 class="text-2xl font-bold text-blue-700"><?= $revenue['total_bookings'] ?: 0 ?></h3>
      </div>
      <div class="p-4 bg-green-50 rounded-lg">
        <h6 class="text-gray-600">Total Revenue</h6>
        <h3 class="text-2xl font-bold text-green-700">$<?= number_format($revenue['total_revenue'] ?: 0, 2) ?></h3>
      </div>
      <div class="p-4 bg-yellow-50 rounded-lg">
        <h6 class="text-gray-600">Average Payment</h6>
        <h3 class="text-2xl font-bold text-yellow-700">$<?= number_format($revenue['avg_payment'] ?: 0, 2) ?></h3>
      </div>
    </div>
  </div>

  <!-- Top Destinations -->
  <div class="bg-white p-5 rounded-xl shadow mb-8 border">
    <h4 class="fw-bold text-gray-800 mb-3">🌍 Top Rated Destinations</h4>
    <table class="table table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Destination</th>
          <th>Country</th>
          <th>Hotels</th>
          <th>Avg Rating</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topDest as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['destination']) ?></td>
            <td><?= htmlspecialchars($d['country']) ?></td>
            <td><?= $d['total_hotels'] ?></td>
            <td><?= number_format($d['avg_rating'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Top Hotels -->
  <div class="bg-white p-5 rounded-xl shadow border">
    <h4 class="fw-bold text-gray-800 mb-3">🏨 Most Booked Hotels</h4>
    <table class="table table-hover align-middle">
      <thead class="table-dark">
        <tr>
          <th>Hotel</th>
          <th>Country</th>
          <th>Total Bookings</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topHotels as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['hotel_name']) ?></td>
            <td><?= htmlspecialchars($h['country']) ?></td>
            <td><?= $h['total_bookings'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
