<?php
/*
------------------------------------------------------------
File: search.php
Purpose: AI-Powered Natural Language Search using Gemini 1.5
Database: travelnest
------------------------------------------------------------
*/

require_once __DIR__ . '/config/db.php';

// 🐞 Debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$query = $_GET['q'] ?? '';
$results = [];
$error = '';
$generatedSQL = '';

if ($query) {
  $apiKey = "AIzaSyAYVNsBDklopYmYrwn82SEE6muUJPzZQCQ"; // 🔒 Replace with your Gemini API key
  $model = "gemini-2.5-flash";

  // 🧠 Prompt to generate SQL (now includes primary key requirement)
  $prompt = "
SYSTEM: You are a MySQL expert. 
Return only one valid SQL SELECT statement for the database 'travelnest'.

SCHEMA:
SCHEMA:
destinations(destination_id, name, country, description);
hotels(hotel_id, destination_id, name, base_price, rating);
rooms(room_id, hotel_id, room_type, price, available);
bookings(booking_id, user_id, room_id, check_in, check_out, guests, status);
payments(payment_id, booking_id, amount, method);

REQUIREMENT:
Always include the primary key columns (like hotel_id, room_id, destination_id, etc.)
in the SELECT clause of your query. Do not exclude them even if not mentioned by the user.

User query: \"$query\"

REQUIREMENTS:
- Always return exactly one complete and valid MySQL SELECT statement.
- Do NOT omit the FROM or WHERE clauses.
- Never cut off mid-sentence; always include a semicolon (;) at the end.
- Output ONLY the SQL, no markdown or explanations.

";

  // 📨 Prepare Gemini payload
  $data = [
    "contents" => [
      [
        "role" => "user",
        "parts" => [["text" => $prompt]]
      ]
    ],
    "generationConfig" => [
      "temperature" => 0.2,
      "maxOutputTokens" => 256,
    ]
  ];

  // ✅ Correct endpoint for 2025
  $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

  // 🚀 Send request
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  $response = curl_exec($ch);

  // 🧩 Log any cURL errors
  if (curl_errno($ch)) {
    file_put_contents(__DIR__ . '/gemini_curl_error.txt', curl_error($ch));
  }
  curl_close($ch);

  // 🧠 Decode JSON
  $responseData = json_decode($response, true);
  file_put_contents(__DIR__ . '/gemini_raw_response.txt', $response);

  if (json_last_error() !== JSON_ERROR_NONE) {
    $error = "❌ Invalid JSON returned by Gemini.";
  }

  // 📜 Extract generated SQL
  $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
  file_put_contents(__DIR__ . '/gemini_debug.txt', $rawText);

  // 🧼 Clean Gemini output
  $generatedSQL = trim(preg_replace('/```[a-z]*|```/', '', $rawText));
  $generatedSQL = trim(str_ireplace(['SQL:', 'sql:'], '', $generatedSQL));

  // ✅ Execute if valid SELECT
  if (!empty($generatedSQL) && stripos(ltrim($generatedSQL), 'select') === 0) {
    try {
      $stmt = $pdo->query($generatedSQL);
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $error = "⚠️ Invalid query generated: " . htmlspecialchars($e->getMessage());
    }
  } else {
    if (!$error) $error = "⚠️ Gemini didn’t generate valid SQL. Check gemini_debug.txt.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AI Travel Search | TravelNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .card-hover:hover { transform: scale(1.02); transition: 0.3s; }
    code { white-space: pre-wrap; }
    .btn-success {
      border-radius: 30px;
      transition: background 0.3s ease, transform 0.2s ease;
    }
    .btn-success:hover {
      background: #28a745;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
<div class="container py-5">
  <h2 class="fw-bold text-center text-primary mb-4">🤖 Smart Travel Search</h2>

  <!-- 🔍 Search Bar -->
  <form method="GET" class="d-flex gap-2 mb-4">
    <input type="text" name="q" class="form-control form-control-lg"
      placeholder="Ask me anything... (e.g., show hotels in Paris below 100 dollars)"
      value="<?= htmlspecialchars($query) ?>" required>
    <button class="btn btn-primary px-4">Search</button>
  </form>

  <?php if ($query): ?>
    <div class="alert alert-info">
      💬 You asked: <strong><?= htmlspecialchars($query) ?></strong><br>
      <small class="text-muted">🧠 Gemini generated SQL:</small>
      <pre class="bg-dark text-white p-3 rounded"><code><?= htmlspecialchars($generatedSQL ?: 'No SQL generated') ?></code></pre>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <!-- 🧭 Results -->
  <?php if (!empty($results)): ?>
    <div class="row mt-4">
      <?php foreach ($results as $r): ?>
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm border-0 p-3 card-hover">
            <div class="card-body">
              <?php foreach ($r as $key => $value): ?>
                <p><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?>:</strong>
                  <?= htmlspecialchars($value) ?></p>
              <?php endforeach; ?>

              <!-- ✅ Added "View Rooms" button -->
              <?php if (isset($r['hotel_id'])): ?>
                <a href="rooms.php?hotel_id=<?= urlencode($r['hotel_id']) ?>" 
                   class="btn btn-success w-100 mt-2 fw-semibold">
                  🏨 View Rooms
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php elseif ($query && !$error): ?>
    <p class="text-center text-muted mt-5">No matching results found.</p>
  <?php endif; ?>
</div>
</body>
</html>
