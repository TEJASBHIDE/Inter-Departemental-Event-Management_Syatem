<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['department_id'])) {
  header("Location: dept_login.php");
  exit();
}
$department_id = $_SESSION['department_id'];

// Fetch all events for department
$eventsStmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? ORDER BY event_date DESC");
$eventsStmt->execute([$department_id]);
$events = $eventsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Event Feedbacks - Department Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .sidebar { min-height: 100vh; }
    .card {
      transition: all 0.3s ease;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    .card:hover {
      box-shadow: 0 10px 15px rgba(79, 70, 229, 0.1);
      transform: translateY(-2px);
    }
    .feedback-row:hover {
      background-color: #f8fafc;
    }
    .search-container {
      transition: all 0.3s ease;
    }
    .search-container:focus-within {
      box-shadow: 0 0 0 3px rgba(199, 210, 254, 0.5);
    }
    .empty-state {
      background-color: #f9fafb;
      border: 1px dashed #e5e7eb;
    }
    .feedback-text {
      max-width: 300px;
      white-space: pre-wrap;
      word-break: break-word;
    }
  </style>
</head>
<body class="bg-gray-50">
<!-- Mobile Nav Toggle -->
<div class="md:hidden bg-white shadow-lg px-4 py-3 flex justify-between items-center sticky top-0 z-30">
  <h1 class="text-xl font-bold text-indigo-600">Eventify Admin</h1>
    <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
</div>
<div class="flex">
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>
  <!-- Overlay -->
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>
  <!-- Main Content -->
  <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-indigo-700">Event Feedbacks</h1>
      </div>
      
      <?php if ($events): ?>
        <div class="space-y-6">
          <?php foreach ($events as $event): ?>
            <div class="card border border-gray-200 rounded-xl p-5 bg-white">
              <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 gap-3">
                <div>
                  <h2 class="text-xl font-semibold text-gray-800">
                    <?= htmlspecialchars($event['event_title']) ?>
                  </h2>
                  <p class="text-gray-600">
                    <i class="far fa-calendar-alt mr-1"></i> <?= $event['event_date'] ?> 
                    <i class="far fa-clock ml-3 mr-1"></i> <?= $event['event_time'] ?>
                  </p>
                </div>
                <div class="search-container relative w-full md:w-64">
                  <input 
                    type="text" 
                    placeholder="Search feedbacks..." 
                    class="search-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    data-event-id="<?= $event['event_id'] ?>"
                    onkeyup="searchFeedbacks(this)"
                  >
                  <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
              </div>
              
              <?php
              $feedbackStmt = $pdo->prepare("
                SELECT f.feedback_text, s.full_name, s.email, f.submitted_at
                FROM feedback f
                JOIN students s ON f.student_id = s.student_id
                WHERE f.event_id = ?
                ORDER BY f.submitted_at DESC
              ");
              $feedbackStmt->execute([$event['event_id']]);
              $feedbacks = $feedbackStmt->fetchAll();
              ?>
              
              <div class="overflow-x-auto">
                <?php if ($feedbacks): ?>
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-indigo-50">
                      <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Feedback</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Submitted</th>
                      </tr>
                    </thead>
                    <tbody id="feedbacks-<?= $event['event_id'] ?>" class="bg-white divide-y divide-gray-200">
                      <?php $count = 1; foreach ($feedbacks as $fb): ?>
                        <tr class="feedback-row">
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $count++ ?></td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($fb['full_name']) ?>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= htmlspecialchars($fb['email']) ?>
                          </td>
                          <td class="px-6 py-4 text-sm text-gray-500 feedback-text">
                            <?= htmlspecialchars($fb['feedback_text']) ?>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex items-center">
                              <i class="far fa-clock mr-1 text-gray-400"></i>
                              <?= $fb['submitted_at'] ?>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                <?php else: ?>
                  <div class="empty-state rounded-lg text-center py-8 my-2">
                    <i class="fas fa-comment-slash text-4xl text-gray-300 mb-3"></i>
                    <p class="text-gray-600">No feedback submitted for this event.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state rounded-lg text-center py-12">
          <i class="fas fa-calendar-times text-5xl text-gray-300 mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No events found</h3>
          <p class="text-gray-500 mt-1">Your department hasn't created any events yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

function searchFeedbacks(input) {
  const eventId = input.getAttribute('data-event-id');
  const searchTerm = input.value.toLowerCase();
  const rows = document.querySelectorAll(`#feedbacks-${eventId} tr`);
  
  rows.forEach(row => {
    const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
    const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
    const feedback = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
    
    if (name.includes(searchTerm) || email.includes(searchTerm) || feedback.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}
</script>
</body>
</html>