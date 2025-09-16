<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
// $department_id = $_SESSION['department_id'];
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

if (!isset($_SESSION['department_id'])) {
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
        
        // Clear form data from session
        unset($_POST['title'], $_POST['event_type'], $_POST['description'], $_POST['preferred_date']);
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
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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
            z-index: 40;
        }
        
        .suggestion-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 0.5rem;
        }
        
        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            background-color: white;
        }
        
        .btn-primary {
            background-color: var(--primary);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fffbeb;
            color: var(--warning);
        }
        
        .status-accepted {
            background-color: #ecfdf5;
            color: var(--success);
        }
        
        .status-rejected {
            background-color: #fef2f2;
            color: var(--danger);
        }
        
        .alert {
            animation: fadeIn 0.3s ease-out;
        }
        
        .alert.fade-out {
            animation: fadeOut 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .empty-state {
            background-color: #f9fafb;
            border: 1px dashed #e5e7eb;
            border-radius: 0.75rem;
        }
        
        .page-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
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
        <div id="overlay" class="overlay fixed inset-0 hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6">
            <div class="max-w-4xl mx-auto">
                <div class="suggestion-card p-6 mb-6">
                    <div class="page-header">
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-comments text-indigo-600 mr-3"></i> Suggest Events
                        </h1>
                        <p class="text-gray-600 mt-1">Feel free to suggest new event ideas!</p>
                    </div>
                    
                    <?php if ($msg): ?>
                        <div id="alert" class="alert mb-6 p-4 rounded-lg flex items-center 
                            <?= strpos($msg, 'successfully') !== false ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' ?>">
                            <i class="fas <?= strpos($msg, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Title*</label>
                            <input type="text" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                                   class="form-input w-full px-4 py-3" placeholder="Enter event title">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Event Type</label>
                            <input type="text" name="event_type" value="<?= htmlspecialchars($_POST['event_type'] ?? '') ?>"
                                   class="form-input w-full px-4 py-3" placeholder="Workshop, Seminar, Cultural...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description*</label>
                            <textarea name="description" required rows="4" 
                                      class="form-input w-full px-4 py-3" 
                                      placeholder="Provide detailed description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date</label>
                            <input type="date" name="preferred_date" value="<?= htmlspecialchars($_POST['preferred_date'] ?? '') ?>"
                                   class="form-input w-full px-4 py-3">
                        </div>

                        <div class="pt-2">
                            <button type="submit" name="suggest" 
                                    class="btn-primary text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Suggestion
                            </button>
                        </div>
                    </form>
                </div>

                <div class="suggestion-card p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-list-ul text-indigo-600 mr-3"></i> Your Suggestions
                    </h2>
                    
                    <div class="mb-4">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" id="searchInput" placeholder="Search by title..." 
                                   class="search-input w-full pl-10 pr-4 py-2">
                        </div>
                    </div>

                    <?php if (count($suggestions) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="suggestionTable" class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($suggestions as $sug): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($sug['title']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($sug['event_type'] ?? '-') ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= $sug['preferred_date'] ? date('M d, Y', strtotime($sug['preferred_date'])) : '-' ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <?php if ($sug['status'] == 'Accepted'): ?>
                                                    <span class="status-badge status-accepted">Accepted</span>
                                                <?php elseif ($sug['status'] == 'Rejected'): ?>
                                                    <span class="status-badge status-rejected">Rejected</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Under Review</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?= htmlspecialchars($sug['admin_remarks'] ?? '-') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state p-8 text-center">
                            <i class="fas fa-lightbulb text-4xl text-gray-300 mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900">No suggestions yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Your event suggestions will appear here once submitted.</p>
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

        // Live search for suggestions
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#suggestionTable tr');
            
            rows.forEach(row => {
                const title = row.querySelector('td:first-child').textContent.toLowerCase();
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

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>