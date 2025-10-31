<?php
/*
------------------------------------------------------------
File: admin_bookings.php
Purpose: Admin panel to view and manage all hotel bookings
------------------------------------------------------------
*/

require_once __DIR__ . '/config/db.php';

$pageTitle = "Admin Bookings | TravelNest";
$activePage = 'bookings';

// ✅ Update booking status
if (isset($_POST['update_status'])) {
  $bid = $_POST['booking_id'];
  $status = $_POST['status'];
  $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE booking_id = :id");
  $stmt->execute([':status' => $status, ':id' => $bid]);
  $alert = "<div class='alert alert-primary text-center mt-3'>✏️ Booking status updated!</div>";
}

// ✅ Update payment status
if (isset($_POST['update_payment'])) {
  $bid = $_POST['booking_id'];
  $payment_status = $_POST['payment_status'];

  // Check if payment record exists
  $check = $pdo->prepare("SELECT * FROM payments WHERE booking_id = :id");
  $check->execute([':id' => $bid]);

  if ($check->rowCount() > 0) {
    // Update existing payment
    $pdo->prepare("UPDATE payments SET payment_status = :status WHERE booking_id = :id")
        ->execute([':status' => $payment_status, ':id' => $bid]);
  } else {
    // Insert new payment record
    $pdo->prepare("INSERT INTO payments (booking_id, amount, method, payment_status) VALUES (:id, 0, 'N/A', :status)")
        ->execute([':id' => $bid, ':status' => $payment_status]);
  }

  $alert = "<div class='alert alert-success text-center mt-3'>💳 Payment status updated!</div>";
}

// ✅ Delete booking
if (isset($_POST['delete_booking'])) {
  $bid = $_POST['booking_id'];
  $pdo->prepare("DELETE FROM bookings WHERE booking_id = :id")->execute([':id' => $bid]);
  $pdo->prepare("DELETE FROM payments WHERE booking_id = :id")->execute([':id' => $bid]);
  $alert = "<div class='alert alert-danger text-center mt-3'>🗑️ Booking deleted successfully!</div>";
}

// ✅ Fetch all bookings (JOIN multiple tables)
$bookings = $pdo->query("
  SELECT 
    b.booking_id, b.check_in, b.check_out, b.guests, b.status, b.created_at,
    CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email,
    r.room_type, r.price,
    h.name AS hotel_name,
    d.name AS destination, d.country,
    p.payment_status
  FROM bookings b
  INNER JOIN users u ON b.user_id = u.user_id
  INNER JOIN rooms r ON b.room_id = r.room_id
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  INNER JOIN destinations d ON h.destination_id = d.destination_id
  LEFT JOIN payments p ON b.booking_id = p.booking_id
  ORDER BY b.created_at DESC
")->fetchAll();

// ✅ UNION example (confirmed or pending)
$unionExample = $pdo->query("
  (SELECT booking_id, status FROM bookings WHERE status='confirmed')
  UNION
  (SELECT booking_id, status FROM bookings WHERE status='pending')
")->fetchAll();

// ✅ MINUS example (bookings without payments)
$minusExample = $pdo->query("
  SELECT booking_id FROM bookings
  WHERE booking_id NOT IN (SELECT booking_id FROM payments WHERE payment_status='paid online')
")->fetchAll();

ob_start();
?>

<h2 class="fw-bold text-primary mb-4">📋 Manage Bookings</h2>

<?= $alert ?? '' ?>

<!-- 🎛️ Buttons Section -->
<div class="mb-4 d-flex gap-3">
  <button class="btn btn-outline-primary fw-semibold" id="showUnion">Show confirmed or pending bookings</button>
  <button class="btn btn-outline-danger fw-semibold" id="showMinus">Show bookings without payment</button>
</div>

<!-- Hidden results -->
<div id="unionResult" class="alert alert-info d-none">
  <h5 class="fw-bold mb-2">Confirmed or Pending Bookings (UNION Result):</h5>
  <?php if ($unionExample): ?>
    <ul class="mb-0">
      <?php foreach ($unionExample as $u): ?>
        <li>Booking ID: <strong><?= $u['booking_id'] ?></strong> — Status: <?= ucfirst($u['status']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="mb-0 text-muted">No confirmed or pending bookings found.</p>
  <?php endif; ?>
</div>

<div id="minusResult" class="alert alert-warning d-none">
  <h5 class="fw-bold mb-2">Bookings Without Payments (MINUS Result):</h5>
  <?php if ($minusExample): ?>
    <ul class="mb-0">
      <?php foreach ($minusExample as $m): ?>
        <li>Booking ID: <strong><?= $m['booking_id'] ?></strong></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p class="mb-0 text-muted">All bookings have payments recorded.</p>
  <?php endif; ?>
</div>

<!-- 🧾 Bookings Table -->
<table class="table table-bordered table-striped shadow-sm mt-4">
  <thead class="table-primary">
    <tr>
      <th>ID</th>
      <th>User</th>
      <th>Hotel</th>
      <th>Room Type</th>
      <th>Check-In</th>
      <th>Check-Out</th>
      <th>Guests</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= $b['booking_id'] ?></td>
        <td><?= htmlspecialchars($b['user_name']) ?><br><small><?= htmlspecialchars($b['email']) ?></small></td>
        <td><?= htmlspecialchars($b['hotel_name']) ?> (<?= htmlspecialchars($b['destination']) ?>)</td>
        <td><?= htmlspecialchars($b['room_type']) ?></td>
        <td><?= htmlspecialchars($b['check_in']) ?></td>
        <td><?= htmlspecialchars($b['check_out']) ?></td>
        <td><?= htmlspecialchars($b['guests']) ?></td>
        <td>
          <form method="POST" class="d-flex">
            <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
            <select name="status" class="form-select form-select-sm me-2">
              <option value="pending" <?= $b['status']=='pending'?'selected':'' ?>>Pending</option>
              <option value="confirmed" <?= $b['status']=='confirmed'?'selected':'' ?>>Confirmed</option>
              <option value="cancelled" <?= $b['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button name="update_status" class="btn btn-sm btn-primary">✔</button>
          </form>
        </td>
        <td>
          <form method="POST" class="d-flex">
            <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
            <select name="payment_status" class="form-select form-select-sm me-2">
              <option value="no payment" <?= $b['payment_status']=='no payment' || !$b['payment_status']?'selected':'' ?>>No Payment</option>
              <option value="paid online" <?= $b['payment_status']=='paid online'?'selected':'' ?>>Paid Online</option>
              <option value="will pay" <?= $b['payment_status']=='will pay'?'selected':'' ?>>Will Pay</option>
            </select>
            <button name="update_payment" class="btn btn-sm btn-success">💾</button>
          </form>
        </td>
        <td>
          <form method="POST" onsubmit="return confirm('Delete this booking?')">
            <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
            <button name="delete_booking" class="btn btn-sm btn-danger">🗑️</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script>
document.getElementById('showUnion').addEventListener('click', () => {
  const unionDiv = document.getElementById('unionResult');
  unionDiv.classList.toggle('d-none');
  document.getElementById('minusResult').classList.add('d-none');
});

document.getElementById('showMinus').addEventListener('click', () => {
  const minusDiv = document.getElementById('minusResult');
  minusDiv.classList.toggle('d-none');
  document.getElementById('unionResult').classList.add('d-none');
});
</script>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?>
