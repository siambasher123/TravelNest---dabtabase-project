<?php
/*
------------------------------------------------------------
File: signup.php
Purpose: User registration with validation & role selection
For table: users (first_name, last_name, email, mobile, address, password, role)
------------------------------------------------------------
*/
require_once __DIR__ . '/config/db.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fname = trim($_POST['fname']);
  $lname = trim($_POST['lname']);
  $email = trim($_POST['email']);
  $mobile = trim($_POST['mobile']);
  $address = trim($_POST['address']);
  $role = $_POST['role'];
  $password = $_POST['password'];
  $confirm = $_POST['confirm'];

  // ✅ Validate email
  if (!preg_match('/@/', $email)) {
    $msg = "<div class='alert alert-danger text-center'>❌ Email must contain '@'</div>";
  }
  // ✅ Validate password
  elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/', $password)) {
    $msg = "<div class='alert alert-danger text-center'>
              ⚠️ Password must have at least 6 characters, one uppercase letter, one number, and one special character.
            </div>";
  }
  elseif ($password !== $confirm) {
    $msg = "<div class='alert alert-warning text-center'>❗ Passwords do not match!</div>";
  } else {
    // ✅ Check if email already exists
    $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $check->execute([':email' => $email]);

    if ($check->rowCount() > 0) {
      $msg = "<div class='alert alert-warning text-center'>⚠️ Email already registered!</div>";
    } else {
      // ✅ Insert into users
      $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, mobile, address, password, role, created_at)
        VALUES (:fname, :lname, :email, :mobile, :address, MD5(:password), :role, NOW())
      ");
      $stmt->execute([
        ':fname' => $fname,
        ':lname' => $lname,
        ':email' => $email,
        ':mobile' => $mobile,
        ':address' => $address,
        ':password' => $password,
        ':role' => $role
      ]);

      $msg = "<div class='alert alert-success text-center'>
                ✅ Signup successful! <a href='login.php'>Click here to login</a>.
              </div>";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signup - TravelNest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      background: linear-gradient(to right, #3b82f6, #06b6d4);
      font-family: 'Poppins', sans-serif;
    }
    .card {
      border-radius: 1rem;
      backdrop-filter: blur(15px);
      background-color: rgba(255, 255, 255, 0.9);
    }
  </style>
</head>
<body>
  <div class="container mt-5">
    <div class="card shadow-lg mx-auto p-4" style="max-width:600px;">
      <h2 class="text-center text-primary mb-3">Create an Account</h2>
      <?= $msg ?>

      <form method="POST">
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="fname" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="lname" class="form-control" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="example@email.com" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mobile</label>
          <input type="text" name="mobile" class="form-control" placeholder="+8801XXXXXXXXX" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" rows="2" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Recheck Password</label>
            <input type="password" name="confirm" id="confirm" class="form-control" required>
          </div>
        </div>

        <button class="btn btn-primary w-100 py-2">Sign Up</button>

        <p class="text-center mt-3 text-muted">
          Already have an account?
          <a href="login.php" class="text-blue-600 fw-bold">Login here</a>
        </p>
      </form>
    </div>
  </div>

  <script>
    // Password match validation
    const pass = document.getElementById('password');
    const confirm = document.getElementById('confirm');
    confirm.addEventListener('input', () => {
      if (confirm.value !== pass.value) {
        confirm.setCustomValidity("Passwords don't match");
      } else {
        confirm.setCustomValidity('');
      }
    });
  </script>
</body>
</html>
