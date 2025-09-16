<?php
require_once '../includes/db.php'; // database connection
session_start();

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $full_name = trim($_POST['full_name']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $department_id = $_POST['department_id'];

  // Validate fields
  if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($department_id)) {
    $alert = 'All fields are required.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $alert = 'Invalid email format.';
  } elseif ($password !== $confirm_password) {
    $alert = 'Passwords do not match.';
  } else {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
      $alert = 'Email is already registered.';
    } else {
      // Insert new student
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $insert = $pdo->prepare("INSERT INTO students (full_name, email, password, department_id) VALUES (?, ?, ?, ?)");
      $success = $insert->execute([$full_name, $email, $hashed_password, $department_id]);

      if ($success) {
        $_SESSION['success'] = "Account created! You can now login.";
        header("Location: login.php");
        exit();
      } else {
        $alert = "Something went wrong. Please try again.";
      }
    }
  }
}

// Fetch departments
$departments = $pdo->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Signup - Eventify</title>
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
    }
    
    .form-container {
      max-width: 500px;
      width: 100%;
      margin: 2rem auto;
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
      overflow: hidden;
      position: relative;
    }
    
    .form-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 8px;
      background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }
    
    .form-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    
    .input-field {
      transition: all 0.3s ease;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      width: 100%;
    }
    
    .input-field:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      outline: none;
    }
    
    .password-toggle {
      transition: all 0.3s ease;
      cursor: pointer;
    }
    
    .password-toggle:hover {
      color: var(--primary);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      transition: all 0.3s ease;
      border-radius: 0.5rem;
      color: white;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
      box-shadow: 0 4px 6px rgba(79, 70, 229, 0.1);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(79, 70, 229, 0.15);
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
    }
    
    .btn-secondary {
      background: white;
      border: 1px solid var(--primary-light);
      color: var(--primary);
      transition: all 0.3s ease;
      border-radius: 0.5rem;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
    }
    
    .btn-secondary:hover {
      background: rgba(79, 70, 229, 0.05);
      transform: translateY(-2px);
    }
    
    .alert-error {
      background: rgba(239, 68, 68, 0.1);
      border-left: 4px solid #ef4444;
      color: #ef4444;
      padding: 0.75rem 1rem;
      border-radius: 0.25rem;
      animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .select-field {
      appearance: none;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.75rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
    }
    
    .floating-label {
      position: relative;
      margin-bottom: 1.5rem;
    }
    
    .floating-label input, .floating-label select {
      padding-top: 1.25rem;
    }
    
    .floating-label label {
      position: absolute;
      top: 0.5rem;
      left: 1rem;
      font-size: 0.75rem;
      color: #6b7280;
      transition: all 0.2s ease;
      pointer-events: none;
    }
    
    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label,
    .floating-label select:valid + label {
      top: 0.25rem;
      font-size: 0.65rem;
      color: var(--primary);
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
    
    .form-content {
      padding: 2rem;
    }
    
    @media (min-width: 640px) {
      .form-content {
        padding: 2.5rem;
      }
    }
  </style>
</head>
<body class="flex items-center justify-center p-4 min-h-screen">
  <!-- Decorative elements -->
  <div class="form-decoration decoration-1"></div>
  <div class="form-decoration decoration-2"></div>

  <div class="form-container">
    <div class="form-content">
      <div class="text-center mb-8">
        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-user-graduate text-indigo-600 text-2xl"></i>
        </div>
        <h2 class="text-3xl font-bold form-header mb-2">Create Your Account</h2>
        <p class="text-gray-500">Join Eventify to participate in departmental events</p>
      </div>

      <?php if (!empty($alert)): ?>
        <div class="alert-error mb-6 flex items-start">
          <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
          <span><?php echo $alert; ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-6" oninput="clearAlert()">
        <div class="floating-label">
          <input type="text" name="full_name" id="full_name" required 
                 class="input-field" placeholder=" " />
          <label for="full_name">Full Name</label>
        </div>

        <div class="floating-label">
          <input type="email" name="email" id="email" required 
                 class="input-field" placeholder=" " />
          <label for="email">Email Address</label>
        </div>

        <div class="floating-label">
          <div class="relative">
            <input type="password" name="password" id="password" required 
                   class="input-field pr-10" placeholder=" " />
            <label for="password">Password</label>
            <span onclick="togglePassword()" 
                  class="absolute right-3 top-3.5 text-gray-400 password-toggle">
              <i class="far fa-eye" id="toggleEye"></i>
            </span>
          </div>
        </div>

        <div class="floating-label">
          <div class="relative">
            <input type="password" name="confirm_password" id="confirm_password" required 
                   class="input-field pr-10" placeholder=" " />
            <label for="confirm_password">Confirm Password</label>
            <span onclick="toggleConfirm()" 
                  class="absolute right-3 top-3.5 text-gray-400 password-toggle">
              <i class="far fa-eye" id="toggleConfirmEye"></i>
            </span>
          </div>
        </div>

        <div class="floating-label">
          <select name="department_id" id="department_id" required class="input-field select-field">
            <option value="" selected disabled></option>
            <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <label for="department_id">Department</label>
        </div>

        <div class="pt-2">
          <button type="submit" class="btn-primary w-full py-3 flex items-center justify-center">
            <i class="fas fa-user-plus mr-2"></i> Create Account
          </button>
        </div>

        <div class="text-center text-sm text-gray-500 mt-6">
          Already have an account? 
          <a href="login.php" class="text-indigo-600 hover:underline font-medium">Login here</a>
        </div>
        
        <div class="text-center pt-4">
          <a href="../home.php" class="btn-secondary inline-flex items-center px-4 py-2">
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

    function toggleConfirm() {
      const passField = document.getElementById("confirm_password");
      const eye = document.getElementById("toggleConfirmEye");
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