<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Fetch event history
$stmt = $pdo->prepare("SELECT e.event_title, e.event_date, e.event_time, r.enrolled_at
  FROM event_enrollments r
  JOIN events e ON r.event_id = e.event_id
  WHERE r.student_id = ?
  ORDER BY e.event_date DESC
");
$stmt->execute([$student_id]);

$history = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Participation History - Eventify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
    }
    
    body {
      background-color: #f8fafc;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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
    
    .history-table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }
    
    .history-table thead th {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    
    .history-table th, 
    .history-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #e2e8f0;
    }
    
    .history-table tbody tr {
      transition: all 0.2s ease;
    }
    
    .history-table tbody tr:hover {
      background-color: #f8fafc;
      transform: translateX(4px);
    }
    
    .history-table tbody tr:nth-child(even) {
      background-color: #f9fafb;
    }
    
    .history-table tbody tr:hover {
      background-color: #f1f5f9;
    }
    
    .empty-state {
      background-color: #f8fafc;
      border: 1px dashed #e2e8f0;
      border-radius: 0.75rem;
      padding: 2rem;
      text-align: center;
    }
    
    .empty-state-icon {
      font-size: 3rem;
      color: #9ca3af;
      margin-bottom: 1rem;
    }
    
    .badge {
      display: inline-block;
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .badge-primary {
      background-color: #eef2ff;
      color: var(--primary);
    }
    
    .badge-secondary {
      background-color: #f5f3ff;
      color: #7c3aed;
    }
    
    .date-cell {
      min-width: 120px;
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
    <aside id="sidebar" class="sidebar w-64 px-6 py-8 fixed top-0 left-0 z-40 h-full transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
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
        <a href="history.php" class="active">
          <i class="fas fa-history"></i> Participation History
        </a>
        <a href="feedback.php" class="hover:text-indigo-600">
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
      <div class="max-w-7xl mx-auto">
        <div class="page-header">
          <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-history text-indigo-600 mr-3"></i> Participation History
          </h1>
          <p class="text-gray-600 mt-1">View all events you've participated in</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <?php if (count($history) > 0): ?>
            <div class="overflow-x-auto">
              <table class="history-table">
                <thead>
                  <tr>
                    <th class="pl-6 pr-4 py-3">Event Name</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">Enrollment Date</th>
                    <th class="pr-6 pl-4 py-3">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($history as $row): ?>
                    <tr>
                      <td class="pl-6 pr-4 py-4 font-medium text-gray-900">
                        <?= htmlspecialchars($row['event_title']) ?>
                      </td>
                      <td class="px-4 py-4 date-cell">
                        <span class="badge badge-primary">
                          <?= date('M j, Y', strtotime($row['event_date'])) ?>
                        </span>
                      </td>
                      <td class="px-4 py-4">
                        <?= date('g:i A', strtotime($row['event_time'])) ?>
                      </td>
                      <td class="px-4 py-4 date-cell">
                        <span class="badge badge-secondary">
                          <?= date('M j, Y', strtotime($row['enrolled_at'])) ?>
                        </span>
                      </td>
                      <td class="pr-6 pl-4 py-4">
                        <a href="feedback.php" class="text-indigo-600 hover:text-indigo-800 hover:underline text-sm">
                          <i class="fas fa-comment mr-1"></i> Feedback
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <div class="empty-state-icon">
                <i class="fas fa-history"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-900">No participation history yet</h3>
              <p class="mt-1 text-sm text-gray-500">Your event participation history will appear here.</p>
              <div class="mt-4">
                <a href="events.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <i class="fas fa-calendar-plus mr-2"></i> Browse Events
                </a>
              </div>
            </div>
          <?php endif; ?>
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
  </script>
</body>
</html>