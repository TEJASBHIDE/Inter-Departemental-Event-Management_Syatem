<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Fetch current student data
$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$success = $error = "";

// Handle password change
if (isset($_POST['change_pass'])) {
  $old_pass = $_POST['old_pass'] ?? '';
  $new_pass = $_POST['new_pass'] ?? '';

  if (password_verify($old_pass, $student['password'])) {
    $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE students SET password = ? WHERE student_id = ?");
    $update->execute([$hashed, $student_id]);
    $success = "Password changed successfully.";
  } else {
    $error = "Incorrect old password.";
  }
}

// Handle profile deletion
if (isset($_POST['delete_confirm']) && $_POST['delete_confirm'] === "YES_DELETE") {
  $delete = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
  $delete->execute([$student_id]);
  session_destroy();
  header("Location: ../public/index.php?msg=Account deleted successfully.");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile Settings - Eventify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --success: #10b981;
      --error: #ef4444;
      --danger: #dc2626;
    }
    
    body {
      background-color: #f8fafc;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
    }
    
    .sidebar {
      min-height: 100vh;
      background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);
      border-right: 1px solid #e2e8f0;
    }
    
    .sidebar-nav a {
      display: flex;
      align-items: center;
      padding: 0.75rem 1rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease;
    }
    
    .sidebar-nav a:hover {
      background-color: #f1f5f9;
      color: var(--primary);
    }
    
    .sidebar-nav a.active {
      background-color: #eef2ff;
      color: var(--primary);
      font-weight: 500;
    }
    
    .sidebar-nav a i {
      margin-right: 0.75rem;
      width: 1.25rem;
      text-align: center;
    }
    
    .mobile-nav {
      background: white;
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    
    .mobile-nav-toggle {
      transition: all 0.2s ease;
    }
    
    .mobile-nav-toggle:hover {
      color: var(--primary);
    }
    
    .overlay {
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 30;
    }
    
    .profile-container {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      position: relative;
    }
    
    .profile-container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 6px;
      background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }
    
    .form-input {
      transition: all 0.2s ease;
      border: 1px solid #e2e8f0;
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      width: 100%;
    }
    
    .form-input:focus {
      border-color: var(--primary-light);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
      outline: none;
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
    
    .btn-danger {
      background: linear-gradient(135deg, var(--danger), #ef4444);
      transition: all 0.3s ease;
      border-radius: 0.5rem;
      color: white;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
    }
    
    .btn-danger:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(220, 38, 38, 0.15);
      background: linear-gradient(135deg, #b91c1c, var(--danger));
    }
    
    .alert {
      padding: 0.875rem 1rem;
      border-radius: 0.5rem;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      animation: slideIn 0.3s ease-out;
    }
    
    .alert-success {
      background-color: rgba(16, 185, 129, 0.1);
      border-left: 4px solid var(--success);
      color: var(--success);
    }
    
    .alert-error {
      background-color: rgba(239, 68, 68, 0.1);
      border-left: 4px solid var(--error);
      color: var(--error);
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
    
    .fade-out {
      animation: fadeOut 0.5s ease-out;
    }
    
    @keyframes fadeOut {
      to {
        opacity: 0;
        height: 0;
        padding: 0;
        margin: 0;
      }
    }
    
    .floating-label {
      position: relative;
      margin-bottom: 1.5rem;
    }
    
    .floating-label input {
      padding-top: 1.5rem;
    }
    
    .floating-label label {
      position: absolute;
      top: 0.75rem;
      left: 1rem;
      font-size: 0.75rem;
      color: #6b7280;
      transition: all 0.2s ease;
      pointer-events: none;
    }
    
    .floating-label input:focus + label,
    .floating-label input:not(:placeholder-shown) + label {
      top: 0.5rem;
      font-size: 0.65rem;
      color: var(--primary);
    }
    
    .profile-section {
      padding: 1.5rem;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .profile-section:last-child {
      border-bottom: none;
    }
    
    .profile-header {
      background: linear-gradient(135deg, #f9f9ff 0%, #f0f4ff 100%);
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body class="min-h-screen">

  <!-- Mobile Nav -->
  <div class="mobile-nav md:hidden px-4 py-3 flex justify-between items-center fixed w-full top-0 z-50">
    <h1 class="text-xl font-bold text-indigo-600 flex items-center">
      <i class="fas fa-calendar-alt mr-2"></i> Eventify
    </h1>
    <button onclick="toggleSidebar()" class="mobile-nav-toggle text-gray-600 focus:outline-none">
      <i class="fas fa-bars text-xl"></i>
    </button>
  </div>

  <div class="flex pt-16 md:pt-0">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="sidebar w-64 px-6 py-8 fixed top-0 left-0 z-40 h-full transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
      <div class="mb-8 flex items-center">
        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
          <i class="fas fa-calendar-check text-indigo-600"></i>
        </div>
        <div>
          <h2 class="text-xl font-bold text-indigo-600">Eventify</h2>
          <p class="text-sm text-gray-500">Welcome, <?= htmlspecialchars($student_name) ?></p>
        </div>
      </div>
      <nav class="sidebar-nav space-y-1">
        <a href="dashboard.php" class="hover:text-indigo-600">
          <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="events.php" class="hover:text-indigo-600">
          <i class="fas fa-calendar-day"></i> View Events
        </a>
        <a href="history.php" class="hover:text-indigo-600">
          <i class="fas fa-history"></i> Participation History
        </a>
        
        <a href="feedback.php" class="hover:text-indigo-600">
          <i class="fas fa-comment-alt"></i> Feedback
        </a>
        <a href="suggest_event.php" class="hover:text-indigo-600">
          <i class="fas fa-comments"></i> Suggest Events
        </a>
        <a href="profile.php" class="active">
          <i class="fas fa-user"></i> Profile
        </a>
        <a href="logout.php" class="text-red-500 hover:text-red-600 mt-4">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </nav>
    </aside>

    <!-- Overlay for mobile -->
    <div id="overlay" class="overlay fixed inset-0 hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-6">
      <div class="max-w-2xl mx-auto">
        <div class="profile-header">
          <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-user-circle text-indigo-600 mr-3"></i> Profile Settings
          </h1>
          <p class="text-gray-600 mt-1">Manage your account and security settings</p>
        </div>

        <div class="profile-container">
          <?php if ($success): ?>
            <div id="alert" class="alert alert-success">
              <i class="fas fa-check-circle mr-2"></i>
              <span><?= $success ?></span>
            </div>
          <?php elseif ($error): ?>
            <div id="alert" class="alert alert-error">
              <i class="fas fa-exclamation-circle mr-2"></i>
              <span><?= $error ?></span>
            </div>
          <?php endif; ?>

          <!-- Account Info Section -->
          <div class="profile-section">
            <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
              <i class="fas fa-id-card text-indigo-600 mr-2"></i> Account Information
            </h2>
            <div class="space-y-3">
              <div>
                <p class="text-sm font-medium text-gray-500">Full Name</p>
                <p class="text-gray-800"><?= htmlspecialchars($student_name) ?></p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Email</p>
                <p class="text-gray-800"><?= htmlspecialchars($student['email']) ?></p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Department</p>
                <p class="text-gray-800">
                  <?php 
                    $dept = $pdo->prepare("SELECT department_name FROM departments WHERE department_id = ?");
                    $dept->execute([$student['department_id']]);
                    echo htmlspecialchars($dept->fetchColumn());
                  ?>
                </p>
              </div>
            </div>
          </div>

          <!-- Change Password Section -->
          <div class="profile-section">
            <h2 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
              <i class="fas fa-lock text-indigo-600 mr-2"></i> Change Password
            </h2>
            <form method="POST" class="space-y-4">
              <div class="floating-label">
                <input type="password" name="old_pass" id="old_pass" required class="form-input" placeholder=" ">
                <label for="old_pass">Current Password</label>
              </div>
              <div class="floating-label">
                <input type="password" name="new_pass" id="new_pass" required class="form-input" placeholder=" ">
                <label for="new_pass">New Password</label>
              </div>
              <div class="pt-2">
                <button type="submit" name="change_pass" class="btn-primary w-full py-3 flex items-center justify-center">
                  <i class="fas fa-key mr-2"></i> Update Password
                </button>
              </div>
            </form>
          </div>

          <!-- Delete Account Section -->
          <div class="profile-section">
            <h2 class="text-lg font-medium text-red-700 mb-4 flex items-center">
              <i class="fas fa-exclamation-triangle mr-2"></i> Delete Account
            </h2>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <i class="fas fa-exclamation-circle text-red-500"></i>
                </div>
                <div class="ml-3">
                  <p class="text-sm text-red-700">
                    Warning: Deleting your account will permanently remove all your data including event participation and feedback. This action cannot be undone.
                  </p>
                </div>
              </div>
            </div>
            <form method="POST" onsubmit="return confirmDelete()">
              <input type="hidden" name="delete_confirm" value="YES_DELETE">
              <button type="submit" class="btn-danger w-full py-3 flex items-center justify-center">
                <i class="fas fa-trash-alt mr-2"></i> Delete My Account
              </button>
            </form>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      sidebar.classList.toggle('-translate-x-full');
      overlay.classList.toggle('hidden');
      document.body.classList.toggle('overflow-hidden');
    }
    
    function confirmDelete() {
      return confirm("Are you absolutely sure you want to delete your account? This action cannot be undone.");
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
      const alert = document.getElementById('alert');
      if (alert) {
        alert.classList.add('fade-out');
        setTimeout(() => alert.remove(), 500);
      }
    }, 5000);
  </script>
</body>
</html>