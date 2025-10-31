<?php
/*
------------------------------------------------------------
File: admin_rooms.php
Purpose: Manage hotel rooms for each hotel
SQL Concepts Used:
  1️⃣ INSERT (add room)
  2️⃣ UPDATE (edit room info)
  3️⃣ DELETE (remove room)
  4️⃣ INNER JOIN (join hotels + rooms)
  5️⃣ ORDER BY (show latest rooms first)
------------------------------------------------------------
*/

// ✅ Connect to database
require_once __DIR__ . '/config/db.php';

// ✅ Page setup
$pageTitle = "Manage Rooms | TravelNest";
$activePage = 'rooms';

// ✅ Add a new room
if (isset($_POST['add_room'])) {
  $hotel_id = $_POST['hotel_id'];
  $room_type = $_POST['room_type'];
  $price = $_POST['price'];
  $available = $_POST['available'];

  if ($hotel_id && $room_type && $price >= 0) {
    $stmt = $pdo->prepare("
      INSERT INTO rooms (hotel_id, room_type, price, available)
      VALUES (:hotel_id, :room_type, :price, :available)
    ");
    $stmt->execute([
      ':hotel_id' => $hotel_id,
      ':room_type' => $room_type,
      ':price' => $price,
      ':available' => $available
    ]);
    $message = "<div class='alert alert-success text-center mt-3'>✅ Room added successfully!</div>";
  } else {
    $message = "<div class='alert alert-warning text-center mt-3'>⚠️ All fields are required!</div>";
  }
}

// ✅ Update room
if (isset($_POST['update_room'])) {
  $room_id = $_POST['room_id'];
  $price = $_POST['price'];
  $available = $_POST['available'];

  $stmt = $pdo->prepare("UPDATE rooms SET price=:price, available=:available WHERE room_id=:id");
  $stmt->execute([':price'=>$price, ':available'=>$available, ':id'=>$room_id]);

  $message = "<div class='alert alert-primary text-center mt-3'>✏️ Room updated successfully!</div>";
}

// ✅ Delete room
if (isset($_POST['delete_room'])) {
  $room_id = $_POST['room_id'];
  $pdo->prepare("DELETE FROM rooms WHERE room_id=:id")->execute([':id'=>$room_id]);
  $message = "<div class='alert alert-danger text-center mt-3'>🗑️ Room deleted!</div>";
}

// ✅ Fetch all hotels for dropdown
$hotels = $pdo->query("SELECT hotel_id, name FROM hotels ORDER BY name")->fetchAll();

// ✅ Fetch all rooms (latest first) with hotel info
$rooms = $pdo->query("
  SELECT r.*, h.name AS hotel_name
  FROM rooms r
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  ORDER BY r.room_id DESC
")->fetchAll();

ob_start();
?>

<!-- 🔹 Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="fw-bold text-primary">🛏️ Manage Rooms</h2>
</div>

<!-- 🔹 Message -->
<?php if (isset($message)) echo $message; ?>

<!-- 🟢 Add Room -->
<div class="card shadow-sm border-0 p-4 mb-4">
  <h5 class="fw-bold mb-3">Add New Room</h5>
  <form method="POST">
    <div class="row g-3 align-items-center">
      <div class="col-md-3">
        <select name="hotel_id" class="form-select" required>
          <option value="">-- Choose Hotel --</option>
          <?php foreach ($hotels as $h): ?>
            <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <select name="room_type" class="form-select" required>
          <option value="single">Single</option>
          <option value="double">Double</option>
          <option value="suite">Suite</option>
        </select>
      </div>
      <div class="col-md-2">
        <input type="number" name="price" step="0.01" min="0" class="form-control" placeholder="Price" required>
      </div>
      <div class="col-md-2">
        <input type="number" name="available" min="0" class="form-control" placeholder="Available Rooms" required>
      </div>
      <div class="col-md-2 text-end">
        <button name="add_room" class="btn btn-success w-100">➕ Add</button>
      </div>
    </div>
  </form>
</div>

<!-- 📋 Rooms Table -->
<div class="card shadow-sm border-0 p-4">
  <h5 class="fw-bold mb-3">Existing Rooms</h5>
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Hotel</th>
        <th>Room Type</th>
        <th>Price ($)</th>
        <th>Available</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rooms)): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">No rooms added yet.</td></tr>
      <?php else: ?>
        <?php foreach ($rooms as $r): ?>
          <tr>
            <form method="POST">
              <td class="text-center fw-bold"><?= $r['room_id'] ?></td>
              <td><?= htmlspecialchars($r['hotel_name']) ?></td>
              <td class="text-center"><?= htmlspecialchars(ucfirst($r['room_type'])) ?></td>
              <td><input type="number" name="price" step="0.01" value="<?= htmlspecialchars($r['price']) ?>" class="form-control form-control-sm text-center"></td>
              <td><input type="number" name="available" value="<?= htmlspecialchars($r['available']) ?>" class="form-control form-control-sm text-center"></td>
              <td class="text-center">
                <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">
                <button name="update_room" class="btn btn-sm btn-success me-1 px-3">Update</button>
                <button name="delete_room" class="btn btn-sm btn-danger px-3" onclick="return confirm('Delete this room?')">Delete</button>
              </td>
            </form>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?>
