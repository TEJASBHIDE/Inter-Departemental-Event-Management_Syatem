<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['department_id'])) {
  header("Location: dept_login.php");
  exit();
}

$department_id = $_SESSION['department_id'];

// Fetch events for this department
$eventsStmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? ORDER BY event_date DESC");
$eventsStmt->execute([$department_id]);
$events = $eventsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enrolled Students - Department Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .sidebar { min-height: 100vh; }
  </style>
</head>
<body class="bg-gray-100">

<!-- Mobile Nav Toggle -->
<div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
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
    <div class="bg-white rounded shadow p-6">
      <h1 class="text-2xl font-bold text-indigo-700 mb-4">Enrolled Students</h1>

      <?php if ($events): ?>
        <?php foreach ($events as $event): ?>

          <div class="mb-6 border rounded p-4 bg-gray-50 shadow">
            <h2 class="text-lg font-semibold text-gray-800 mb-2">
              <?= htmlspecialchars($event['event_title']) ?> - <?= $event['event_date'] ?> <?= $event['event_time'] ?>
            </h2>

            <?php
$studentsStmt = $pdo->prepare("
  SELECT s.full_name, s.email
  FROM event_enrollments ee
  JOIN students s ON ee.student_id = s.student_id
  WHERE ee.event_id = ?
");
$studentsStmt->execute([$event['event_id']]);
$students = $studentsStmt->fetchAll();
?>


            <?php if ($students): ?>
              <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                  <thead class="bg-indigo-100">
                    <tr>
                      <th class="px-4 py-2">#</th>
                      <th class="px-4 py-2">Student Name</th>
                      <th class="px-4 py-2">Email</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $count = 1; foreach ($students as $stu): ?>
                      <tr class="border-t">
                        <td class="px-4 py-2"><?= $count++ ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($stu['full_name']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($stu['email']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <p class="text-gray-600">No students enrolled for this event.</p>
            <?php endif; ?>
          </div>

        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-600">No events found for your department.</p>
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
</script>


</body>
</html>
