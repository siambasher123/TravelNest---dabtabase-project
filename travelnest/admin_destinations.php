<?php
/*
------------------------------------------------------------
File: admin_destinations.php
Purpose: Beautiful Admin Destinations Management
------------------------------------------------------------
*/

require_once __DIR__ . '/config/db.php';

// ✅ Add destination
if (isset($_POST['add_destination'])) {
  $stmt = $pdo->prepare("INSERT INTO destinations (name, country, description) VALUES (:name, :country, :description)");
  $stmt->execute([
    ':name' => $_POST['name'],
    ':country' => $_POST['country'],
    ':description' => $_POST['description']
  ]);
  $alert = "<div class='alert alert-success text-center mt-3'>✅ Destination added successfully!</div>";
}

// ✅ Update destination
if (isset($_POST['update_destination'])) {
  $stmt = $pdo->prepare("UPDATE destinations SET name=:name, country=:country, description=:description WHERE destination_id=:id");
  $stmt->execute([
    ':name' => $_POST['name'],
    ':country' => $_POST['country'],
    ':description' => $_POST['description'],
    ':id' => $_POST['destination_id']
  ]);
  $alert = "<div class='alert alert-primary text-center mt-3'>✏️ Destination updated successfully!</div>";
}

// ✅ Delete destination
if (isset($_POST['delete_destination'])) {
  $stmt = $pdo->prepare("DELETE FROM destinations WHERE destination_id = :id");
  $stmt->execute([':id' => $_POST['destination_id']]);
  $alert = "<div class='alert alert-danger text-center mt-3'>🗑️ Destination deleted!</div>";
}

// ✅ Fetch all destinations (latest first)
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY destination_id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Destinations | TravelNest</title>
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
    }

    .sidebar-header {
      text-align: center;
    }

    .sidebar-header h3 {
      font-weight: 700;
      font-size: 1.6rem;
      color: #facc15;
    }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #d1d5db;
      text-decoration: none;
      padding: 12px 25px;
      font-weight: 500;
      transition: 0.3s;
      border-left: 4px solid transparent;
    }

    .sidebar-nav a:hover, .sidebar-nav a.active {
      background: rgba(250, 204, 21, 0.1);
      color: #facc15;
      border-left: 4px solid #facc15;
    }

    .sidebar-footer {
      text-align: center;
      border-top: 1px solid #1f2937;
      padding-top: 10px;
    }

    .sidebar-footer a {
      color: #f87171;
      text-decoration: none;
      font-weight: 600;
    }

    /* 🟢 Main Content */
    .content {
      margin-left: 270px;
      padding: 30px;
    }

    .destination-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .destination-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>

<!-- 🖤 Sidebar -->
<div class="sidebar">
  <div>
    <div class="sidebar-header mb-3">
      <h3>TravelNest</h3>
      <p class="text-sm text-gray-400">Admin Panel</p>
    </div>

    <nav class="sidebar-nav">
      <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
      <a href="index.php?page=admin_users"><i class="bi bi-people"></i> Users</a>
      <a href="index.php?page=admin_hotels"><i class="bi bi-building"></i> Hotels</a>
      <a href="index.php?page=admin_destinations" class="active"><i class="bi bi-geo-alt"></i> Destinations</a>
      <a href="index.php?page=admin_bookings"><i class="bi bi-journal"></i> Bookings</a>
    </nav>
  </div>
  <div class="sidebar-footer">
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>

<!-- 🌍 Main Content -->
<div class="content">
  <h2 class="fw-bold text-3xl text-blue-700 mb-4">🌍 Manage Destinations</h2>
  <?= $alert ?? '' ?>

  <!-- 🟢 Add Destination Form -->
  <div class="card p-4 shadow-lg border-0 mb-5">
    <h5 class="fw-bold text-gray-700 mb-3">Add New Destination</h5>
    <form method="POST" class="row g-3">
      <div class="col-md-3">
        <input type="text" name="name" class="form-control border-gray-300 shadow-sm" placeholder="Destination Name" required>
      </div>
      <div class="col-md-3">
        <input type="text" name="country" class="form-control border-gray-300 shadow-sm" placeholder="Country" required>
      </div>
      <div class="col-md-4">
        <input type="text" name="description" class="form-control border-gray-300 shadow-sm" placeholder="Short Description">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button name="add_destination" class="btn btn-success w-100 fw-semibold shadow-sm hover:scale-105 transition">Add</button>
      </div>
    </form>
  </div>

  <!-- 🧭 Destination Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($destinations as $d): ?>
      <div class="destination-card bg-white rounded-xl shadow p-4 border border-gray-200 relative">
        <form method="POST">
          <h5 class="text-xl fw-bold text-gray-800 mb-2">
            <i class="bi bi-geo-alt-fill text-blue-500"></i>
            <input type="text" name="name" value="<?= htmlspecialchars($d['name']) ?>" class="border-0 bg-transparent fw-semibold w-75">
          </h5>
          <p class="text-sm text-gray-600 mb-2">
            <input type="text" name="country" value="<?= htmlspecialchars($d['country']) ?>" class="border-0 bg-transparent text-sm fw-semibold w-75 text-gray-700">
          </p>
          <textarea name="description" rows="2" class="form-control border-gray-300 mb-3"><?= htmlspecialchars($d['description']) ?></textarea>

          <input type="hidden" name="destination_id" value="<?= $d['destination_id'] ?>">

          <div class="flex justify-between">
            <button name="update_destination" class="btn btn-success btn-sm fw-semibold shadow-sm hover:scale-105 transition px-3">
              <i class="bi bi-pencil-square"></i> Update
            </button>
            <button name="delete_destination" onclick="return confirm('Delete this destination?')" class="btn btn-danger btn-sm fw-semibold shadow-sm hover:scale-105 transition px-3">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
