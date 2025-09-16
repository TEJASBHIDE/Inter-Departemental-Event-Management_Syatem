<?php
require_once '../includes/db.php';
require_once '../fpdf186/fpdf.php';
session_start();

if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}

$department_id = $_SESSION['department_id'];
$msg = '';

// Fetch Department Name
$stmt = $pdo->prepare("SELECT department_name FROM departments WHERE department_id = ?");
$stmt->execute([$department_id]);
$deptData = $stmt->fetch();
$deptName = $deptData ? $deptData['department_name'] : '';

// Handle Generate Report
if (isset($_POST['generate_report'])) {
    $event_id = $_POST['event_id'];
    $custom_report = trim($_POST['custom_report']);

    // Fetch Event Details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND department_id = ?");
    $stmt->execute([$event_id, $department_id]);
    $event = $stmt->fetch();

    if ($event && $custom_report) {
        // Generate PDF
        $pdf = new FPDF();
        $pdf->AddPage();

        // Header Section
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, "Department of " . $deptName, 0, 1, 'C');
        $pdf->Cell(0, 10, "FINOLEX ACADEMY OF MANAGEMENT AND TECHNOLOGY, RATNAGIRI", 0, 1, 'C');

        // Horizontal Line
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(8);

        // Event Details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, "Event Title: " . $event['event_title'], 0, 1);
        $pdf->Cell(0, 8, "Event Date: " . $event['event_date'], 0, 1);
        $pdf->Cell(0, 8, "Start Time: " . $event['event_time'], 0, 1);
        $pdf->Cell(0, 8, "End Time: " . $event['event_end_time'], 0, 1);
        $pdf->Cell(0, 8, "Max Participants: " . ($event['max_participants'] ?? 'No Limit'), 0, 1);
        $pdf->Ln(6);

        // Report Body
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, "Report Summary:", 0, 1);
        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 8, $custom_report);

        $pdf->Ln(10);
        $pdf->Cell(0, 8, "Prepared By: " . $deptName, 0, 1);
        $pdf->Cell(0, 8, "Date: " . date('d-m-Y'), 0, 1);

        // Output PDF
        $pdf->Output('D', "Event_Report_" . $event['event_title'] . ".pdf");
        exit();
    } else {
        $msg = "Please select event and enter report text.";
    }
}

// Fetch Completed Events (Past Events)
$stmt = $pdo->prepare("SELECT * FROM events WHERE department_id = ? AND CONCAT(event_date, ' ', event_end_time) < NOW() ORDER BY event_date DESC");
$stmt->execute([$department_id]);
$completedEvents = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generate Reports - Department Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #4338ca;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        /* Form elements */
        select,
        textarea {
            transition: all 0.3s ease;
            border: 1px solid #d1d5db;
        }

        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Card styling */
        .report-card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .report-card:hover {
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        /* Mobile menu */
        #sidebar {
            transition: transform 0.3s ease;
        }

        #overlay {
            transition: opacity 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
            }

            select,
            textarea {
                width: 100%;
            }

            .btn-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn-group button {
                width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    <!-- Mobile Header -->
  <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i> Reports
        </h1>
        <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>

    </div>

    <div class="flex">
        <?php include 'sidebar.php'; ?>

        <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden md:hidden" onclick="toggleSidebar()">
        </div>

        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0 animate-fade">
            <div class="report-card max-w-4xl mx-auto overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 p-6 text-white">
                    <h1 class="text-2xl font-bold flex items-center">
                        <i class="fas fa-file-alt mr-3"></i> Generate Event Report
                    </h1>
                    <p class="text-indigo-100 mt-1">Create detailed PDF reports for your department events</p>
                </div>

                <div class="form-container p-6">
                    <?php if ($msg): ?>
                        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 text-yellow-500">
                                    <i class="fas fa-exclamation-circle"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700"><?= $msg ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (count($completedEvents) > 0): ?>
                        <form method="POST" class="space-y-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-calendar-day mr-2 text-indigo-600"></i> Select Completed Event
                                </label>
                                <div class="relative">
                                    <select name="event_id" required
                                        class="block w-full px-4 py-3 pr-8 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 bg-white appearance-none">
                                        <option value="">-- Select Event --</option>
                                        <?php foreach ($completedEvents as $event): ?>
                                            <option value="<?= $event['event_id'] ?>">
                                                <?= htmlspecialchars($event['event_title']) ?> (<?= $event['event_date'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    <i class="fas fa-file-signature mr-2 text-indigo-600"></i> Report Summary
                                </label>
                                <textarea name="custom_report" required rows="8"
                                    class="block w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Write detailed report here... Include key points, outcomes, participant feedback, and any follow-up actions..."></textarea>
                            </div>

                            <div class="flex flex-wrap gap-4 btn-group">
                                <button type="submit" name="generate_report"
                                    class="btn-primary text-white px-6 py-3 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-pdf mr-2"></i> Generate PDF Report
                                </button>

                                <button type="reset"
                                    class="btn-secondary text-gray-700 px-6 py-3 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-undo mr-2"></i> Clear Form
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-10">
                            <div class="mx-auto w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-calendar-times text-indigo-500 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Completed Events</h3>
                            <p class="text-gray-500 max-w-md mx-auto">
                                There are no completed events available for report generation.
                                Reports can only be generated for past events.
                            </p>
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

        // Close sidebar when clicking on a link (if your sidebar has navigation links)
        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');
                if (window.innerWidth < 768) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        });
    </script>
</body>

</html>