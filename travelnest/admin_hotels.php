<?php
/*
------------------------------------------------------------
File: admin_hotels.php
Purpose: Admin page to manage hotels (view, add, edit, delete)
------------------------------------------------------------
*/

require_once __DIR__ . '/config/db.php';

$pageTitle = "Admin Hotels | TravelNest";
$activePage = 'hotels';

// ✅ Add a new hotel
if (isset($_POST['add_hotel'])) {
  $name = $_POST['name'];
  $destination_id = $_POST['destination_id'];
  $base_price = $_POST['base_price'];
  $rating = $_POST['rating'];

  $insert = $pdo->prepare("
    INSERT INTO hotels (destination_id, name, base_price, rating)
    VALUES (:dest, :name, :price, :rating)
  ");
  $insert->execute([
    ':dest' => $destination_id,
    ':name' => $name,
    ':price' => $base_price,
    ':rating' => $rating
  ]);

  $alert = "<div class='alert alert-success text-center mt-3'>✅ Hotel added successfully!</div>";
}

// ✅ Update hotel
if (isset($_POST['update_hotel'])) {
  $hid = $_POST['hotel_id'];
  $name = $_POST['name'];
  $price = $_POST['base_price'];
  $rating = $_POST['rating'];

  $update = $pdo->prepare("
    UPDATE hotels 
    SET name = :name, base_price = :price, rating = :rating 
    WHERE hotel_id = :id
  ");
  $update->execute([
    ':name' => $name,
    ':price' => $price,
    ':rating' => $rating,
    ':id' => $hid
  ]);

  $alert = "<div class='alert alert-primary text-center mt-3'>✏️ Hotel updated successfully!</div>";
}

// ✅ Delete hotel
if (isset($_POST['delete_hotel'])) {
  $hid = $_POST['hotel_id'];
  $pdo->prepare("DELETE FROM hotels WHERE hotel_id = :id")->execute([':id' => $hid]);
  $alert = "<div class='alert alert-danger text-center mt-3'>🗑️ Hotel deleted!</div>";
}

// ✅ Fetch destinations
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY name")->fetchAll();

// ✅ Fetch hotels (latest first)
$hotels = $pdo->query("
  SELECT h.*, d.name AS destination_name, d.country
  FROM hotels h
  INNER JOIN destinations d ON h.destination_id = d.destination_id
  ORDER BY h.hotel_id DESC
")->fetchAll();

ob_start();
?>

<h2 class="fw-bold text-3xl text-blue-700 mb-4">🏨 Manage Hotels</h2>
<?= $alert ?? '' ?>

<!-- ➕ Add Hotel -->
<div class="card shadow-lg border-0 mb-5">
  <div class="card-body">
    <h5 class="fw-bold text-gray-700 mb-3">Add New Hotel</h5>
    <form method="POST" class="row g-3">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Hotel Name</label>
        <input type="text" name="name" class="form-control border-gray-300 shadow-sm" placeholder="Enter hotel name" required>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Destination</label>
        <select name="destination_id" class="form-select border-gray-300 shadow-sm" required>
          <option value="">-- Choose Destination --</option>
          <?php foreach ($destinations as $d): ?>
            <option value="<?= $d['destination_id'] ?>"><?= htmlspecialchars($d['name']) ?> (<?= htmlspecialchars($d['country']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold">Base Price ($)</label>
        <input type="number" name="base_price" class="form-control border-gray-300 shadow-sm" required>
      </div>
      <div class="col-md-2">
        <label class="form-label fw-semibold">Rating</label>
        <input type="number" step="0.1" name="rating" min="0" max="5" class="form-control border-gray-300 shadow-sm" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button name="add_hotel" class="btn btn-success w-100 py-2 fw-semibold shadow-sm hover:scale-105 transition">Add Hotel</button>
      </div>
    </form>
  </div>
</div>

<!-- 🏨 Hotels Table -->
<div class="card shadow-lg border-0">
  <div class="card-body">
    <h5 class="fw-bold text-gray-700 mb-3">All Hotels (Newest First)</h5>

    <div class="table-responsive">
      <table class="table align-middle table-hover shadow-sm">
        <thead class="table-dark">
          <tr class="text-center">
            <th>ID</th>
            <th>Hotel Name</th>
            <th>Destination</th>
            <th>Country</th>
            <th>Base Price ($)</th>
            <th>Rating</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hotels as $h): ?>
            <tr class="align-middle text-center hover:bg-gray-50">
              <form method="POST">
                <td><strong><?= $h['hotel_id'] ?></strong></td>
                <td>
                  <input type="text" name="name" value="<?= htmlspecialchars($h['name']) ?>" class="form-control form-control-sm border-gray-300">
                </td>
                <td><?= htmlspecialchars($h['destination_name']) ?></td>
                <td><?= htmlspecialchars($h['country']) ?></td>
                <td>
                  <input type="number" name="base_price" value="<?= htmlspecialchars($h['base_price']) ?>" class="form-control form-control-sm border-gray-300">
                </td>
                <td>
                  <input type="number" step="0.1" name="rating" value="<?= htmlspecialchars($h['rating']) ?>" class="form-control form-control-sm border-gray-300">
                </td>
                <td>
                  <input type="hidden" name="hotel_id" value="<?= $h['hotel_id'] ?>">
                  <div class="d-flex justify-content-center gap-2">
                    <button name="update_hotel" class="btn btn-success btn-sm fw-semibold shadow-sm px-3 hover:scale-105 transition">
                      <i class="bi bi-pencil-square"></i> Update
                    </button>
                    <button name="delete_hotel" onclick="return confirm('Delete this hotel?')" class="btn btn-danger btn-sm fw-semibold shadow-sm px-3 hover:scale-105 transition">
                      <i class="bi bi-trash3"></i> Delete
                    </button>
                  </div>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?>
