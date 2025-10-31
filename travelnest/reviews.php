<?php
/*
------------------------------------------------------------
File: reviews.php
Purpose: Allow users to post and view hotel reviews
SQL Concepts Used:
  1️⃣ INSERT (add a new review)
  2️⃣ INNER JOIN (combine reviews + hotels)
  3️⃣ AVG() Aggregate Function
  4️⃣ GROUP BY + HAVING (filter hotels with high ratings)
  5️⃣ ORDER BY (sort by latest reviews)
------------------------------------------------------------
*/

// Demo user (in real system, this would come from session)
$user_id = 1;

// ✅ Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $hotel_id = $_POST['hotel_id'];
  $rating   = $_POST['rating'];
  $comment  = $_POST['comment'];

  $stmt = $pdo->prepare("
    INSERT INTO reviews (user_id, hotel_id, rating, comment)
    VALUES (:user, :hotel, :rating, :comment)
  ");
  $stmt->execute([
    ':user' => $user_id,
    ':hotel' => $hotel_id,
    ':rating' => $rating,
    ':comment' => $comment
  ]);

  echo "<div class='alert alert-success text-center mt-3'>
          ✅ Review submitted successfully!
        </div>";
}

// ✅ Fetch hotel list for dropdown
$hotels = $pdo->query("SELECT hotel_id, name FROM hotels ORDER BY name")->fetchAll();

// ✅ Fetch all reviews (JOIN + ORDER BY)
$reviews = $pdo->query("
  SELECT r.*, u.name AS user_name, h.name AS hotel_name
  FROM reviews r
  INNER JOIN users u ON r.user_id = u.user_id
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  ORDER BY r.created_at DESC
")->fetchAll();

// ✅ Average ratings (GROUP BY + HAVING + AVG)
$ratings = $pdo->query("
  SELECT h.name AS hotel_name, AVG(r.rating) AS avg_rating, COUNT(*) AS total_reviews
  FROM reviews r
  INNER JOIN hotels h ON r.hotel_id = h.hotel_id
  GROUP BY h.name
  HAVING AVG(r.rating) >= 3.5
  ORDER BY avg_rating DESC
")->fetchAll();
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">Hotel Reviews ⭐</h2>

  <!-- 📝 Submit Review -->
  <div class="card p-4 mb-5 shadow-sm">
    <h5 class="mb-3">Add Your Review</h5>
    <form method="POST">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Select Hotel</label>
          <select name="hotel_id" class="form-select" required>
            <option value="">-- Choose a Hotel --</option>
            <?php foreach ($hotels as $h): ?>
              <option value="<?= $h['hotel_id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Rating (1–5)</label>
          <input type="number" name="rating" min="1" max="5" class="form-control" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Comment</label>
        <textarea name="comment" rows="3" class="form-control" required></textarea>
      </div>

      <button class="btn btn-primary w-100">Submit Review</button>
    </form>
  </div>

  <!-- 📊 Average Ratings -->
  <h4 class="mt-5 mb-3">🏆 Top-Rated Hotels (AVG Rating ≥ 3.5)</h4>
  <table class="table table-bordered table-striped">
    <thead class="table-light">
      <tr>
        <th>Hotel</th>
        <th>Average Rating</th>
        <th>Total Reviews</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($ratings as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['hotel_name']) ?></td>
          <td><?= number_format($r['avg_rating'], 2) ?></td>
          <td><?= $r['total_reviews'] ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- 💬 All Reviews -->
  <h4 class="mt-5 mb-3">Recent Reviews</h4>
  <div class="row">
    <?php if ($reviews): ?>
      <?php foreach ($reviews as $rev): ?>
        <div class="col-md-6 mb-4">
          <div class="card p-3 shadow-sm">
            <h5><?= htmlspecialchars($rev['hotel_name']) ?></h5>
            <p><strong>By:</strong> <?= htmlspecialchars($rev['user_name']) ?></p>
            <p><strong>Rating:</strong> ⭐ <?= htmlspecialchars($rev['rating']) ?>/5</p>
            <p><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
            <p class="text-muted small">Posted on <?= htmlspecialchars($rev['created_at']) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center text-muted">No reviews yet.</p>
    <?php endif; ?>
  </div>
</div>
