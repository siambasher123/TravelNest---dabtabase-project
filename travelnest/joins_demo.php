<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = "SQL Joins Demonstration | TravelNest";
ob_start();

/*
------------------------------------------------------------
File: joins_demo.php
Purpose: Demonstrate all SQL JOIN operations in one page
------------------------------------------------------------
*/

// 1️⃣ INNER JOIN
$inner = $pdo->query("
  SELECT h.name AS hotel, d.name AS destination, d.country
  FROM hotels h
  INNER JOIN destinations d ON h.destination_id = d.destination_id
  LIMIT 10
")->fetchAll();

// 2️⃣ RIGHT OUTER JOIN
$right = $pdo->query("
  SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS user_name, b.booking_id, b.status
  FROM bookings b
  RIGHT JOIN users u ON b.user_id = u.user_id
  LIMIT 10
")->fetchAll();

// 3️⃣ FULL OUTER JOIN (Simulated)
$full = $pdo->query("
  SELECT b.booking_id, CONCAT(u.first_name, ' ', u.last_name) AS user_name
  FROM bookings b
  LEFT JOIN users u ON b.user_id = u.user_id
  UNION
  SELECT b.booking_id, CONCAT(u.first_name, ' ', u.last_name) AS user_name
  FROM bookings b
  RIGHT JOIN users u ON b.user_id = u.user_id
  LIMIT 10
")->fetchAll();

// 4️⃣ SELF JOIN
$self = $pdo->query("
  SELECT a.name AS hotel_a, b.name AS hotel_b, a.rating AS rating_a, b.rating AS rating_b
  FROM hotels a
  JOIN hotels b ON a.destination_id = b.destination_id
  WHERE a.rating > b.rating AND a.hotel_id <> b.hotel_id
  LIMIT 10
")->fetchAll();

// 5️⃣ CARTESIAN JOIN
$cartesian = $pdo->query("
  SELECT h.name AS hotel, d.country
  FROM hotels h, destinations d
  LIMIT 10
")->fetchAll();

// 6️⃣ NATURAL JOIN
$natural = $pdo->query("
  SELECT name, country, rating
  FROM hotels NATURAL JOIN destinations
  LIMIT 10
")->fetchAll();

// 7️⃣ EQUI JOIN
$equi = $pdo->query("
  SELECT h.name AS hotel, d.country
  FROM hotels h
  JOIN destinations d ON h.destination_id = d.destination_id
  LIMIT 10
")->fetchAll();

// 8️⃣ NON-EQUI JOIN
$nonEqui = $pdo->query("
  SELECT h.name AS hotel, h.base_price, dis.discount_percent
  FROM hotels h
  JOIN discounts dis ON h.base_price BETWEEN dis.min_price AND dis.max_price
  ORDER BY h.base_price
  LIMIT 10
")->fetchAll();

// 9️⃣ MULTI-COLUMN JOIN
$multi = $pdo->query("
  SELECT CONCAT(u.first_name, ' ', u.last_name) AS user_name, b.booking_id, b.status, p.amount
  FROM users u
  JOIN bookings b ON u.user_id = b.user_id
  JOIN payments p ON b.booking_id = p.booking_id AND p.amount > 0
  LIMIT 10
")->fetchAll();

function renderTable($data) {
  if (empty($data)) {
    echo "<p class='text-muted'>No results found.</p>";
    return;
  }
  echo "<table class='table table-bordered table-sm table-striped'><thead class='table-dark'><tr>";
  foreach (array_keys($data[0]) as $col) {
    echo "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $col))) . "</th>";
  }
  echo "</tr></thead><tbody>";
  foreach ($data as $row) {
    echo "<tr>";
    foreach ($row as $val) echo "<td>" . htmlspecialchars($val) . "</td>";
    echo "</tr>";
  }
  echo "</tbody></table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    pre { background: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 6px; }
    .card { margin-bottom: 30px; border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .card h4 { color: #2563eb; }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="fw-bold text-center mb-4">🔗 SQL Joins Demonstration (TravelNest)</h2>

  <!-- INNER JOIN -->
  <div class="card p-3">
    <h4>1️⃣ INNER JOIN</h4>
    <pre>SELECT h.name, d.name, d.country
FROM hotels h
INNER JOIN destinations d ON h.destination_id = d.destination_id;</pre>
    <?php renderTable($inner); ?>
  </div>

  <!-- RIGHT OUTER JOIN -->
  <div class="card p-3">
    <h4>2️⃣ RIGHT OUTER JOIN</h4>
    <pre>SELECT u.first_name, u.last_name, b.booking_id, b.status
FROM bookings b
RIGHT JOIN users u ON b.user_id = u.user_id;</pre>
    <?php renderTable($right); ?>
  </div>

  <!-- FULL OUTER JOIN -->
  <div class="card p-3">
    <h4>3️⃣ FULL OUTER JOIN (Simulated)</h4>
    <pre>SELECT b.booking_id, u.first_name, u.last_name
FROM bookings b
LEFT JOIN users u ON b.user_id = u.user_id
UNION
SELECT b.booking_id, u.first_name, u.last_name
FROM bookings b
RIGHT JOIN users u ON b.user_id = u.user_id;</pre>
    <?php renderTable($full); ?>
  </div>

  <!-- SELF JOIN -->
  <div class="card p-3">
    <h4>4️⃣ SELF JOIN</h4>
    <pre>SELECT a.name, b.name, a.rating, b.rating
FROM hotels a
JOIN hotels b ON a.destination_id = b.destination_id
WHERE a.rating > b.rating;</pre>
    <?php renderTable($self); ?>
  </div>

  <!-- CARTESIAN JOIN -->
  <div class="card p-3">
    <h4>5️⃣ CARTESIAN JOIN</h4>
    <pre>SELECT h.name, d.country
FROM hotels h, destinations d;</pre>
    <?php renderTable($cartesian); ?>
  </div>

  <!-- NATURAL JOIN -->
  <div class="card p-3">
    <h4>6️⃣ NATURAL JOIN</h4>
    <pre>SELECT name, country, rating
FROM hotels NATURAL JOIN destinations;</pre>
    <?php renderTable($natural); ?>
  </div>

  <!-- EQUI JOIN -->
  <div class="card p-3">
    <h4>7️⃣ EQUI JOIN</h4>
    <pre>SELECT h.name, d.country
FROM hotels h
JOIN destinations d ON h.destination_id = d.destination_id;</pre>
    <?php renderTable($equi); ?>
  </div>

  <!-- NON-EQUI JOIN -->
  <div class="card p-3">
    <h4>8️⃣ NON-EQUI JOIN</h4>
    <pre>SELECT h.name, h.base_price, dis.discount_percent
FROM hotels h
JOIN discounts dis ON h.base_price BETWEEN dis.min_price AND dis.max_price;</pre>
    <?php renderTable($nonEqui); ?>
  </div>

  <!-- MULTI-COLUMN JOIN -->
  <div class="card p-3">
    <h4>9️⃣ MULTI-COLUMN JOIN</h4>
    <pre>SELECT u.first_name, u.last_name, b.booking_id, b.status, p.amount
FROM users u
JOIN bookings b ON u.user_id = b.user_id
JOIN payments p ON b.booking_id = p.booking_id AND p.amount > 0;</pre>
    <?php renderTable($multi); ?>
  </div>

</div>
</body>
</html>
