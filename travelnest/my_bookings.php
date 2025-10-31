<?php
/*
------------------------------------------------------------
File: my_bookings.php
Purpose: Display all bookings made by the current user
SQL Concepts Used:
  1️⃣ INNER JOIN (bookings + rooms + hotels + destinations)
  2️⃣ WHERE Clause (filter by user)
  3️⃣ ORDER BY (sort by booking date)
  4️⃣ GROUP BY + COUNT (optional summary by status)
------------------------------------------------------------
*/

// For demo: assume logged-in user_id = 1
$user_id = 1;

// ✅ Fetch user’s bookings (INNER JOIN + WHERE + ORDER BY)
$stmt = $pdo->prepare("
  SELECT b.booking_id, b.check_in, b.check_out, b.status, b.created_at,
         r.room_type, r.price,
         h.name AS hotel_name,
         d.name AS destination_name, d.country
  FROM bookings b
  INNER JOIN rooms r ON b.room_id = r.room_id
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  INNER JOIN destinations d ON h.destination_id = d.destination_id
  WHERE b.user_id = :uid
  ORDER BY b.created_at DESC
");
$stmt->execute([':uid' => $user_id]);
$bookings = $stmt->fetchAll();

// ✅ Summary per booking status (GROUP BY + COUNT)
$statusStats = $pdo->query("
  SELECT status, COUNT(*) AS total
  FROM bookings
  WHERE user_id = $user_id
  GROUP BY status
")->fetchAll();
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">My Bookings 📅</h2>

  <!-- Summary -->
  <div class="row mb-4">
    <?php foreach ($statusStats as $s): ?>
      <div class="col-md-4">
        <div class="card text-center p-3 shadow-sm bg-light">
          <h6 class="text-uppercase"><?= htmlspecialchars($s['status']) ?></h6>
          <h4><?= htmlspecialchars($s['total']) ?></h4>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Booking List -->
  <div class="row">
    <?php if ($bookings): ?>
      <?php foreach ($bookings as $b): ?>
        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow-sm">
            <h5><?= htmlspecialchars($b['hotel_name']) ?> — <?= htmlspecialchars($b['destination_name']) ?></h5>
            <p><strong>Country:</strong> <?= htmlspecialchars($b['country']) ?></p>
            <p><strong>Room Type:</strong> <?= ucfirst(htmlspecialchars($b['room_type'])) ?></p>
            <p><strong>Check-In:</strong> <?= htmlspecialchars($b['check_in']) ?></p>
            <p><strong>Check-Out:</strong> <?= htmlspecialchars($b['check_out']) ?></p>
            <p><strong>Status:</strong> 
              <span class="badge bg-<?= $b['status']=='confirmed'?'success':($b['status']=='pending'?'warning':'danger') ?>">
                <?= htmlspecialchars($b['status']) ?>
              </span>
            </p>
            <p><strong>Price:</strong> $<?= htmlspecialchars($b['price']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">You have no bookings yet.</p>
    <?php endif; ?>
  </div>
</div>
