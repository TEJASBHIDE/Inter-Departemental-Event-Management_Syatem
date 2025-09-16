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
    $deadline = $_POST['enrollment_deadline'];
    $max_participants = $_POST['max_participants'] !== '' ? (int) $_POST['max_participants'] : null;

    if ($title && $desc && $date && $start_time && $end_time && $deadline) {

        $eventDateTime = strtotime($date . ' ' . $end_time);
        $now = time();

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $msg = "❌ Event date cannot be in the past.";
        } elseif (strtotime($deadline) > strtotime($date)) {
            $msg = "❌ Enrollment deadline cannot be after event date.";
        } elseif ($eventDateTime < $now) {
            $msg = "❌ Event end time cannot be in the past.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO events (event_title, event_description, event_date, event_time, event_end_time, max_participants, enrollment_deadline, department_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $desc, $date, $start_time, $end_time, $max_participants, $deadline, $department_id]);
            header("Location: manage_events.php?success=created");
            exit();
        }
    } else {
        $msg = "❌ Please fill all required fields.";
    }
}

// Handle Delete Event (Prevent deleting completed events)
if (isset($_GET['delete'])) {
    $eid = $_GET['delete'];

    $check = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = ?");
    $check->execute([$eid, $department_id]);
    $event = $check->fetch();

    if ($event) {
        $eventEndTime = strtotime($event['event_date'] . ' ' . $event['event_end_time']);
        if ($eventEndTime < time()) {
            $msg = "❌ Cannot delete completed events.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ? AND department_id = ?");
            $stmt->execute([$eid, $department_id]);
            header("Location: manage_events.php?success=deleted");
            exit();
        }
    }
}

// Handle Edit Event
if (isset($_POST['update'])) {
    $eid = $_POST['event_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $deadline = $_POST['enrollment_deadline'];
    $max_participants = $_POST['max_participants'] !== '' ? (int) $_POST['max_participants'] : null;

    if ($title && $desc && $date && $start_time && $end_time && $deadline) {

        $eventDateTime = strtotime($date . ' ' . $end_time);
        $now = time();

        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $msg = "❌ Event date cannot be in the past.";
        } elseif (strtotime($deadline) > strtotime($date)) {
            $msg = "❌ Enrollment deadline cannot be after event date.";
        } elseif ($eventDateTime < $now) {
            $msg = "❌ Event end time cannot be in the past.";
        } else {
            $stmt = $pdo->prepare("UPDATE events SET event_title = ?, event_description = ?, event_date = ?, event_time = ?, event_end_time = ?, max_participants = ?, enrollment_deadline = ? WHERE event_id = ? AND department_id = ?");
            $stmt->execute([$title, $desc, $date, $start_time, $end_time, $max_participants, $deadline, $eid, $department_id]);
            header("Location: manage_events.php?success=updated");
            exit();
        }
    } else {
        $msg = "❌ All fields are required.";
    }
}

// Fetch Events
$stmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? ORDER BY event_date DESC");
$stmt->execute([$department_id]);
$events = $stmt->fetchAll();

// Edit Event Fetch
$editEvent = null;
if (isset($_GET['edit'])) {
    $eid = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = ?");
    $stmt->execute([$eid, $department_id]);
    $editEvent = $stmt->fetch();
}

