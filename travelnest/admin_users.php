<?php
/*
------------------------------------------------------------
File: admin_users.php
Purpose: Admin control panel to manage users
Layout: Uses admin_layout.php (sidebar version)
SQL Concepts Used:
  1️⃣ SELECT (display all users)
  2️⃣ UPDATE (change user role)
  3️⃣ DELETE (remove user)
  4️⃣ ALTER TABLE (add/drop/modify columns)
  5️⃣ RENAME COLUMN (example shown below)
------------------------------------------------------------
*/

$pageTitle = "Admin Users | TravelNest";
$activePage = 'users';

// ✅ Handle role update
if (isset($_POST['update_role'])) {
  $uid = $_POST['user_id'];
  $role = $_POST['role'];
  $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE user_id = :id");
  $stmt->execute([':role' => $role, ':id' => $uid]);
  $alert = "<div class='alert alert-success text-center mt-3'>✅ User role updated!</div>";
}

// ✅ Handle delete user
if (isset($_POST['delete_user'])) {
  $uid = $_POST['user_id'];
  $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
  $stmt->execute([':id' => $uid]);
  $alert = "<div class='alert alert-danger text-center mt-3'>🗑️ User deleted successfully!</div>";
}

// ✅ Fetch all users (with proper name)
$users = $pdo->query("
  SELECT user_id, first_name, last_name, email, role, created_at
  FROM users
  ORDER BY created_at DESC
")->fetchAll();

// ✅ Example SQL schema operations (not executed automatically)
// You can run these manually in phpMyAdmin to demonstrate lab concepts:
//
// -- Add new column:
// ALTER TABLE users ADD COLUMN phone VARCHAR(20);
//
// -- Modify column data type:
// ALTER TABLE users MODIFY COLUMN first_name VARCHAR(150);
//
// -- Rename column:
// ALTER TABLE users RENAME COLUMN first_name TO full_name;
//
// -- Delete column:
// ALTER TABLE users DROP COLUMN phone;
//
// -- Drop table:
// DROP TABLE users;

ob_start();
?>

<h2 class="fw-bold text-primary mb-4">👤 Manage Users</h2>
<?= $alert ?? '' ?>

<table class="table table-bordered table-striped shadow-sm align-middle">
  <thead class="table-primary">
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Created At</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['user_id'] ?></td>
        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
          <form method="POST" class="d-flex">
            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
            <select name="role" class="form-select form-select-sm me-2">
              <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
              <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
            </select>
            <button name="update_role" class="btn btn-sm btn-primary">Update</button>
          </form>
        </td>
        <td><?= $u['created_at'] ?></td>
        <td>
          <form method="POST" onsubmit="return confirm('Delete this user?')">
            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
            <button name="delete_user" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php
$content = ob_get_clean();
include 'admin_layout.php';
?>
