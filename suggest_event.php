<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$department_id = $_SESSION['department_id'];
$msg = "";

if (!isset($_SESSION['department_id'])) {
    // Fallback: Fetch department_id from DB if needed
    $stmt = $pdo->prepare("SELECT department_id FROM students WHERE student_id = ?");
    $stmt->execute([$_SESSION['student_id']]);
    $data = $stmt->fetch();

    if ($data) {
        $_SESSION['department_id'] = $data['department_id'];
        $department_id = $data['department_id'];
    } else {
        die("Unable to determine department. Please re-login.");
    }
} else {
    $department_id = $_SESSION['department_id'];
}

// Handle Suggestion Submission
if (isset($_POST['suggest'])) {
    $title = trim($_POST['title']);
    $event_type = trim($_POST['event_type']);
    $description = trim($_POST['description']);
    $preferred_date = $_POST['preferred_date'];

    // Restriction: Max 3 pending suggestions
    $pendingCheck = $pdo->prepare("SELECT COUNT(*) FROM event_suggestions WHERE student_id = ? AND status = 'Under Review'");
    $pendingCheck->execute([$student_id]);
    $pendingCount = $pendingCheck->fetchColumn();

    if ($pendingCount >= 3) {
        $msg = "You already have 3 suggestions under review. Please wait for a response.";
    } elseif ($title && $description) {
        $stmt = $pdo->prepare("INSERT INTO event_suggestions (student_id, department_id, title, event_type, description, preferred_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$student_id, $department_id, $title, $event_type, $description, $preferred_date]);
        $msg = "Suggestion submitted successfully!";
    } else {
        $msg = "Please fill all required fields.";
    }
}

// Fetch Student's Suggestions
$stmt = $pdo->prepare("SELECT * FROM event_suggestions WHERE student_id = ? ORDER BY submitted_at DESC");
$stmt->execute([$student_id]);
$suggestions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Suggest Event - Eventify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
        }

        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
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
            z-index: 40;
        }
    </style>
</head>

<body class="bg-gray-100">

    <!-- Mobile Nav -->
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
                <a href="suggest_event.php" class="active">
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

        <!-- Overlay -->
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="bg-white rounded shadow p-6">
                <div class="page-header">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-comments text-indigo-600 mr-3"></i> Suggest Events
                    </h1>
                    <p class="text-gray-600 mt-1">Feel free to suggest new event ideas!</p>
                </div>
                <div class="flex items-center mb-5 pb-5 border-b border-indigo-600">
                </div>
                <?php if ($msg): ?>
                    <div id="alert" class="mb-4 bg-yellow-100 text-yellow-800 px-4 py-2 rounded"><?= $msg ?></div>
                <?php endif; ?>

                <form method="POST" class="space-y-4 mb-8">
                    <div>
                        <label class="block mb-1 text-sm">Event Title*</label>
                        <input type="text" name="title" required class="w-full border rounded px-3 py-2"
                            placeholder="Enter event title">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm">Event Type</label>
                        <input type="text" name="event_type" class="w-full border rounded px-3 py-2"
                            placeholder="Workshop, Seminar, Cultural...">
                    </div>

                    <div>
                        <label class="block mb-1 text-sm">Description*</label>
                        <textarea name="description" required rows="4" class="w-full border rounded px-3 py-2"
                            placeholder="Provide detailed description..."></textarea>
                    </div>

                    <div>
                        <label class="block mb-1 text-sm">Preferred Date</label>
                        <input type="date" name="preferred_date" class="w-full border rounded px-3 py-2">
                    </div>

                    <button type="submit" name="suggest"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Submit Suggestion
                    </button>
                </form>

                <h2 class="text-xl font-semibold text-indigo-700 mb-3">Your Suggestions</h2>

                <input type="text" id="searchInput" placeholder="Search by title..."
                    class="w-full border mb-4 px-3 py-2 rounded">

                <?php if (count($suggestions) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left px-4 py-2">Title</th>
                                    <th class="text-left px-4 py-2">Type</th>
                                    <th class="text-left px-4 py-2">Preferred Date</th>
                                    <th class="text-left px-4 py-2">Status</th>
                                    <th class="text-left px-4 py-2">Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="suggestionTable">
                                <?php foreach ($suggestions as $sug): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?= htmlspecialchars($sug['title']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($sug['event_type'] ?? '-') ?></td>
                                        <td class="px-4 py-2"><?= $sug['preferred_date'] ?? '-' ?></td>
                                        <td class="px-4 py-2 font-semibold">
                                            <?php if ($sug['status'] == 'Accepted'): ?>
                                                <span class="text-green-600">Accepted</span>
                                            <?php elseif ($sug['status'] == 'Rejected'): ?>
                                                <span class="text-red-600">Rejected</span>
                                            <?php else: ?>
                                                <span class="text-yellow-600">Under Review</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($sug['admin_remarks'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No suggestions submitted yet.</p>
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

        // Live search for suggestions
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#suggestionTable tr');

            rows.forEach(row => {
                const title = row.querySelector('td').innerText.toLowerCase();
                row.style.display = title.includes(filter) ? '' : 'none';
            });
        });

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