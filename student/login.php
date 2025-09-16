<?php
require_once '../includes/db.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
  header("Location: dashboard.php");
  exit();
}

$alert = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (empty($email) || empty($password)) {
    $alert = 'Please enter both email and password.';
  } else {
    $stmt = $pdo->prepare("SELECT student_id, full_name, password FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password'])) {
      $_SESSION['student_id'] = $student['student_id'];
      $_SESSION['student_name'] = $student['full_name'];
      header("Location: dashboard.php");
      exit();
    } else {
      $alert = "Invalid email or password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Login - Eventify</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
    }
    
    body {
      background: linear-gradient(135deg, #f9f9ff 0%, #f0f4ff 100%);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .login-container {
      max-width: 480px;
      width: 100%;
      margin: auto;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
      overflow: hidden;
      position: relative;
      padding: 2.5rem;
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
      margin-bottom: 2rem;
    }
    
    .login-title {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }
    
    .login-icon {
      width: 5rem;
      height: 5rem;
      background: rgba(79, 70, 229, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      color: var(--primary);
      font-size: 1.75rem;
    }
    
    .input-group {
      margin-bottom: 1.5rem;
    }
    
    .input-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: #374151;
      font-size: 0.9375rem;
    }
    
    .input-field {
      width: 100%;
      padding: 0.875rem 1rem;
      border: 1px solid #e5e7eb;
      border-radius: 0.5rem;
      font-size: 1rem;
      transition: all 0.2s ease;
    }
    
    .input-field:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      outline: none;
    }
    
    .password-wrapper {
      position: relative;
    }
    
    .password-toggle {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      cursor: pointer;
      transition: color 0.2s ease;
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
      margin-top: 1rem;
    }
    
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(79, 70, 229, 0.15);
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    }
    
    .btn-secondary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.625rem 1.25rem;
      background: white;
      border: 1px solid var(--primary-light);
      color: var(--primary);
      border-radius: 0.5rem;
      font-weight: 500;
      transition: all 0.3s ease;
      text-decoration: none;
      margin-top: 1rem;
    }
    
    .btn-secondary:hover {
      background: rgba(79, 70, 229, 0.05);
      transform: translateY(-2px);
    }
    
    .login-footer {
      text-align: center;
      margin-top: 1.5rem;
      color: #6b7280;
      font-size: 0.875rem;
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
      border-left: 4px solid #ef4444;
      color: #ef4444;
      padding: 0.875rem 1rem;
      border-radius: 0.375rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: flex-start;
      animation: fadeIn 0.3s ease;
    }
    
    .alert-error i {
      margin-right: 0.5rem;
      margin-top: 0.125rem;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .form-decoration {
      position: fixed;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(99, 102, 241, 0.05);
      z-index: -1;
    }
    
    .decoration-1 {
      top: -50px;
      right: -50px;
    }
    
    .decoration-2 {
      bottom: -80px;
      left: -80px;
      width: 300px;
      height: 300px;
    }
  </style>
</head>
<body class="p-4">
  <!-- Decorative elements -->
  <div class="form-decoration decoration-1"></div>
  <div class="form-decoration decoration-2"></div>

  <div class="flex items-center justify-center flex-1">
    <div class="login-container">
      <div class="login-header">
        <div class="login-icon">
          <i class="fas fa-sign-in-alt"></i>
        </div>
        <h1 class="login-title">Welcome Back</h1>
        <p class="text-gray-500">Sign in to access your Eventify account</p>
      </div>

      <?php if (!empty($alert)): ?>
        <div class="alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo $alert; ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4" oninput="clearAlert()">
        <div class="input-group">
          <label class="input-label">Email Address</label>
          <input type="email" name="email" required class="input-field" placeholder="Enter your email" />
        </div>

        <div class="input-group">
          <label class="input-label">Password</label>
          <div class="password-wrapper">
            <input type="password" name="password" id="password" required 
                   class="input-field pr-10" placeholder="Enter your password" />
            <span onclick="togglePassword()" class="password-toggle">
              <i class="far fa-eye" id="toggleEye"></i>
            </span>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt mr-2"></i> Login
        </button>

        <div class="login-footer">
          Don't have an account? <a href="signup.php">Sign up here</a>
        </div>

        <div class="text-center">
          <a href="../home.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
          </a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passField = document.getElementById("password");
      const eye = document.getElementById("toggleEye");
      if (passField.type === "password") {
        passField.type = "text";
        eye.classList.remove("fa-eye");
        eye.classList.add("fa-eye-slash");
      } else {
        passField.type = "password";
        eye.classList.remove("fa-eye-slash");
        eye.classList.add("fa-eye");
      }
    }

    function clearAlert() {
      const alert = document.querySelector(".alert-error");
      if (alert) {
        alert.style.animation = "fadeOut 0.3s ease";
        setTimeout(() => alert.remove(), 300);
      }
    }
    
    // Add fadeOut animation dynamically
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-10px); }
      }
    `;
    document.head.appendChild(style);
  </script>
</body>
</html>