<?php
/*
------------------------------------------------------------
File: book.php
Purpose: Allow user to book a selected room
SQL Concepts Used:
  1️⃣ INSERT (new booking record)
  2️⃣ UPDATE (reduce available rooms)
  3️⃣ WHERE Clause (ensure room availability)
  4️⃣ PRIMARY KEY + FOREIGN KEY (booking-room-user relationship)
------------------------------------------------------------
*/

$room_id = $_GET['room_id'] ?? 0;

// ✅ Fetch room details
$roomStmt = $pdo->prepare("
  SELECT r.*, h.name AS hotel_name
  FROM rooms r
  JOIN hotels h ON r.hotel_id = h.hotel_id
  WHERE r.room_id = :id
");
$roomStmt->execute([':id' => $room_id]);
$room = $roomStmt->fetch();

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = $_POST['name'];
  $email = $_POST['email'];
  $checkin  = $_POST['checkin'];
  $checkout = $_POST['checkout'];
  $guests   = $_POST['guests'];

  // Check availability first
  $checkStmt = $pdo->prepare("SELECT available FROM rooms WHERE room_id = :id");
  $checkStmt->execute([':id' => $room_id]);
  $available = $checkStmt->fetchColumn();

  if ($available > 0) {
    // ✅ 1️⃣ INSERT booking record
    $insert = $pdo->prepare("
      INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, status)
      VALUES (1, :room, :in, :out, :guests, 'confirmed')
    ");
    $insert->execute([
      ':room' => $room_id,
      ':in' => $checkin,
      ':out' => $checkout,
      ':guests' => $guests
    ]);

    // ✅ 2️⃣ UPDATE availability
    $update = $pdo->prepare("UPDATE rooms SET available = available - 1 WHERE room_id = :id");
    $update->execute([':id' => $room_id]);

    echo "<div class='alert alert-success text-center mt-3'>
            ✅ Room successfully booked! Check-in: $checkin | Check-out: $checkout
          </div>";
  } else {
    echo "<div class='alert alert-danger text-center mt-3'>
            ❌ Sorry, this room is no longer available.
          </div>";
  }
}
?>

<div class="container mt-4">
  <?php if ($room): ?>
    <h2 class="text-center mb-4">Book Room — <?= htmlspecialchars($room['hotel_name']) ?></h2>
    <div class="card p-4 shadow-sm">
      <h5><?= ucfirst(htmlspecialchars($room['room_type'])) ?> Room</h5>
      <p><strong>Price:</strong> $<?= htmlspecialchars($room['price']) ?></p>
      <p><strong>Available:</strong> <?= htmlspecialchars($room['available']) ?> left</p>

      <form method="POST">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Check-In Date</label>
            <input type="date" name="checkin" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Check-Out Date</label>
            <input type="date" name="checkout" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Guests</label>
          <input type="number" name="guests" class="form-control" min="1" required>
        </div>

        <button class="btn btn-success w-100">Confirm Booking</button>
      </form>
    </div>
  <?php else: ?>
    <div class="alert alert-danger text-center mt-5">
      <h4>❌ Invalid Room Selected!</h4>
    </div>
  <?php endif; ?>
</div>