// Handle success messages
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'created')
        $msg = "✅ Event created successfully!";
    if ($_GET['success'] === 'updated')
        $msg = "✅ Event updated successfully!";
    if ($_GET['success'] === 'deleted')
        $msg = "✅ Event deleted successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Events - Department Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .sidebar {
            min-height: 100vh;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Mobile Nav -->
    <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600">Eventify Admin</h1>
        <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
    </div>

    <div class="flex">
        <?php include 'sidebar.php'; ?>
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>

        <!-- Main -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="bg-white rounded shadow p-6">
                <h1 class="text-2xl font-bold text-indigo-700 mb-4">Manage Events</h1>

                <?php if ($msg): ?>
                    <div id="alert" class="mb-4 bg-yellow-100 text-yellow-800 px-4 py-2 rounded"><?= $msg ?></div>
                <?php endif; ?>

                <!-- Event Form -->
                <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <input type="hidden" name="event_id" value="<?= $editEvent['event_id'] ?? '' ?>">

                    <div>
                        <label class="block text-sm mb-1">Event Title*</label>
                        <input type="text" name="title" required class="w-full border rounded px-3 py-2"
                            value="<?= htmlspecialchars($editEvent['event_title'] ?? '') ?>">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Event Date*</label>
                        <input type="date" name="date" required min="<?= date('Y-m-d') ?>"
                            class="w-full border rounded px-3 py-2" value="<?= $editEvent['event_date'] ?? '' ?>">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Start Time*</label>
                        <input type="time" name="start_time" required class="w-full border rounded px-3 py-2"
                            value="<?= $editEvent['event_time'] ?? '' ?>">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">End Time*</label>
                        <input type="time" name="end_time" required class="w-full border rounded px-3 py-2"
                            value="<?= $editEvent['event_end_time'] ?? '' ?>">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Enrollment Deadline*</label>
                        <input type="datetime-local" name="enrollment_deadline" required min="<?= date('Y-m-d') ?>"
                            class="w-full border rounded px-3 py-2"
                            value="<?= $editEvent['enrollment_deadline'] ?? '' ?>">
                    </div>

                    <div>
                        <label class="block text-sm mb-1">Max Participants (Optional)</label>
                        <input type="number" name="max_participants" min="1" class="w-full border rounded px-3 py-2"
                            value="<?= $editEvent['max_participants'] ?? '' ?>">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Description*</label>
                        <textarea name="description" required
                            class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editEvent['event_description'] ?? '') ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <?php if ($editEvent): ?>
                            <button type="submit" name="update"
                                class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update Event</button>
                            <a href="manage_events.php" class="ml-4 text-indigo-600 hover:underline">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="create"
                                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Create Event</button>
                        <?php endif; ?>
                    </div>
                </form>

                <!-- Events List -->
                <?php if ($events): ?>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($events as $event):
                            $isCompleted = strtotime($event['event_date'] . ' ' . $event['event_end_time']) < time();
                            ?>
                            <div class="border rounded shadow p-4 bg-gray-50 card <?= $isCompleted ? 'opacity-50' : '' ?>">
                                <h3 class="text-lg font-bold text-indigo-700 mb-1">
                                    <?= htmlspecialchars($event['event_title']) ?></h3>
                                <p class="text-gray-600 text-sm mb-1">📅 <?= $event['event_date'] ?> | ⏰
                                    <?= $event['event_time'] ?> - <?= $event['event_end_time'] ?></p>
                                <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($event['event_description']) ?></p>
                                <p class="text-gray-500 text-xs mb-1">Max Participants:
                                    <?= $event['max_participants'] ?? 'No Limit' ?></p>
                                <p class="text-gray-500 text-xs mb-2">Enrollment Deadline: <?= $event['enrollment_deadline'] ?>
                                </p>

                                <?php if ($isCompleted): ?>
                                    <span class="text-green-600 font-semibold">✅ Completed</span>
                                <?php else: ?>
                                    <div class="flex space-x-3 mt-2">
                                        <a href="manage_events.php?edit=<?= $event['event_id'] ?>"
                                            class="text-sm text-yellow-600 hover:underline">Edit</a>
                                        <a href="manage_events.php?delete=<?= $event['event_id'] ?>"
                                            class="text-sm text-red-500 hover:underline"
                                            onclick="return confirm('Are you sure to delete this event?')">Delete</a>
                                    </div>
                                <?php endif; ?>
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

        // Hide alerts after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) alert.remove();
        }, 2000);
    </script>

</body>

</html>