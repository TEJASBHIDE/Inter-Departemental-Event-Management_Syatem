<?php
require_once '../includes/db.php';
session_start();

if (isset($_SESSION['department_id'])) {
  header("Location: dept_dashboard.php");
  exit();
}

$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $dept_name = trim($_POST['department'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($dept_name && $email && $password && $confirm_password) {
    if ($password !== $confirm_password) {
      $error = "Passwords do not match.";
    } else {
      $check = $pdo->prepare("SELECT * FROM departments WHERE admin_email = ?");
      $check->execute([$email]);

      if ($check->rowCount() === 0) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO departments (department_name, admin_email, admin_password) VALUES (?, ?, ?)");
        $insert->execute([$dept_name, $email, $hashed]);
        $success = "Account created successfully. You can now login.";
      } else {
        $error = "Email is already registered.";
      }
    }
  } else {
    $error = "All fields are required.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Department Signup - Eventify Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-100 font-['Poppins']">

<div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full mx-4 border border-gray-100">
  <div class="text-center mb-8">
    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <i class="fas fa-building text-indigo-600 text-3xl"></i>
    </div>
    <h1 class="text-3xl font-bold text-gray-800">Department Signup</h1>
    <p class="text-gray-500 mt-2">Create your department admin account</p>
  </div>

  <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
      <i class="fas fa-check-circle mr-2"></i>
      <span><?= $success ?></span>
    </div>
  <?php elseif ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
      <i class="fas fa-exclamation-circle mr-2"></i>
      <span><?= $error ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" class="space-y-5" onsubmit="return validatePasswords();">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-university text-gray-400"></i>
        </div>
        <input type="text" name="department" required 
               class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
               placeholder="Enter department name">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-envelope text-gray-400"></i>
        </div>
        <input type="email" name="email" required 
               class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
               placeholder="Enter your email">
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-lock text-gray-400"></i>
        </div>
        <input type="password" name="password" id="password" required 
               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
               placeholder="Create password">
        <button type="button" onclick="toggleVisibility('password')" 
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
          <i class="far fa-eye"></i>
        </button>
      </div>
      <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-lock text-gray-400"></i>
        </div>
        <input type="password" name="confirm_password" id="confirm_password" required 
               class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
               placeholder="Confirm password">
        <button type="button" onclick="toggleVisibility('confirm_password')" 
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
          <i class="far fa-eye"></i>
        </button>
      </div>
    </div>

    <button type="submit" 
            class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md transition-all">
      Create Account
    </button>
  </form>

  <div class="mt-6 text-center">
    <p class="text-sm text-gray-600">
      Already have an account? 
      <a href="dept_login.php" class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline transition-colors">
        Sign in
      </a>
    </p>
  </div>
</div>

<script>
function toggleVisibility(id) {
  const field = document.getElementById(id);
  const icon = field.nextElementSibling.querySelector('i');
  field.type = field.type === 'password' ? 'text' : 'password';
  icon.classList.toggle('fa-eye');
  icon.classList.toggle('fa-eye-slash');
}

function validatePasswords() {
  const pass = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;
  
  if (pass.length < 8) {
    alert("Password must be at least 8 characters long.");
    return false;
  }
  
  if (pass !== confirm) {
    alert("Passwords do not match.");
    return false;
  }
  return true;
}
</script>

</body>
</html>