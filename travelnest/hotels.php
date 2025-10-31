<?php
/*
------------------------------------------------------------
File: hotels.php
Purpose: Display hotels with filters and statistics
SQL Concepts Used:
  1️⃣ INNER JOIN  (hotels + destinations)
  2️⃣ WHERE Clause  (filter by country, search, price range)
  3️⃣ ORDER BY  (sort by rating & price)
  4️⃣ GROUP BY + HAVING
  5️⃣ Aggregate Functions (COUNT, AVG)
------------------------------------------------------------
*/

// ✅ Read filter inputs
$country  = $_GET['country'] ?? '';
$search   = $_GET['search'] ?? '';
$minPrice = $_GET['min'] ?? 0;
$maxPrice = $_GET['max'] ?? 99999;

// ✅ Build query for hotels (JOIN + WHERE + ORDER BY)
$sql = "SELECT h.*, d.name AS destination_name, d.country
        FROM hotels h
        INNER JOIN destinations d ON h.destination_id = d.destination_id
        WHERE h.base_price BETWEEN :min AND :max";     // range search

$params = [
  ':min' => $minPrice,
  ':max' => $maxPrice
];

if (!empty($country)) {
  $sql .= " AND d.country = :country";
  $params[':country'] = $country;
}

if (!empty($search)) {
  $sql .= " AND h.name LIKE :search";
  $params[':search'] = "%$search%";           //pattern match
}

$sql .= " ORDER BY h.rating DESC, h.base_price ASC";    //high rating , low rating

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$hotels = $stmt->fetchAll();

// ✅ Aggregate statistics per country (GROUP BY + HAVING)
$stats = $pdo->query("
  SELECT d.country, COUNT(*) AS total_hotels, AVG(h.rating) AS avg_rating
  FROM hotels h
  JOIN destinations d ON h.destination_id = d.destination_id
  GROUP BY d.country
  HAVING AVG(h.rating) > 4.0
  ORDER BY avg_rating DESC
")->fetchAll();
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">Hotels 🏨</h2>

  <!-- 🔎 Search & Filter Form -->
  <form method="get" class="row mb-4">
    <input type="hidden" name="page" value="hotels">
    <div class="col-md-3">
      <input type="text" name="search" class="form-control"
             placeholder="Search hotel name..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-3">
      <input type="text" name="country" class="form-control"
             placeholder="Country..." value="<?= htmlspecialchars($country) ?>">
    </div>
    <div class="col-md-2">
      <input type="number" name="min" class="form-control"
             placeholder="Min Price" value="<?= htmlspecialchars($minPrice) ?>">
    </div>
    <div class="col-md-2">
      <input type="number" name="max" class="form-control"
             placeholder="Max Price" value="<?= htmlspecialchars($maxPrice) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <!-- 🏨 Hotel Listings -->
  <div class="row">
    <?php if ($hotels): ?>
      <?php foreach ($hotels as $h): ?>
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm p-3">
            <h5><?= htmlspecialchars($h['name']) ?></h5>
            <p><strong>Destination:</strong> <?= htmlspecialchars($h['destination_name']) ?></p>
            <p><strong>Country:</strong> <?= htmlspecialchars($h['country']) ?></p>
            <p><strong>Price:</strong> $<?= htmlspecialchars($h['base_price']) ?></p>
            <p><strong>Rating:</strong> ⭐ <?= htmlspecialchars($h['rating']) ?></p>
            <a href="index.php?page=rooms&hotel_id=<?= $h['hotel_id'] ?>" class="btn btn-success btn-sm">View Rooms</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">No hotels found for the filters applied.</p>
    <?php endif; ?>
  </div>

  <!-- 📊 Aggregated Stats -->
  <h3 class="mt-5">🌍 Hotel Statistics (Group By + Having)</h3>
  <table class="table table-bordered mt-3">
    <thead class="table-light">
      <tr>
        <th>Country</th>
        <th>Total Hotels</th>
        <th>Average Rating</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stats as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['country']) ?></td>
          <td><?= htmlspecialchars($s['total_hotels']) ?></td>
          <td><?= number_format($s['avg_rating'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
