<?php
/*
------------------------------------------------------------
File: home.php
Purpose: Display top destinations and hotels, and add AI-powered search
SQL Concepts Used:
  1️⃣ SELECT with ORDER BY and LIMIT
  2️⃣ JOIN via database VIEW (v_hotel_details)
  3️⃣ Integration with Gemini for Natural Language SQL Search
------------------------------------------------------------
*/

// ✅ Connect database (if not already)
require_once __DIR__ . '/config/db.php';

// ✅ Fetch top destinations
$dest = [];
try {
  $dest = $pdo->query("SELECT * FROM destinations ORDER BY country, name LIMIT 6")->fetchAll();
} catch (PDOException $e) {
  $dest = [];
}

// ✅ Fetch top hotels (using view)
$hotels = [];
try {
  $hotels = $pdo->query("SELECT * FROM v_hotel_details ORDER BY rating DESC LIMIT 3")->fetchAll();
} catch (PDOException $e) {
  // If the view doesn't exist, try fallback directly from hotels
  try {
    $hotels = $pdo->query("
      SELECT h.hotel_id, h.name AS hotel_name, d.name AS destination, d.country, h.base_price, h.rating
      FROM hotels h
      JOIN destinations d ON h.destination_id = d.destination_id
      ORDER BY h.rating DESC
      LIMIT 3
    ")->fetchAll();
  } catch (PDOException $ex) {
    $hotels = [];
  }
}
?>

<div class="container mt-5">
  <h1 class="text-center fw-bold mb-5">🏝️ Welcome to <span class="text-primary">TravelNest</span></h1>

  <!-- 🔍 Smart Natural Language Search Bar -->
  <div class="card shadow-sm p-4 mb-5">
    <form method="GET" action="search.php" class="d-flex gap-2">
      <input 
        type="text" 
        name="q" 
        class="form-control form-control-lg"
        placeholder="Ask anything... e.g. 'show me hotels in Paris below 10 dollars'"
        required
      >
      <button class="btn btn-primary px-4">Search</button>
    </form>
    <small class="text-muted mt-2 d-block">
      💡 Try: “show destinations with good ratings” or “show all hotels in Spain”
    </small>
  </div>

  <!-- 🌍 Top Destinations -->
  <h3 class="fw-bold mb-3">🌍 Top Destinations</h3>
  <div class="row">
    <?php if (empty($dest)): ?>
      <p class="text-muted text-center">No destinations available.</p>
    <?php else: ?>
      <?php foreach($dest as $d): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm border-0 p-3">
            <div class="card-body">
              <h5 class="card-title text-primary"><?= htmlspecialchars($d['name']) ?></h5>
              <p class="card-text text-muted"><?= htmlspecialchars($d['country']) ?></p>
              <?php if (!empty($d['description'])): ?>
                <p class="small text-secondary"><?= htmlspecialchars($d['description']) ?></p>
              <?php endif; ?>
              <a href="index.php?page=hotels&country=<?= urlencode($d['country']) ?>" class="btn btn-outline-primary btn-sm">View Hotels</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- 🏨 Top Rated Hotels -->
  <h3 class="fw-bold mt-5 mb-3">🏨 Top Rated Hotels</h3>
  <div class="row">
    <?php if (empty($hotels)): ?>
      <p class="text-muted text-center">No hotels found. Add hotels from admin dashboard.</p>
    <?php else: ?>
      <?php foreach($hotels as $h): ?>
        <?php 
          // Safely handle price column (can be base_price or price)
          $price = $h['base_price'] ?? $h['price'] ?? 0;
        ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm border-0 p-3">
            <div class="card-body">
              <h5 class="card-title text-success"><?= htmlspecialchars($h['hotel_name']) ?></h5>
              <p class="card-text text-muted">
                <?= htmlspecialchars($h['destination']) ?> — <?= htmlspecialchars($h['country']) ?><br>
                ⭐ Rating: <?= htmlspecialchars($h['rating']) ?>
              </p>
              <p class="fw-bold text-primary">💰 $<?= number_format($price, 2) ?> / night</p>
              <a href="index.php?page=rooms&hotel_id=<?= $h['hotel_id'] ?>" class="btn btn-success btn-sm">View Rooms</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
