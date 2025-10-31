<?php
/*
------------------------------------------------------------
File: rooms.php
Purpose: Display all available rooms for a specific hotel
------------------------------------------------------------
*/

// ✅ Include the database connection
require_once __DIR__ . '/config/db.php';

// ✅ Get the selected hotel ID
$hotel_id = $_GET['hotel_id'] ?? 0;

// ✅ Fetch hotel info (INNER JOIN)
$hotelStmt = $pdo->prepare("
  SELECT h.*, d.name AS destination, d.country
  FROM hotels h
  INNER JOIN destinations d ON h.destination_id = d.destination_id
  WHERE h.hotel_id = :id
");
$hotelStmt->execute([':id' => $hotel_id]);
$hotel = $hotelStmt->fetch();

// ✅ Fetch rooms (and apply discount logic)
$roomStmt = $pdo->prepare("
  SELECT r.*, 
         h.name AS hotel_name,
         (SELECT discount_percent 
          FROM discounts 
          WHERE r.price BETWEEN min_price AND max_price
          LIMIT 1) AS discount
  FROM rooms r
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  WHERE r.hotel_id = :id
  ORDER BY r.price ASC
");
$roomStmt->execute([':id' => $hotel_id]);
$rooms = $roomStmt->fetchAll();
?>

<div class="container py-5">
  <?php if ($hotel): ?>
    <div class="text-center mb-5">
      <h2 class="fw-bold"><?= htmlspecialchars($hotel['name']) ?></h2>
      <p class="text-muted fs-5">
        <?= htmlspecialchars($hotel['destination']) ?>, <?= htmlspecialchars($hotel['country']) ?>
      </p>
      <hr class="w-25 mx-auto mb-4">
    </div>

    <div class="row g-4">
      <?php if ($rooms): ?>
        <?php foreach ($rooms as $r): ?>
          <?php
            // Apply discount if exists
            $finalPrice = $r['discount']
              ? $r['price'] - ($r['price'] * $r['discount'] / 100)
              : $r['price'];
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="card room-card h-100 shadow-sm border-0">
              <div class="card-body">
                <h5 class="card-title fw-bold text-primary mb-2">
                  <?= ucfirst(htmlspecialchars($r['room_type'])) ?> Room
                </h5>
                <p class="card-text mb-1"><strong>Base Price:</strong> $<?= htmlspecialchars($r['price']) ?></p>

                <?php if ($r['discount']): ?>
                  <p class="text-success mb-1"><strong>Discount:</strong> <?= $r['discount'] ?>%</p>
                  <p class="fw-semibold"><strong>Final Price:</strong> $<?= number_format($finalPrice, 2) ?></p>
                <?php else: ?>
                  <p class="fw-semibold"><strong>Final Price:</strong> $<?= number_format($finalPrice, 2) ?></p>
                <?php endif; ?>

                <p class="text-muted mb-3"><strong>Available:</strong> <?= htmlspecialchars($r['available']) ?> rooms</p>
                <a href="index.php?page=book&room_id=<?= $r['room_id'] ?>" class="btn btn-warning w-100 fw-semibold">Book Now</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p class="text-muted fs-5">No rooms found for this hotel.</p>
        </div>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <div class="alert alert-danger text-center mt-5">
      <h4>❌ Invalid Hotel Selected!</h4>
      <p>Please go back and choose a valid hotel.</p>
    </div>
  <?php endif; ?>
</div>

<style>
.room-card {
  border-radius: 15px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.room-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}
.btn-warning {
  border-radius: 30px;
  transition: background 0.3s ease, transform 0.2s ease;
}
.btn-warning:hover {
  background: #ffcc00;
  transform: scale(1.05);
}
</style>
