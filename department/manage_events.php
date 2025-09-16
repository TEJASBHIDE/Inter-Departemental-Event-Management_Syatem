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
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? AND event_title LIKE ? ORDER BY event_date DESC");
    $stmt->execute([$department_id, "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? ORDER BY event_date DESC");
    $stmt->execute([$department_id]);
}
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
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --secondary: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
        }

        /* Form styling */
        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Buttons */
        .btn {
            transition: all 0.3s ease;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-warning {
            background-color: var(--warning);
            color: white;
        }

        .btn-warning:hover {
            background-color: #e69009;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Cards */
        .event-card {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
        }

        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .event-card.completed {
            position: relative;
        }

        .event-card.completed::after {
            content: "Completed";
            position: absolute;
            top: 0;
            right: 0;
            background-color: var(--success);
            color: white;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border-bottom-left-radius: 0.5rem;
        }

        /* Alert */
        .alert {
            transition: all 0.3s ease;
            border-radius: 0.375rem;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }

        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
            border-left: 4px solid var(--warning);
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }

        /* Search bar */
        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-input {
            padding-left: 2.5rem;
            border-radius: 2rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 400px;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .event-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Mobile Nav -->
    <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600 flex items-center">
            <i class="fas fa-calendar-alt mr-2"></i> Eventify
        </h1>
        <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
    </div>
    <div class="flex">
        <?php include 'sidebar.php'; ?>
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden md:hidden" onclick="toggleSidebar()">
        </div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h1 class="text-2xl font-bold text-indigo-700 mb-4 md:mb-0">
                            <i class="fas fa-calendar-plus mr-2"></i> Manage Events
                        </h1>
                    </div>

                    <?php if ($msg): ?>
                        <div id="alert" class="alert <?php
                        echo strpos($msg, '❌') !== false ? 'alert-danger' :
                            (strpos($msg, '✅') !== false ? 'alert-success' : 'alert-warning');
                        ?>">
                            <i class="fas <?php
                            echo strpos($msg, '❌') !== false ? 'fa-exclamation-circle mr-2' :
                                (strpos($msg, '✅') !== false ? 'fa-check-circle mr-2' : 'fa-exclamation-triangle mr-2');
                            ?>"></i>
                            <?= $msg ?>
                        </div>
                    <?php endif; ?>

                    <!-- Event Form -->
                    <div class="bg-indigo-50 rounded-lg p-6 mb-8">
                        <h2 class="text-lg font-semibold text-indigo-700 mb-4">
                            <i class="fas fa-<?= $editEvent ? 'edit' : 'plus' ?> mr-2"></i>
                            <?= $editEvent ? 'Edit Event' : 'Create New Event' ?>
                        </h2>

                        <form action="" method="POST" class="grid form-grid gap-4">
                            <input type="hidden" name="event_id" value="<?= $editEvent['event_id'] ?? '' ?>">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Title*</label>
                                <input type="text" name="title" required class="form-input"
                                    value="<?= htmlspecialchars($editEvent['event_title'] ?? '') ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date*</label>
                                <input type="date" name="date" required min="<?= date('Y-m-d') ?>" class="form-input"
                                    value="<?= $editEvent['event_date'] ?? '' ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Time*</label>
                                <input type="time" name="start_time" required class="form-input"
                                    value="<?= $editEvent['event_time'] ?? '' ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Time*</label>
                                <input type="time" name="end_time" required class="form-input"
                                    value="<?= $editEvent['event_end_time'] ?? '' ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Enrollment Deadline*</label>
                                <input type="datetime-local" name="enrollment_deadline" required
                                    min="<?= date('Y-m-d') ?>" class="form-input"
                                    value="<?= $editEvent['enrollment_deadline'] ?? '' ?>">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Max Participants
                                    (Optional)</label>
                                <input type="number" name="max_participants" min="1" class="form-input"
                                    value="<?= $editEvent['max_participants'] ?? '' ?>">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                                <textarea name="description" required rows="3"
                                    class="form-input"><?= htmlspecialchars($editEvent['event_description'] ?? '') ?></textarea>
                            </div>

                            <div class="md:col-span-2 flex flex-wrap gap-3">
                                <?php if ($editEvent): ?>
                                    <button type="submit" name="update" class="btn btn-warning">
                                        <i class="fas fa-save mr-2"></i> Update Event
                                    </button>
                                    <a href="manage_events.php" class="btn btn-secondary">
                                        <i class="fas fa-times mr-2"></i> Cancel
                                    </a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary">
                                        <i class="fas fa-plus mr-2"></i> Create Event
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Events List -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <h2 class="text-lg font-semibold text-indigo-700 mb-4 md:mb-0">
                            <i class="fas fa-list-ul mr-2"></i> Your Events
                        </h2>
                        <!-- Search Bar - Aligned to right -->
                        <form method="GET" class="w-full md:w-auto">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-indigo-600"></i>
                                <input type="text" name="search" placeholder="Search events..."
                                    class="form-input search-input py-2 pl-10 pr-4 w-full md:w-64"
                                    value="<?= htmlspecialchars($search) ?>">
                                <?php if ($search): ?>
                                    <a href="manage_events.php"
                                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <?php if ($events): ?>
                        <div class="grid event-grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <?php foreach ($events as $event):
                                $isCompleted = strtotime($event['event_date'] . ' ' . $event['event_end_time']) < time();
                                ?>
                                <div class="event-card">
                                    <div class="p-5">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="text-lg font-bold text-indigo-700 truncate">
                                                <?= htmlspecialchars($event['event_title']) ?>
                                            </h3>
                                            <span
                                                class="text-xs font-medium px-2 py-1 rounded-full 
                                                <?= $isCompleted ? 'bg-green-100 text-green-800' : 'bg-indigo-100 text-indigo-800' ?>">
                                                <?= $isCompleted ? 'Completed' : 'Upcoming' ?>
                                            </span>
                                        </div>

                                        <div class="space-y-2 text-sm text-gray-600 mb-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-calendar-day mr-2 text-indigo-500"></i>
                                                <?= date('M j, Y', strtotime($event['event_date'])) ?>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-clock mr-2 text-indigo-500"></i>
                                                <?= date('g:i A', strtotime($event['event_time'])) ?> -
                                                <?= date('g:i A', strtotime($event['event_end_time'])) ?>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-user-friends mr-2 text-indigo-500"></i>
                                                Max: <?= $event['max_participants'] ?? 'No Limit' ?>
                                            </div>
                                            <div class="flex items-center">
                                                <i class="fas fa-hourglass-end mr-2 text-indigo-500"></i>
                                                Enroll by: <?= date('M j, g:i A', strtotime($event['enrollment_deadline'])) ?>
                                            </div>
                                        </div>

                                        <p class="text-gray-700 text-sm mb-4 line-clamp-2">
                                            <?= htmlspecialchars($event['event_description']) ?>
                                        </p>

                                        <?php if (!$isCompleted): ?>
                                            <div class="flex justify-end space-x-2">
                                                <a href="manage_events.php?edit=<?= $event['event_id'] ?>"
                                                    class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <a href="manage_events.php?delete=<?= $event['event_id'] ?>"
                                                    class="text-sm text-red-500 hover:text-red-700 flex items-center"
                                                    onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <div class="mx-auto w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-calendar-times text-indigo-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-700 mb-2">No Events Found</h3>
                            <p class="text-gray-500">
                                <?= $search ? 'No events match your search.' : 'You haven\'t created any events yet.' ?>
                            </p>
                            <?php if ($search): ?>
                                <a href="manage_events.php" class="text-indigo-600 hover:underline mt-2 inline-block">
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

            // Prevent scrolling when sidebar is open
            if (!sidebar.classList.contains('-translate-x-full')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Hide alerts after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);

        // Focus search input when search icon is clicked (for mobile)
        document.querySelector('.fa-search').addEventListener('click', function () {
            document.querySelector('.search-input').focus();
        });
    </script>
</body>

</html>