<?php
require_once '../includes/db.php';
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';
require_once '../PHPMailer/src/Exception.php';
if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}
$department_id = $_SESSION['department_id'];
$msg = "";
// Handle Notification Send
if (isset($_GET['notify'])) {
    $event_id = $_GET['notify'];
    // Fetch Event
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = ?");
    $stmt->execute([$event_id, $department_id]);
    $event = $stmt->fetch();
    if ($event) {
        // Fetch Students in Department
        $stmt = $pdo->prepare("SELECT full_name, email FROM students WHERE department_id = ?");
        $stmt->execute([$department_id]);
        $students = $stmt->fetchAll();
        if ($students) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'a240076@famt.ac.in'; // Your Gmail
                $mail->Password = 'xjqe yees saws bkpu';    // Your App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('a240076@famt.ac.in', 'Department Admin');
                foreach ($students as $student) {
                    $mail->addAddress($student['email'], $student['full_name']);
                }
                $mail->isHTML(true);
                $mail->Subject = "New Event Notification - " . $event['event_title'];
                $mail->Body = "
                    <h2>New Event Released!</h2>
                    <p><strong>Title:</strong> {$event['event_title']}</p>
                    <p><strong>Description:</strong> {$event['event_description']}</p>
                    <p><strong>Date:</strong> {$event['event_date']}</p>
                    <p><strong>Time:</strong> {$event['event_time']} - {$event['event_end_time']}</p>
                    <p><strong>Enrollment Deadline:</strong> {$event['enrollment_deadline']}</p>
                    <p>Login to Eventify and enroll if interested!</p>
                ";
                if ($mail->send()) {
                    // Mark event as notified
                    $update = $pdo->prepare("UPDATE events SET notified = 1 WHERE event_id = ?");
                    $update->execute([$event_id]);

                    $msg = "Notification sent successfully!";
                }
            } catch (Exception $e) {
                $msg = "Mailer Error: " . $mail->ErrorInfo;
            }
        } else {
            $msg = "No students found in this department.";
        }
    } else {
        $msg = "Event not found, unauthorized, or already notified.";
    }
}
// Fetch Upcoming Events (exclude completed)
$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE department_id = ? 
      AND (event_date > CURDATE() 
           OR (event_date = CURDATE() AND event_end_time > CURTIME()))
    ORDER BY event_date ASC
");
$stmt->execute([$department_id]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notify Students - Department Panel</title>
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
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
        }
        
        /* Card styling */
        .event-card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            overflow: hidden;
            background: white;
            position: relative;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }
        
        .event-card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 1.25rem;
        }
        
        .event-card-body {
            padding: 1.5rem;
        }
        
        /* Status badges */
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .status-notified {
            background-color: #ecfdf5;
            color: #059669;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            width: 100%;
            text-align: center;
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
        
        .btn-disabled {
            background-color: #e5e7eb;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        /* Alert messages */
        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            animation: slideIn 0.3s ease-out;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--success);
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
            border-left: 4px solid var(--primary);
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
        
        /* Event details */
        .event-detail {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: #4b5563;
            font-size: 0.9375rem;
        }
        
        .event-detail i {
            margin-right: 0.75rem;
            color: var(--primary);
            width: 1.25rem;
            text-align: center;
        }
        
        /* Mobile nav */
        .mobile-nav {
            background: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }
        
        /* Responsive grid */
        @media (max-width: 768px) {
            .events-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Mobile Nav -->
    <div class="mobile-nav md:hidden px-4 py-3 flex justify-between items-center sticky top-0 z-30">
        <h1 class="text-xl font-bold text-indigo-600 flex items-center">
            <i class="fas fa-bell mr-2"></i> Notifications
        </h1>
            <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
    </div>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>
        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-4 text-white">
                        <h1 class="text-2xl font-bold flex items-center">
                            <i class="fas fa-bell mr-3"></i> Notify Students
                        </h1>
                        <p class="text-indigo-100 mt-1">Send notifications about upcoming events to your department students</p>
                    </div>
                    
                    <div class="p-6">
                        <?php if ($msg): ?>
                            <div id="alert-msg" class="alert <?php 
                                echo strpos($msg, 'Error') !== false ? 'alert-error' : 
                                     (strpos($msg, 'successfully') !== false ? 'alert-success' : 'alert-info');
                            ?>">
                                <i class="fas <?php 
                                    echo strpos($msg, 'Error') !== false ? 'fa-exclamation-circle mr-2' : 
                                         (strpos($msg, 'successfully') !== false ? 'fa-check-circle mr-2' : 'fa-info-circle mr-2');
                                ?>"></i>
                                <span><?= $msg ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($events): ?>
                            <div class="grid gap-6 events-grid sm:grid-cols-2 lg:grid-cols-3">
                                <?php foreach ($events as $event): ?>
                                    <div class="event-card">
                                        <div class="event-card-header">
                                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($event['event_title']) ?></h3>
                                        </div>
                                        <div class="event-card-body">
                                            <?php if ($event['notified'] == 1): ?>
                                                <span class="status-badge status-notified">
                                                    <i class="fas fa-check-circle mr-1"></i> Notified
                                                </span>
                                            <?php endif; ?>
                                            
                                            <div class="event-detail">
                                                <i class="fas fa-calendar-day"></i>
                                                <span><?= $event['event_date'] ?></span>
                                            </div>
                                            
                                            <div class="event-detail">
                                                <i class="fas fa-clock"></i>
                                                <span><?= $event['event_time'] ?> - <?= $event['event_end_time'] ?></span>
                                            </div>
                                            
                                            <div class="event-detail">
                                                <i class="fas fa-hourglass-end"></i>
                                                <span>Enroll by <?= $event['enrollment_deadline'] ?></span>
                                            </div>
                                            
                                            <p class="text-gray-600 text-sm mt-4 mb-4"><?= htmlspecialchars($event['event_description']) ?></p>
                                            
                                            <?php if ($event['notified'] == 1): ?>
                                                <button class="btn btn-disabled">
                                                    <i class="fas fa-check mr-2"></i> Already Notified
                                                </button>
                                            <?php else: ?>
                                                <a href="event_notify.php?notify=<?= $event['event_id'] ?>" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane mr-2"></i> Notify Students
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12">
                                <div class="mx-auto w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-calendar-times text-indigo-600 text-3xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Upcoming Events</h3>
                                <p class="text-gray-500">There are currently no upcoming events to notify students about.</p>
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
        
        // Auto-hide alert message after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById('alert-msg');
            if (alert) {
                alert.classList.add('fade-out');
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>