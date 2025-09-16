<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Fetch eligible events (upcoming or past within 2 days)
$stmt = $pdo->prepare("SELECT e.event_id, e.event_title, e.event_date
  FROM event_enrollments r
  JOIN events e ON r.event_id = e.event_id
  WHERE r.student_id = ? AND (
    e.event_date >= CURDATE() OR e.event_date >= DATE_SUB(CURDATE(), INTERVAL 2 DAY)
  )
  ORDER BY e.event_date DESC
");
$stmt->execute([$student_id]);
$events = $stmt->fetchAll();

$success = $error = "";

// Submit feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $event_id = $_POST['event_id'] ?? '';
  $message = trim($_POST['feedback'] ?? '');

  if ($event_id && $message) {
    $check = $pdo->prepare("SELECT * FROM feedback WHERE student_id = ? AND event_id = ?");
    $check->execute([$student_id, $event_id]);

    if ($check->rowCount() === 0) {
      $insert = $pdo->prepare("INSERT INTO feedback (student_id, event_id, feedback_text) VALUES (?, ?, ?)");
      $insert->execute([$student_id, $event_id, $message]);
      $success = "Feedback submitted successfully.";
    } else {
      $error = "You have already submitted feedback for this event.";
    }
  } else {
    $error = "Please select an event and enter feedback.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Submit Feedback - Eventify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --success: #10b981;
      --error: #ef4444;
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
    
    .feedback-container {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      position: relative;
    }
    
    .feedback-container::before {
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
    
    /* .form-select {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
      background-position: right 0.75rem center;
      background-repeat: no-repeat;
      background-size: 1.5em 1.5em;
    } */
    
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
    
    .floating-label select, 
    .floating-label textarea {
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
    
    .floating-label select:focus + label,
    .floating-label select:valid + label,
    .floating-label textarea:focus + label,
    .floating-label textarea:not(:placeholder-shown) + label {
      top: 0.5rem;
      font-size: 0.65rem;
      color: var(--primary);
    }
    
    .page-header {
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
        <a href="feedback.php" class="active">
          <i class="fas fa-comment-alt"></i> Feedback
        </a>
        <a href="suggest_event.php" class="hover:text-indigo-600">
          <i class="fas fa-comments"></i> Suggest Events
        </a>
        <a href="profile.php" class="hover:text-indigo-600">
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
        <div class="page-header">
          <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-comment-alt text-indigo-600 mr-3"></i> Submit Feedback
          </h1>
          <p class="text-gray-600 mt-1">Share your thoughts about recent events</p>
        </div>

        <div class="feedback-container p-6">
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

          <form action="" method="POST" class="space-y-6">
            <div class="floating-label">
              <select name="event_id" id="event_id" required class="form-input form-select">
                <option value="" selected disabled></option>
                <?php foreach ($events as $event): ?>
                  <option value="<?= $event['event_id'] ?>">
                    <?= htmlspecialchars($event['event_title']) ?> (<?= date('M j, Y', strtotime($event['event_date'])) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <label for="event_id">Select Event</label>
            </div>

            <div class="floating-label">
              <textarea name="feedback" id="feedback" required 
                class="form-input" placeholder=" "></textarea>
              <label for="feedback">Your Feedback</label>
            </div>

            <div class="pt-2">
              <button type="submit" class="btn-primary w-full py-3 flex items-center justify-center">
                <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
              </button>
            </div>
          </form>
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