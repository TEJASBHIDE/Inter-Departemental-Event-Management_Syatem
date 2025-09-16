<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['department_id'])) {
  header("Location: dept_login.php");
  exit();
}

$department_id = $_SESSION['department_id'];
$msg = '';

// Handle Create Event
if (isset($_POST['create'])) {
  $title = trim($_POST['title']);
  $desc = trim($_POST['description']);
  $date = $_POST['date'];
  $start_time = $_POST['start_time'];
  $end_time = $_POST['end_time'];
  $max_participants = $_POST['max_participants'] !== '' ? (int)$_POST['max_participants'] : null;

  if ($title && $desc && $date && $start_time && $end_time) {
    $stmt = $pdo->prepare("INSERT INTO events (event_title, event_description, event_date, event_time, event_end_time, max_participants, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $desc, $date, $start_time, $end_time, $max_participants, $department_id]);
    $msg = "Event created successfully!";
} else {
    $msg = "Please fill all required fields.";
}

}

// Handle Delete
if (isset($_GET['delete'])) {
  $eid = $_GET['delete'];
  $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ? AND department_id = ?");
  $stmt->execute([$eid, $department_id]);
  header("Location: manage_events.php");
  exit();
}

// Handle Edit Form Submission
if (isset($_POST['update'])) {
  $eid = $_POST['event_id'];
  $title = trim($_POST['title']);
  $desc = trim($_POST['description']);
  $date = $_POST['date'];
  $start_time = $_POST['start_time'];
  $end_time = $_POST['end_time'];
  $max_participants = $_POST['max_participants'] !== '' ? (int)$_POST['max_participants'] : null;

  if ($title && $desc && $date && $start_time && $end_time) {
    $stmt = $pdo->prepare("UPDATE events SET event_title = ?, event_description = ?, event_date = ?, event_time = ?, event_end_time = ?, max_participants = ? WHERE event_id = ? AND department_id = ?");
    $stmt->execute([$title, $desc, $date, $start_time, $end_time, $max_participants, $eid, $department_id]);
    $msg = "Event updated successfully!";
  } else {
    $msg = "All fields are required.";
  }
}

// Fetch Events
$stmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? ORDER BY event_date DESC");
$stmt->execute([$department_id]);
$events = $stmt->fetchAll();

// If editing, fetch event details
$editEvent = null;
if (isset($_GET['edit'])) {
  $eid = $_GET['edit'];
  $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = ?");
  $stmt->execute([$eid, $department_id]);
  $editEvent = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Events - Department Panel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .sidebar { min-height: 100vh; }
    .card:hover { transform: translateY(-4px); box-shadow: 0 4px 14px rgba(0,0,0,0.1); }
  </style>
</head>
<body class="bg-gray-100">

<!-- Mobile Nav Toggle -->
<div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
  <h1 class="text-xl font-bold text-indigo-600">Dept Panel</h1>
  <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
</div>

<div class="flex">
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>

  <!-- Main Content -->
  <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
    <div class="bg-white rounded shadow p-6">
      <h1 class="text-2xl font-bold text-indigo-700 mb-4">Manage Events</h1>

      <?php if ($msg): ?>
        <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded"><?= $msg ?></div>
      <?php endif; ?>

      <!-- Event Form -->
      <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <input type="hidden" name="event_id" value="<?= $editEvent['event_id'] ?? '' ?>">

        <div>
          <label class="block text-sm mb-1">Event Title*</label>
          <input type="text" name="title" required class="w-full border rounded px-3 py-2" value="<?= htmlspecialchars($editEvent['event_title'] ?? '') ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Event Date*</label>
          <input type="date" name="date" required class="w-full border rounded px-3 py-2" value="<?= $editEvent['event_date'] ?? '' ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">Start Time*</label>
          <input type="time" name="start_time" required class="w-full border rounded px-3 py-2" value="<?= $editEvent['event_time'] ?? '' ?>">
        </div>
        <div>
          <label class="block text-sm mb-1">End Time*</label>
          <input type="time" name="end_time" required class="w-full border rounded px-3 py-2" value="<?= $editEvent['event_end_time'] ?? '' ?>">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm mb-1">Description*</label>
          <textarea name="description" required class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editEvent['event_description'] ?? '') ?></textarea>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm mb-1">Max Participants (Optional)</label>
          <input type="number" name="max_participants" min="1" class="w-full border rounded px-3 py-2" value="<?= $editEvent['max_participants'] ?? '' ?>">
        </div>

        <div class="md:col-span-2">
          <?php if ($editEvent): ?>
            <button type="submit" name="update" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update Event</button>
            <a href="manage_events.php" class="ml-4 text-indigo-600 hover:underline">Cancel</a>
          <?php else: ?>
            <button type="submit" name="create" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Create Event</button>
          <?php endif; ?>
        </div>
      </form>

      <!-- Events List -->
      <?php if ($events): ?>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <?php foreach ($events as $event): ?>
            <div class="border rounded shadow p-4 bg-gray-50 card">
              <h3 class="text-lg font-bold text-indigo-700 mb-1"><?= htmlspecialchars($event['event_title']) ?></h3>
              <p class="text-gray-600 text-sm mb-1">📅 <?= $event['event_date'] ?> | ⏰ <?= $event['event_time'] ?> - <?= $event['event_end_time'] ?></p>
              <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($event['event_description']) ?></p>
              <p class="text-gray-500 text-xs mb-2">Max Participants: <?= $event['max_participants'] ?? 'No Limit' ?></p>

              <div class="flex space-x-3 mt-2">
                <a href="manage_events.php?edit=<?= $event['event_id'] ?>" class="text-sm text-yellow-600 hover:underline">Edit</a>
                <a href="manage_events.php?delete=<?= $event['event_id'] ?>" class="text-sm text-red-500 hover:underline" onclick="return confirm('Are you sure to delete this event?')">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-600">No events created yet.</p>
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
