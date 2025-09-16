<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}
$department_id = $_SESSION['department_id'];
// Mark message as read
if (isset($_GET['read'])) {
    $contact_id = $_GET['read'];
    $stmt = $pdo->prepare("UPDATE department_contacts SET is_read = 1 WHERE contact_id = ? AND department_id = ?");
    $stmt->execute([$contact_id, $department_id]);
    header("Location: contact_reply.php");
    exit();
}
// Fetch messages for department
$stmt = $pdo->prepare("SELECT dc.*, d.department_name FROM department_contacts dc
    JOIN departments d ON dc.department_id = d.department_id
    WHERE dc.department_id = ?
    ORDER BY dc.submitted_at DESC");
$stmt->execute([$department_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Messages - Department Panel</title>
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
        
        /* Message Cards */
        .message-card {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            background: white;
            border-left: 4px solid transparent;
        }
        
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .message-card.unread {
            border-left-color: var(--primary);
            background-color: #f5f7ff;
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
            font-size: 0.875rem;
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
        
        .btn-success {
            background-color: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0d9c6f;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-read {
            background-color: #ecfdf5;
            color: #065f46;
        }
        
        .badge-unread {
            background-color: #eff6ff;
            color: #1e40af;
        }
        
        /* Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .message-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .message-actions .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Nav -->
  <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600 flex items-center">
            <i class="fas fa-envelope mr-2"></i> Messages
        </h1>
            <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
    </div>
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="page-header px-6 py-4">
                        <h1 class="text-2xl font-bold flex items-center">
                            <i class="fas fa-comments mr-3"></i> Student Contact Messages
                        </h1>
                        <p class="text-indigo-100 mt-1">Manage all student inquiries and communications</p>
                    </div>
                    
                    <!-- Messages List -->
                    <div class="p-6">
                        <?php if (count($messages) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message-card <?= $msg['is_read'] == 0 ? 'unread' : '' ?>">
                                        <div class="p-5">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-3">
                                                <div class="mb-2 sm:mb-0">
                                                    <h3 class="text-lg font-bold text-gray-800">
                                                        <?= htmlspecialchars($msg['student_name']) ?>
                                                    </h3>
                                                    <a href="mailto:<?= $msg['student_email'] ?>" class="text-indigo-600 hover:underline text-sm">
                                                        <i class="fas fa-envelope mr-1"></i> <?= htmlspecialchars($msg['student_email']) ?>
                                                    </a>
                                                </div>
                                                
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs text-gray-500">
                                                        <i class="far fa-clock mr-1"></i> <?= $msg['submitted_at'] ?>
                                                    </span>
                                                    <span class="badge <?= $msg['is_read'] ? 'badge-read' : 'badge-unread' ?>">
                                                        <?= $msg['is_read'] ? 'Read' : 'Unread' ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                                                <p class="text-gray-700 whitespace-pre-line"><?= htmlspecialchars($msg['message']) ?></p>
                                            </div>
                                            
                                            <div class="flex flex-wrap gap-3 message-actions">
                                                <?php if ($msg['is_read'] == 0): ?>
                                                    <a href="contact_reply.php?read=<?= $msg['contact_id'] ?>" class="btn btn-warning">
                                                        <i class="fas fa-check-circle mr-2"></i> Mark as Read
                                                    </a>
                                                <?php else: ?>
                                                    <span class="btn btn-success opacity-70 cursor-default">
                                                        <!-- <i class="fas fa-check-double mr-2"></i>  -->
                                                        Message Read
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <a href="mailto:<?= $msg['student_email'] ?>?subject=Response from <?= rawurlencode($msg['department_name']) ?>&body=<?= rawurlencode("Hello " . $msg['student_name'] . ",\n\nWe hope you're doing well. This is a response from your department regarding your recent query. Please find the details below:\n\n[Your Message Here]\n\nBest Regards,\n" . $msg['department_name']) ?>"
                                                    class="btn btn-primary">
                                                    <i class="fas fa-reply mr-2"></i> Reply
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-10">
                                <div class="mx-auto w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-inbox text-indigo-500 text-3xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Messages Found</h3>
                                <p class="text-gray-500">You haven't received any contact messages from students yet.</p>
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
            
            // Prevent scrolling when sidebar is open
            if (!sidebar.classList.contains('-translate-x-full')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
    </script>
</body>
</html>