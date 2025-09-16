<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Get upcoming enrolled events
$upcomingEvents = $pdo->prepare("
  SELECT e.* FROM events e
  JOIN event_enrollments ee ON e.event_id = ee.event_id
  WHERE ee.student_id = ? 
  AND e.event_date >= CURDATE()
  ORDER BY e.event_date ASC
  LIMIT 3
");
$upcomingEvents->execute([$student_id]);

// Get recent event participation
$recentParticipation = $pdo->prepare("
  SELECT e.* FROM events e
  JOIN event_enrollments ee ON e.event_id = ee.event_id
  WHERE ee.student_id = ? 
  AND e.event_date < CURDATE()
  ORDER BY e.event_date DESC
  LIMIT 3
");
$recentParticipation->execute([$student_id]);

// Get feedback stats
// $feedbackStats = $pdo->prepare("
//   SELECT 
//     COUNT(*) as total_feedback,
//     SUM(CASE WHEN is_anonymous = 1 THEN 1 ELSE 0 END) as anonymous_feedback
//   FROM feedback
//   WHERE student_id = ?
// ");
// $feedbackStats->execute([$student_id]);
// $feedbackData = $feedbackStats->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard - Eventify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --secondary: #f59e0b;
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
    
    .stat-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 0.75rem;
      background: white;
      position: relative;
      overflow: hidden;
    }
    
    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--primary-light));
    }
    
    .event-card {
      transition: all 0.3s ease;
      border-radius: 0.75rem;
      background: white;
    }
    
    .event-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .event-date {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      border-radius: 0.5rem;
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
      z-index: 40;
    }
    
    .dashboard-header {
      background: linear-gradient(135deg, #f9f9ff 0%, #f0f4ff 100%);
      border-radius: 0.75rem;
    }
    
    .empty-state {
      background-color: #f8fafc;
      border: 1px dashed #e2e8f0;
      border-radius: 0.75rem;
    }
    
    .progress-ring__circle {
      transition: stroke-dashoffset 0.35s;
      transform: rotate(-90deg);
      transform-origin: 50% 50%;
    }
  </style>
</head>

<body class="min-h-screen">

  <!-- Mobile nav -->
  <div class="mobile-nav md:hidden px-4 py-3 flex justify-between items-center sticky top-0 z-30">
    <h1 class="text-xl font-bold text-indigo-600 flex items-center">
      <i class="fas fa-calendar-alt mr-2"></i> Eventify
    </h1>
    <button onclick="toggleSidebar()" class="mobile-nav-toggle text-gray-600 focus:outline-none">
      <i class="fas fa-bars text-xl"></i>
    </button>
  </div>

  <div class="flex">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="sidebar w-64 px-6 py-8 fixed top-0 left-0 z-50 h-full transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
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
        <a href="dashboard.php" class="active">
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
        <a href="profile.php" class="hover:text-indigo-600">
          <i class="fas fa-user"></i> Profile
        </a>
        <a href="logout.php" class="text-red-500 hover:text-red-600 mt-4">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </nav>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div id="overlay" class="overlay fixed inset-0 hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-6">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Dashboard Header -->
        <div class="dashboard-header p-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-800">
                Welcome back, <?= htmlspecialchars(explode(' ', $student_name)[0]) ?>!
              </h1>
              <p class="text-gray-600">Here's what's happening with your events</p>
            </div>
            <div class="mt-4 md:mt-0">
              <a href="events.php" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-calendar-plus mr-2"></i> Browse Events
              </a>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-indigo-100 text-indigo-600 mr-4">
                <i class="fas fa-calendar-check text-xl"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Upcoming Events</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $upcomingEvents->rowCount() ?></p>
              </div>
            </div>
          </div>

          <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-amber-100 text-amber-600 mr-4">
                <i class="fas fa-history text-xl"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Past Participations</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $recentParticipation->rowCount() ?></p>
              </div>
            </div>
          </div>

          <div class="stat-card p-6 shadow-sm">
            <div class="flex items-center">
              <div class="p-3 rounded-lg bg-green-100 text-green-600 mr-4">
                <i class="fas fa-comment-dots text-xl"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Feedback Submitted</p>
                <p class="text-2xl font-semibold text-gray-900"><?= $feedbackData['total_feedback'] ?? 0 ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Upcoming Events Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
              <i class="fas fa-calendar-day text-indigo-600 mr-2"></i> Your Upcoming Events
            </h2>
          </div>
          <div class="divide-y divide-gray-200">
            <?php if ($upcomingEvents->rowCount() > 0): ?>
              <?php while ($event = $upcomingEvents->fetch()): ?>
                <div class="p-6 hover:bg-gray-50 event-card">
                  <div class="flex flex-col md:flex-row md:items-center">
                    <div class="event-date w-20 h-20 flex flex-col items-center justify-center mr-6 mb-4 md:mb-0">
                      <span class="text-2xl font-bold"><?= date('d', strtotime($event['event_date'])) ?></span>
                      <span class="text-xs uppercase"><?= date('M', strtotime($event['event_date'])) ?></span>
                    </div>
                    <div class="flex-1">
                      <h3 class="text-lg font-medium text-gray-900 mb-1"><?= htmlspecialchars($event['event_title']) ?></h3>
                      <p class="text-sm text-gray-500 mb-2">
                        <i class="fas fa-clock mr-1"></i> 
                        <?= date('g:i A', strtotime($event['event_time'])) ?> - <?= date('g:i A', strtotime($event['event_end_time'])) ?>
                      </p>
                      <p class="text-sm text-gray-600"><?= htmlspecialchars($event['event_description']) ?></p>
                    </div>
                    <div class="mt-4 md:mt-0">
                      <a href="events.php" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        View Details
                      </a>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="p-8 text-center empty-state">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No upcoming events</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't enrolled in any upcoming events yet.</p>
                <div class="mt-4">
                  <a href="events.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-calendar-plus mr-2"></i> Browse Events
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Recent Participation Section -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
              <i class="fas fa-history text-indigo-600 mr-2"></i> Recent Participation
            </h2>
          </div>
          <div class="divide-y divide-gray-200">
            <?php if ($recentParticipation->rowCount() > 0): ?>
              <?php while ($event = $recentParticipation->fetch()): ?>
                <div class="p-6 hover:bg-gray-50 event-card">
                  <div class="flex flex-col md:flex-row md:items-center">
                    <div class="w-20 h-20 flex flex-col items-center justify-center bg-gray-100 rounded-lg mr-6 mb-4 md:mb-0">
                      <span class="text-2xl font-bold text-gray-600"><?= date('d', strtotime($event['event_date'])) ?></span>
                      <span class="text-xs uppercase text-gray-500"><?= date('M', strtotime($event['event_date'])) ?></span>
                    </div>
                    <div class="flex-1">
                      <h3 class="text-lg font-medium text-gray-900 mb-1"><?= htmlspecialchars($event['event_title']) ?></h3>
                      <p class="text-sm text-gray-500 mb-2">
                        <i class="fas fa-clock mr-1"></i> 
                        <?= date('g:i A', strtotime($event['event_time'])) ?> - <?= date('g:i A', strtotime($event['event_end_time'])) ?>
                      </p>
                      <div class="flex space-x-4">
                        <a href="feedback.php?event_id=<?= $event['event_id'] ?>" class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline">
                          <i class="fas fa-comment mr-1"></i> Leave Feedback
                        </a>
                        <!-- <a href="#" class="text-sm text-gray-600 hover:text-gray-800 hover:underline">
                          <i class="fas fa-certificate mr-1"></i> View Certificate
                        </a> -->
                      </div>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <div class="p-8 text-center empty-state">
                <i class="fas fa-history text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-lg font-medium text-gray-900">No participation history</h3>
                <p class="mt-1 text-sm text-gray-500">Your event participation history will appear here.</p>
              </div>
            <?php endif; ?>
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
  </script>
</body>
</html>