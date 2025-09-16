<?php
require_once '../includes/db.php';
session_start();

if (isset($_SESSION['department_id'])) {
  header("Location: dept_dashboard.php");
  exit();
}
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email && $password) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE admin_email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
      $admin = $stmt->fetch();
      if (password_verify($password, $admin['admin_password'])) {
        $_SESSION['department_id'] = $admin['department_id'];
        $_SESSION['department_name'] = $admin['department_name'];
        header("Location: dept_dashboard.php");
        exit();
      } else {
        $error = "Incorrect password.";
      }
    } else {
      $error = "No account found with that email.";
    }
  } else {
    $error = "Please fill all fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Department Login - Eventify Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --error: #ef4444;
    }
    
    body {
      background: linear-gradient(135deg, #f9f9ff 0%, #f0f4ff 100%);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
    }
    
    .login-container {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      position: relative;
      width: 100%;
      max-width: 28rem;
    }
    
    .login-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }
    
    .login-header {
      text-align: center;
      padding: 2rem 2rem 1rem;
    }
    
    .login-icon {
      width: 4rem;
      height: 4rem;
      margin: 0 auto 1rem;
      background: rgba(79, 70, 229, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
    }
    
    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }
    
    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      font-weight: 500;
      color: #374151;
    }
    
    .form-input {
      width: 100%;
      padding: 0.875rem 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      font-size: 1rem;
      transition: all 0.2s ease;
    }
    
    .form-input:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      outline: none;
    }
    
    .password-toggle {
      position: absolute;
      right: 1rem;
      top: 16px;
      color: #9ca3af;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .password-toggle:hover {
      color: var(--primary);
    }
    
    .btn-login {
      width: 100%;
      padding: 0.875rem;
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      font-weight: 500;
      border-radius: 0.5rem;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(79, 70, 229, 0.15);
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    }
    
    .login-footer {
      text-align: center;
      padding: 1.5rem 2rem;
      border-top: 1px solid #f3f4f6;
    }
    
    .login-footer a {
      color: var(--primary);
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s ease;
    }
    
    .login-footer a:hover {
      text-decoration: underline;
    }
    
    .alert-error {
      background: rgba(239, 68, 68, 0.1);
      border-left: 4px solid var(--error);
      color: var(--error);
      padding: 0.875rem 1rem;
      border-radius: 0.5rem;
      margin: 0 2rem 1.5rem;
      display: flex;
      align-items: center;
      animation: slideIn 0.3s ease-out;
    }
    
    .alert-error i {
      margin-right: 0.5rem;
    }
    
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .floating-element {
      position: fixed;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(99, 102, 241, 0.05);
      z-index: -1;
    }
    
    .element-1 {
      top: -50px;
      right: -50px;
    }
    
    .element-2 {
      bottom: -80px;
      left: -80px;
      width: 300px;
      height: 300px;
    }
  </style>
</head>
<body class="flex items-center justify-center p-4">
  <!-- Floating decorative elements -->
  <div class="floating-element element-1"></div>
  <div class="floating-element element-2"></div>

  <div class="login-container">
    <div class="login-header">
      <div class="login-icon">
        <i class="fas fa-university text-2xl"></i>
      </div>
      <h1 class="text-2xl font-bold text-gray-800 mb-1">Department Admin Login</h1>
      <p class="text-gray-500">Access your department dashboard</p>
    </div>

    <?php if ($error): ?>
      <div class="alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?= $error ?></span>
      </div>
    <?php endif; ?>

    <form method="POST" class="px-6">
      <div class="form-group">
        <label class="form-label">Admin Email</label>
        <input type="email" name="email" required class="form-input" placeholder="Enter your email">
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="relative">
          <input type="password" name="password" id="password" required class="form-input pr-10" placeholder="Enter your password">
          <span onclick="toggleVisibility()" class="password-toggle">
            <i class="far fa-eye" id="toggleIcon"></i>
          </span>
        </div>
      </div>

      <div class="mt-6 mb-4">
        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt mr-2"></i> Login
        </button>
      </div>
    </form>

    <div class="login-footer">
      <p class="text-sm text-gray-600">
        Don't have an account? 
        <a href="dept_signup.php" class="text-indigo-600 hover:underline">Sign up</a>
      </p>
    </div>
  </div>

  <script>
    function toggleVisibility() {
      const passwordField = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>