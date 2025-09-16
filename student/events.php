<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
  header("Location: login.php");
  exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$msg = "";

// Handle enrollment
if (isset($_GET['enroll'])) {
  $eid = $_GET['enroll'];

  // Check if event is completed
  $checkEvent = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = (SELECT department_id FROM students WHERE student_id = ?)");
  $checkEvent->execute([$eid, $student_id]);
  $eventData = $checkEvent->fetch();

  if ($eventData) {
    $eventEndTime = strtotime($eventData['event_date'] . ' ' . $eventData['event_end_time']);
    $enrollmentDeadline = strtotime($eventData['enrollment_deadline']);

    if ($eventEndTime < time()) {
      $msg = "❌ Cannot enroll, event already completed.";
    } elseif ($enrollmentDeadline < time()) {
      $msg = "⚠️ Enrollment deadline has passed.";
    } else {
      // Check if max participants reached
      if ($eventData['max_participants'] !== null) {
        $countEnrollments = $pdo->prepare("SELECT COUNT(*) FROM event_enrollments WHERE event_id = ?");
        $countEnrollments->execute([$eid]);
        $currentParticipants = $countEnrollments->fetchColumn();
        
        if ($currentParticipants >= $eventData['max_participants']) {
          $msg = "⚠️ Event has reached maximum participants limit.";
          header("Location: events.php?msg=" . urlencode($msg));
          exit();
        }
      }

      $check = $pdo->prepare("SELECT * FROM event_enrollments WHERE student_id = ? AND event_id = ?");
      $check->execute([$student_id, $eid]);
      if ($check->rowCount() === 0) {
        $stmt = $pdo->prepare("INSERT INTO event_enrollments (student_id, event_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $eid]);
        $msg = "✅ Enrolled successfully.";
      }
    }
  }
  header("Location: events.php?msg=" . urlencode($msg));
  exit();
}

// Handle unenrollment
if (isset($_GET['unenroll'])) {
  $eid = $_GET['unenroll'];
  $stmt = $pdo->prepare("DELETE FROM event_enrollments WHERE student_id = ? AND event_id = ?");
  $stmt->execute([$student_id, $eid]);
  $msg = "✅ Unenrolled successfully.";
  header("Location: events.php?msg=" . urlencode($msg));
  exit();
}

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch events with sorting (upcoming first, then completed)
$query = "SELECT e.*, 
          (SELECT COUNT(*) FROM event_enrollments ee WHERE ee.event_id = e.event_id) AS current_participants,
          CASE WHEN CONCAT(e.event_date, ' ', e.event_end_time) < NOW() THEN 1 ELSE 0 END AS is_completed
          FROM events e 
          WHERE e.department_id = (SELECT department_id FROM students WHERE student_id = ?)";

if ($search) {
  $query .= " AND e.event_title LIKE ?";
}

$query .= " ORDER BY is_completed ASC, e.event_date ASC, e.event_time ASC";

$stmt = $pdo->prepare($query);

if ($search) {
  $stmt->execute([$student_id, "%$search%"]);
} else {
  $stmt->execute([$student_id]);
}

$events = $stmt->fetchAll();

// Show messages from URL
if (isset($_GET['msg'])) {
  $msg = $_GET['msg'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>View Events - Eventify</title>
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
    
    .card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-radius: 0.75rem;
      overflow: hidden;
      background: white;
      position: relative;
    }
    
    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      padding: 1rem;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .event-status {
      position: absolute;
      top: 1rem;
      right: 1rem;
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .completed {
      background-color: #ecfdf5;
      color: #059669;
    }
    
    .enrollment-closed {
      background-color: #ffedd5;
      color: #ea580c;
    }
    
    .full-event {
      background-color: #fee2e2;
      color: #dc2626;
    }
    
    .btn-enroll {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-enroll-primary {
      background-color: var(--primary);
      color: white;
    }
    
    .btn-enroll-primary:hover {
      background-color: var(--primary-dark);
    }
    
    .btn-enroll-secondary {
      background-color: white;
      border: 1px solid #e2e8f0;
      color: var(--primary);
    }
    
    .btn-enroll-secondary:hover {
      background-color: #f8fafc;
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
    
    .alert-message {
      animation: slideIn 0.3s ease-out;
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
    
    .event-icon {
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      background: rgba(79, 70, 229, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin-right: 1rem;
    }
    
    .event-detail {
      display: flex;
      align-items: center;
      margin-bottom: 0.5rem;
      color: #4b5563;
    }
    
    .event-detail i {
      margin-right: 0.5rem;
      color: var(--primary);
    }
    
    .search-container {
      position: relative;
      max-width: 400px;
    }
    
    .search-input {
      padding-left: 2.5rem;
      border-radius: 2rem;
      border: 1px solid #e2e8f0;
      transition: all 0.3s ease;
      width: 100%;
    }
    
    .search-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    .search-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #64748b;
    }
    
    .participants-count {
      font-size: 0.75rem;
      color: #6b7280;
    }
    
    .participants-count.full {
      color: #dc2626;
      font-weight: 500;
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
        <div class="event-icon">
          <i class="fas fa-calendar-check"></i>
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
        <a href="events.php" class="active">
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
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center mb-4 md:mb-0">
              <i class="fas fa-calendar-day text-indigo-600 mr-3"></i> All Events
            </h1>
            <div class="flex items-center mb-10 pb-6 border-b border-indigo-900"></div>
            <!-- Search Bar -->
            <form method="GET" class="search-container">
              <div class="relative">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" placeholder="Search events..." 
                  class="form-input search-input py-2 pl-10 pr-4" 
                  value="<?= htmlspecialchars($search) ?>">
                <?php if ($search): ?>
                  <a href="events.php" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>

          <?php if ($msg): ?>
            <div id="alert" class="alert-message mb-6 px-4 py-3 rounded-lg flex items-center
              <?php 
                echo strpos($msg, '❌') !== false ? 'bg-red-50 text-red-800' : 
                     (strpos($msg, '⚠️') !== false ? 'bg-yellow-50 text-yellow-800' : 'bg-green-50 text-green-800');
              ?>">
              <i class="fas <?php 
                echo strpos($msg, '❌') !== false ? 'fa-exclamation-circle mr-2' : 
                     (strpos($msg, '⚠️') !== false ? 'fa-exclamation-triangle mr-2' : 'fa-check-circle mr-2');
              ?>"></i>
              <span><?= htmlspecialchars($msg) ?></span>
            </div>
          <?php endif; ?>

          <?php if (count($events) > 0): ?>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              <?php foreach ($events as $event):
                $eid = $event['event_id'];
                $isCompleted = strtotime($event['event_date'] . ' ' . $event['event_end_time']) < time();
                $isEnrollmentClosed = strtotime($event['enrollment_deadline']) < time();
                $isFull = ($event['max_participants'] !== null && $event['current_participants'] >= $event['max_participants']);
                $enrollCheck = $pdo->prepare("SELECT * FROM event_enrollments WHERE student_id = ? AND event_id = ?");
                $enrollCheck->execute([$student_id, $eid]);
                ?>
                <div class="card <?= $isCompleted ? 'opacity-80' : '' ?>">
                  <div class="card-header">
                    <h3 class="text-lg font-semibold"><?= htmlspecialchars($event['event_title']) ?></h3>
                  </div>
                  <div class="card-body">
                    <?php if ($isCompleted): ?>
                      <span class="event-status completed">
                        <i class="fas fa-check-circle mr-1"></i> Completed
                      </span>
                    <?php elseif ($isEnrollmentClosed): ?>
                      <span class="event-status enrollment-closed">
                        <i class="fas fa-clock mr-1"></i> Enrollment Closed
                      </span>
                    <?php elseif ($isFull): ?>
                      <span class="event-status full-event">
                        <i class="fas fa-user-slash mr-1"></i> Full
                      </span>
                    <?php endif; ?>
                    
                    <div class="event-detail">
                      <i class="fas fa-calendar-day"></i>
                      <span><?= date('F j, Y', strtotime($event['event_date'])) ?></span>
                    </div>
                    
                    <div class="event-detail">
                      <i class="fas fa-clock"></i>
                      <span><?= date('g:i A', strtotime($event['event_time'])) ?> - <?= date('g:i A', strtotime($event['event_end_time'])) ?></span>
                    </div>
                    
                    <p class="text-gray-600 text-sm mb-4 mt-3"><?= htmlspecialchars($event['event_description']) ?></p>
                    
                    <div class="flex justify-between items-center text-xs text-gray-500">
                      <div>
                        <i class="fas fa-users mr-1"></i>
                        <span class="participants-count <?= $isFull ? 'full' : '' ?>">
                          <?= $event['current_participants'] ?>/<?= $event['max_participants'] ?? '∞' ?>
                        </span>
                      </div>
                      <div>
                        <i class="fas fa-hourglass-end mr-1"></i>
                        <span>Enroll by <?= date('M j, g:i A', strtotime($event['enrollment_deadline'])) ?></span>
                      </div>
                    </div>
                    
                    <div class="mt-4">
                      <?php if ($isCompleted): ?>
                        <span class="text-sm text-gray-500">Event has ended</span>
                      <?php elseif ($isEnrollmentClosed): ?>
                        <span class="text-sm text-orange-500">Enrollment closed</span>
                      <?php elseif ($isFull): ?>
                        <span class="text-sm text-red-500">Event is full</span>
                      <?php else: ?>
                        <?php if ($enrollCheck->rowCount() > 0): ?>
                          <a href="events.php?unenroll=<?= $eid ?>" class="btn-enroll btn-enroll-secondary">
                            <i class="fas fa-user-minus mr-2"></i> Unenroll
                          </a>
                        <?php else: ?>
                          <a href="events.php?enroll=<?= $eid ?>" class="btn-enroll btn-enroll-primary">
                            <i class="fas fa-user-plus mr-2"></i> Enroll Now
                          </a>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="text-center py-12">
              <div class="mx-auto w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-calendar-times text-indigo-600 text-3xl"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">
                <?= $search ? 'No matching events found' : 'No events found' ?>
              </h3>
              <p class="text-gray-500">
                <?= $search ? 'Try a different search term' : 'There are currently no events scheduled for your department.' ?>
              </p>
              <?php if ($search): ?>
                <a href="events.php" class="text-indigo-600 hover:underline mt-2 inline-block">
                  Clear search
                </a>
              <?php endif; ?>
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