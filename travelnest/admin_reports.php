<?php
/*
------------------------------------------------------------
File: admin_reports.php
Purpose: Generate detailed analytical reports for admin
Layout: Uses admin_layout.php (sidebar version)
SQL Concepts Used:
  1️⃣ SUBQUERY (find top-rated hotels/destinations)
  2️⃣ CARTESIAN JOIN (cross-match hotels and countries)
  3️⃣ SELF JOIN (compare hotels in same country)
  4️⃣ OUTER JOINS (right/full join examples)
  5️⃣ Aggregate Functions (COUNT, SUM, AVG, MIN, MAX)
  6️⃣ SPOOL (output results into a file)
  7️⃣ SET MEMBERSHIP (IN operator)
------------------------------------------------------------
*/

$pageTitle = "Admin Reports | TravelNest";
$activePage = 'reports';

// ✅ 1️⃣ Top-rated hotels using SUBQUERY
$topHotels = $pdo->query("
  SELECT h.name, h.rating, d.name AS destination, d.country
  FROM hotels h
  JOIN destinations d ON h.destination_id = d.destination_id
  WHERE rating > (SELECT AVG(rating) FROM hotels)
  ORDER BY rating DESC
  LIMIT 5
")->fetchAll();

// ✅ 2️⃣ Cartesian Join Example
$cartesian = $pdo->query("
  SELECT h.name AS hotel, d.country
  FROM hotels h, destinations d
  LIMIT 10
")->fetchAll();

// ✅ 3️⃣ Self Join Example
$selfJoin = $pdo->query("
  SELECT a.name AS hotel_a, b.name AS hotel_b, a.rating AS rating_a, b.rating AS rating_b
  FROM hotels a
  JOIN hotels b ON a.destination_id = b.destination_id
  WHERE a.rating > b.rating AND a.hotel_id <> b.hotel_id
  ORDER BY a.rating DESC
  LIMIT 10
")->fetchAll();

// ✅ 4️⃣ RIGHT OUTER JOIN
$rightJoin = $pdo->query("
  SELECT b.booking_id, b.status, CONCAT(u.first_name, ' ', u.last_name) AS user_name
  FROM bookings b
  RIGHT JOIN users u ON b.user_id = u.user_id  
  ORDER BY b.booking_id DESC
  LIMIT 10
")->fetchAll();  // right outer

// ✅ 5️⃣ FULL OUTER JOIN (Simulated with UNION)
$fullJoin = $pdo->query("
  SELECT b.booking_id, CONCAT(u.first_name, ' ', u.last_name) AS user_name
  FROM bookings b
  LEFT JOIN users u ON b.user_id = u.user_id
  UNION
  SELECT b.booking_id, CONCAT(u.first_name, ' ', u.last_name) AS user_name
  FROM bookings b
  RIGHT JOIN users u ON b.user_id = u.user_id
  LIMIT 10
")->fetchAll();  // full outer

// ✅ 6️⃣ Aggregate stats per destination
$stats = $pdo->query("
  SELECT d.name AS destination, d.country,
         COUNT(h.hotel_id) AS total_hotels,
         AVG(h.rating) AS avg_rating,
         MAX(h.rating) AS top_rating,
         MIN(h.base_price) AS min_price
  FROM destinations d
  LEFT JOIN hotels h ON d.destination_id = h.destination_id
  GROUP BY d.name, d.country
  ORDER BY avg_rating DESC
")->fetchAll();

// ✅ 7️⃣ SPOOL Example — Output to File
$reportFile = __DIR__ . '/report_output.txt';
$spool = fopen($reportFile, 'w');
fwrite($spool, "==== TravelNest Admin Report (Generated: " . date('Y-m-d H:i:s') . ") ====\n\n");
fwrite($spool, "Top Hotels:\n");
foreach ($topHotels as $h) {
  fwrite($spool, "- {$h['name']} ({$h['destination']}, {$h['country']}) Rating: {$h['rating']}\n");
}
fclose($spool);

// ✅ 8️⃣ Set Membership Example (IN operator)
$setMembership = $pdo->query("
  SELECT h.name AS hotel_name, d.country, h.rating
  FROM hotels h
  JOIN destinations d ON h.destination_id = d.destination_id
  WHERE d.country IN ('France', 'Spain', 'Italy')
  ORDER BY d.country, h.rating DESC
  LIMIT 10
")->fetchAll();

ob_start();
?>

<!-- 🔹 Header Section with Download Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h2 class="fw-bold text-primary">📈 Admin Reports & Analysis</h2>
  <a href="download_report.php" class="btn btn-success shadow-sm px-4 py-2">
    ⬇️ Download Report (.txt)
  </a>
</div>

<!-- 🔹 Top Hotels -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">🏨 Top Rated Hotels (Subquery)</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-primary"><tr><th>Hotel</th><th>Destination</th><th>Country</th><th>Rating</th></tr></thead>
    <tbody>
      <?php foreach ($topHotels as $h): ?>
        <tr>
          <td><?= htmlspecialchars($h['name']) ?></td>
          <td><?= htmlspecialchars($h['destination']) ?></td>
          <td><?= htmlspecialchars($h['country']) ?></td>
          <td><?= htmlspecialchars($h['rating']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 🔹 Cartesian Join -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">🌍 Hotel-Country Combinations (Cartesian Join)</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-primary"><tr><th>Hotel</th><th>Country</th></tr></thead>
    <tbody>
      <?php foreach ($cartesian as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['hotel']) ?></td>
          <td><?= htmlspecialchars($c['country']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 🔹 Self Join -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">🏨 Hotel Comparison (Self Join)</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-primary"><tr><th>Hotel A</th><th>Hotel B</th><th>Rating A</th><th>Rating B</th></tr></thead>
    <tbody>
      <?php foreach ($selfJoin as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['hotel_a']) ?></td>
          <td><?= htmlspecialchars($s['hotel_b']) ?></td>
          <td><?= htmlspecialchars($s['rating_a']) ?></td>
          <td><?= htmlspecialchars($s['rating_b']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 🔹 Outer Joins -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">🔗 Outer Join Examples</h4>

  <p><strong>Right Outer Join:</strong> Bookings with/without user info</p>
  <ul>
    <?php foreach ($rightJoin as $r): ?>
      <li>Booking <?= $r['booking_id'] ?> — User: <?= htmlspecialchars($r['user_name'] ?? 'None') ?> (Status: <?= htmlspecialchars($r['status']) ?>)</li>
    <?php endforeach; ?>
  </ul>

  <p><strong>Full Outer Join (Simulated with UNION):</strong></p>
  <ul>
    <?php foreach ($fullJoin as $f): ?>
      <li>Booking <?= $f['booking_id'] ?> — User: <?= htmlspecialchars($f['user_name'] ?? 'None') ?></li>
    <?php endforeach; ?>
  </ul>
</div>

<!-- 🔹 Set Membership -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">🎯 Set Membership (IN Operator)</h4>
  <p>This section lists hotels located in France, Spain, or Italy using the <code>IN</code> operator.</p>
  <table class="table table-bordered table-striped">
    <thead class="table-primary"><tr><th>Hotel</th><th>Country</th><th>Rating</th></tr></thead>
    <tbody>
      <?php foreach ($setMembership as $h): ?>
        <tr>
          <td><?= htmlspecialchars($h['hotel_name']) ?></td>
          <td><?= htmlspecialchars($h['country']) ?></td>
          <td><?= htmlspecialchars($h['rating']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- 🔹 Aggregate Stats -->
<div class="card shadow-sm border-0 p-3 mb-4">
  <h4 class="fw-bold text-secondary">📊 Destination Statistics (Aggregate Functions)</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-primary">
      <tr>
        <th>Destination</th>
        <th>Country</th>
        <th>Total Hotels</th>
        <th>Average Rating</th>
        <th>Top Rating</th>
        <th>Lowest Price</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($stats as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['destination']) ?></td>
          <td><?= htmlspecialchars($s['country']) ?></td>
          <td><?= $s['total_hotels'] ?></td>
          <td><?= number_format($s['avg_rating'], 2) ?></td>
          <td><?= $s['top_rating'] ?></td>
          <td>$<?= number_format($s['min_price'], 2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="alert alert-success mt-4 shadow-sm">
  ✅ Report successfully generated. You can find it as 
  <strong><?= basename($reportFile) ?></strong> inside your <code>travelnest</code> folder,
  or download it using the green button above.
</div>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?>
