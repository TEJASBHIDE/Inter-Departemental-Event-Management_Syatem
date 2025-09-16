<?php
require_once '../includes/db.php';

session_start();
if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}

$department_id = $_SESSION['department_id'];
$msg = '';

if (isset($_POST['action']) && isset($_POST['suggestion_id'])) {
    $suggestion_id = $_POST['suggestion_id'];
    $new_status = $_POST['action'];
    $remarks = $_POST['remarks'] ?? null;

    // Update status
    $stmt = $pdo->prepare("UPDATE event_suggestions SET status = ?, admin_remarks = ?, reviewed_at = NOW() WHERE suggestion_id = ? AND department_id = ?");
    $stmt->execute([$new_status, $remarks, $suggestion_id, $department_id]);
    $msg = "Suggestion status updated successfully!";
}

// Handle Delete Action
if (isset($_POST['action']) && $_POST['action'] === 'Delete' && isset($_POST['suggestion_id'])) {
    $suggestion_id = $_POST['suggestion_id'];

    try {
        $pdo->beginTransaction();

        // First check if the suggestion belongs to this department
        $checkStmt = $pdo->prepare("SELECT department_id FROM event_suggestions WHERE suggestion_id = ?");
        $checkStmt->execute([$suggestion_id]);
        $suggestion = $checkStmt->fetch();

        if ($suggestion && $suggestion['department_id'] == $department_id) {
            // Delete the suggestion
            $deleteStmt = $pdo->prepare("DELETE FROM event_suggestions WHERE suggestion_id = ?");
            $deleteStmt->execute([$suggestion_id]);

            $pdo->commit();
            $msg = "Suggestion deleted successfully!";
        } else {
            $pdo->rollBack();
            $msg = "Error: You can only delete suggestions from your department.";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $msg = "Error deleting suggestion: " . $e->getMessage();
    }

    // Refresh the page to show updated list
    header("Location: view_suggestions.php");
    exit();
}

// Fetch all suggestions
$stmt = $pdo->prepare("
    SELECT es.*, s.full_name, s.email, d.department_name
    FROM event_suggestions es 
    JOIN students s ON es.student_id = s.student_id
    JOIN departments d ON es.department_id = d.department_id
    WHERE es.department_id = ? 
    ORDER BY es.submitted_at DESC
");
$stmt->execute([$department_id]);
$suggestions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Suggestions - Department Panel</title>
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
            font-family: 'Inter', sans-serif;
        }

        .suggestion-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 0.75rem;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
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

        .btn-primary {
            background-color: var(--primary);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: var(--success);
            transition: all 0.3s;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: var(--danger);
            transition: all 0.3s;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
        }

        .empty-state {
            background-color: #f9fafb;
            border: 1px dashed #e5e7eb;
            border-radius: 0.75rem;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Mobile Nav -->
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
        <main class="flex-1 md:ml-64 p-6">
            <div class="max-w-7xl mx-auto">
                <div class="suggestion-card p-6 mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-lightbulb text-indigo-600 mr-3"></i> Student Event Suggestions
                    </h1>
                    <p class="text-gray-600 mb-6">Review and manage event suggestions from your department students</p>

                    <?php if ($msg): ?>
                        <div id="alert"
                            class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($suggestions) > 0): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($suggestions as $sug): ?>
                            <div class="suggestion-card p-6">
                                <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div
                                                class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mr-3">
                                                <i class="fas fa-user-graduate"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-gray-900"><?= htmlspecialchars($sug['full_name']) ?>
                                                </h3>
                                                <p class="text-sm text-gray-500"><?= htmlspecialchars($sug['email']) ?></p>
                                            </div>
                                        </div>

                                        <h4 class="text-lg font-semibold text-gray-800 mt-4">
                                            <?= htmlspecialchars($sug['title']) ?>
                                        </h4>
                                        <p class="text-gray-600 mt-2"><?= htmlspecialchars($sug['description']) ?></p>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <?php if ($sug['event_type']): ?>
                                                <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-full">
                                                    <?= htmlspecialchars($sug['event_type']) ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($sug['preferred_date']): ?>
                                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    <?= date( htmlspecialchars($sug['preferred_date'])) ?>
                                                </span>
                                            <?php endif; ?>

                                            <span
                                                class="status-badge status-<?= strtolower(str_replace(' ', '-', $sug['status'])) ?>">
                                                <?= $sug['status'] ?>
                                            </span>
                                        </div>

                                        <?php if ($sug['admin_remarks']): ?>
                                            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                                <p class="text-sm font-medium text-gray-700">Admin Remarks:</p>
                                                <p class="text-gray-600"><?= htmlspecialchars($sug['admin_remarks']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="md:w-64">
                                        <div class="action-buttons">
                                            <?php if ($sug['status'] === 'Under Review'): ?>
                                                <form method="POST" class="w-full">
                                                    <input type="hidden" name="suggestion_id" value="<?= $sug['suggestion_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks
                                                            (Optional)</label>
                                                        <textarea name="remarks" class="form-input w-full px-3 py-2 text-sm"
                                                            rows="2"></textarea>
                                                    </div>
                                                    <div class="flex gap-2">
                                                        <button name="action" value="Accepted"
                                                            class="btn-success text-white px-4 py-2 rounded-lg flex-1">
                                                            <i class="fas fa-check mr-2"></i> Accept
                                                        </button>
                                                        <button name="action" value="Rejected"
                                                            class="btn-danger text-white px-4 py-2 rounded-lg flex-1">
                                                            <i class="fas fa-times mr-2"></i> Reject
                                                        </button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>

                                            <a href="mailto:<?= htmlspecialchars($sug['email']) ?>?subject=Regarding Your Event Suggestion: <?= rawurlencode($sug['title']) ?>&body=Dear <?= rawurlencode($sug['full_name']) ?>%2C%0A%0ARegarding your event suggestion <?= rawurlencode($sug['title']) ?>%3A%0A%0A[Write your response here]%0A%0ARegards%2C%0A<?= rawurlencode($sug['department_name']) ?> Department"
                                                class="btn-primary text-white px-4 py-2 rounded-lg flex items-center justify-center w-full mt-2">
                                                <i class="fas fa-envelope mr-2"></i> Notify Student
                                            </a>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="suggestion_id" value="<?= $sug['suggestion_id'] ?>">
                                                <input type="hidden" name="action" value="Delete">
                                                <button type="submit"
                                                    onclick="return confirm('Are you sure you want to delete this suggestion?')"
                                                    class="btn-danger text-white px-4 py-2 rounded-lg flex-1">
                                                    <i class="fas fa-trash-can mr-3"></i> Delete
                                                </button>
                                            </form>
                                        </div>

                                        <div class="mt-4 text-xs text-gray-500">
                                            <p>Submitted: <?= date('M d, Y h:i A', strtotime($sug['submitted_at'])) ?></p>
                                            <?php if ($sug['reviewed_at']): ?>
                                                <p>Reviewed: <?= date('M d, Y h:i A', strtotime($sug['reviewed_at'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="suggestion-card p-8 text-center empty-state">
                        <i class="fas fa-lightbulb text-4xl text-gray-300 mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900">No suggestions found</h3>
                        <p class="mt-1 text-sm text-gray-500">There are currently no event suggestions from students in your
                            department.</p>
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
            document.body.classList.toggle('overflow-hidden');
        }
        // Hide alerts after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    </script>
</body>

</html>