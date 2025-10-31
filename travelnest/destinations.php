<?php
/*
------------------------------------------------------------
File: destinations.php
Purpose: Display list of destinations with search & filter
SQL Concepts Used:
  1️⃣ WHERE Clause
  2️⃣ LIKE (Pattern Match)
  3️⃣ IN (Set Membership)
  4️⃣ ORDER BY (Sorting)
------------------------------------------------------------
*/

// ✅ Fetch filter/search inputs
$search  = $_GET['search']  ?? '';
$country = $_GET['country'] ?? '';

// ✅ Base query
$sql = "SELECT * FROM destinations WHERE 1";
$params = [];

// ✅ WHERE + LIKE — search by name or description
if (!empty($search)) {
  $sql .= " AND (name LIKE :search OR description LIKE :search)";
  $params[':search'] = "%$search%";
}

// ✅ IN (Set membership) — filter by multiple countries
if (!empty($country)) {
  $countries = explode(',', $country); // e.g., ?country=France,Japan
  $placeholders = implode(',', array_fill(0, count($countries), '?'));
  $sql .= " AND country IN ($placeholders)";
  $params = array_merge($params, $countries);
}

// ✅ ORDER BY — sort alphabetically
$sql .= " ORDER BY country, name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinations = $stmt->fetchAll();
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">Explore Destinations 🌍</h2>

  <!-- 🔎 Search Form -->
  <form method="get" class="row mb-4">
    <input type="hidden" name="page" value="destinations">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control"
             placeholder="Search destination..."
             value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-4">
      <input type="text" name="country" class="form-control"
             placeholder="Filter by country (comma-separated)"
             value="<?= htmlspecialchars($country) ?>">
    </div>
    <div class="col-md-4">
      <button class="btn btn-primary w-100">Search</button>
    </div>
  </form>

  <!-- 📋 Results -->
  <div class="row">
    <?php if ($destinations): ?>
      <?php foreach ($destinations as $d): ?>
        <div class="col-md-4 mb-4">
          <div class="card p-3 shadow-sm">
            <h5><?= htmlspecialchars($d['name']) ?></h5>
            <p><strong>Country:</strong> <?= htmlspecialchars($d['country']) ?></p>
            <p><?= htmlspecialchars($d['description']) ?></p>
            <a href="index.php?page=hotels&country=<?= urlencode($d['country']) ?>"
               class="btn btn-success btn-sm">View Hotels</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">No destinations found.</p>
    <?php endif; ?>
  </div>
</div>
