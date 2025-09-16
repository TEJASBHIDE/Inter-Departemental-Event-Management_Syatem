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

    <!-- Mobile Nav Toggle -->
    <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600">Eventify Admin</h1>
        <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
    </div>

    <div class="flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="bg-white rounded shadow p-6">
                <h1 class="text-2xl font-bold text-indigo-700 mb-4">Notify Students - Upcoming Events</h1>

                <?php if ($msg): ?>
                    <div class="mb-4 bg-green-100 text-green-800 px-4 py-2 rounded" id="alert-msg"><?= $msg ?></div>
                <?php endif; ?>

                <?php if ($events): ?>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($events as $event): ?>
                            <div class="border rounded shadow p-4 bg-gray-50 card">
                                <h3 class="text-lg font-bold text-indigo-700 mb-2">
                                    <?= htmlspecialchars($event['event_title']) ?></h3>
                                <p class="text-gray-700 text-sm mb-1">📅 <?= $event['event_date'] ?></p>
                                <p class="text-gray-700 text-sm mb-1">⏰ <?= $event['event_time'] ?> -
                                    <?= $event['event_end_time'] ?></p>
                                <p class="text-gray-600 text-sm mb-2">📝 <?= htmlspecialchars($event['event_description']) ?>
                                </p>
                                <p class="text-gray-600 text-sm mb-4">Enrollment Deadline: <?= $event['enrollment_deadline'] ?>
                                </p>

                                <?php if ($event['notified'] == 1): ?>
                                    <button class="bg-gray-400 text-white w-full py-2 rounded cursor-not-allowed" disabled>✅
                                        Notified</button>
                                <?php else: ?>
                                    <a href="event_notify.php?notify=<?= $event['event_id'] ?>"
                                        class="block bg-indigo-600 text-white text-center py-2 rounded hover:bg-indigo-700">Notify
                                        Students</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No upcoming events to notify.</p>
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

        // Auto-hide message
        setTimeout(() => {
            const alert = document.getElementById('alert-msg');
            if (alert) alert.style.display = 'none';
        }, 5000);
    </script>
</body>

</html>