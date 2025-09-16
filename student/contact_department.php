<?php
require_once '../includes/db.php';

$msg = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['student_name']);
    $email = trim($_POST['student_email']);
    $dept_id = $_POST['department_id'];
    $message = trim($_POST['message']);

    if ($name && $email && $dept_id && $message) {
        $stmt = $pdo->prepare("INSERT INTO department_contacts (student_name, student_email, department_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $dept_id, $message]);
        $msg = "Your message has been sent successfully!";
    } else {
        $msg = "All fields are required.";
    }
}

// Fetch departments for dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Department - Eventify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        .fade-out { 
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-container {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            background: linear-gradient(to bottom right, white 0%, #f9fafb 100%);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        .input-field:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background-color: white;
        }
        .btn-primary {
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            transition: all 0.3s ease;
            letter-spacing: 0.5px;
        }
        .btn-primary:hover {
            background: linear-gradient(to right, #4f46e5, #7c3aed);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-secondary {
            background: linear-gradient(to right, #64748b, #475569);
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: linear-gradient(to right, #475569, #334155);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 md:p-8">

<div class="form-container p-8 max-w-lg w-full relative">
    <!-- Home button -->
    <a href="../home.php" class="absolute top-4 right-4 text-gray-500 hover:text-indigo-600 transition-colors">
        <i class="fas fa-home text-xl"></i>
    </a>

    <div class="text-center mb-8">
        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope-open-text text-indigo-600 text-2xl"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Contact Your Department</h2>
        <p class="text-gray-500">We'll get back to you soon</p>
    </div>

    <?php if ($msg): ?>
        <div id="msgBox" class="mb-6 px-4 py-3 rounded-lg text-center font-medium 
            <?php echo strpos($msg, 'successfully') !== false ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <i class="fas <?php echo strpos($msg, 'successfully') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
            <?= $msg ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Name <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                <input type="text" name="student_name" required 
                       class="input-field w-full pl-10 pr-3 py-2.5 rounded-lg focus:outline-none"
                       placeholder="Enter your full name">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Email <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input type="email" name="student_email" required 
                       class="input-field w-full pl-10 pr-3 py-2.5 rounded-lg focus:outline-none"
                       placeholder="Enter your email address">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Department <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-building text-gray-400"></i>
                </div>
                <select name="department_id" required 
                        class="input-field w-full pl-10 pr-3 py-2.5 rounded-lg focus:outline-none appearance-none">
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <i class="fas fa-chevron-down text-gray-400"></i>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Message <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="absolute top-3 left-3">
                    <i class="fas fa-comment-dots text-gray-400"></i>
                </div>
                <textarea name="message" required rows="4" 
                          class="input-field w-full pl-10 pr-3 py-2.5 rounded-lg focus:outline-none"
                          placeholder="Type your message here..."></textarea>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit" name="submit" class="btn-primary w-full text-white py-3 px-4 rounded-lg font-medium">
                <i class="fas fa-paper-plane mr-2"></i> Send Message
            </button>
        </div>
    </form>

    <div class="mt-6 text-center">
        <a href="../home.php" class="btn-secondary inline-flex items-center text-white py-2 px-6 rounded-lg text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
        </a>
    </div>
</div>

<script>
// Auto hide message after 5 seconds
setTimeout(() => {
    const msgBox = document.getElementById('msgBox');
    if (msgBox) {
        msgBox.classList.add('fade-out');
        msgBox.style.opacity = 0;
        setTimeout(() => msgBox.remove(), 500);
    }
}, 5000);
</script>

</body>
</html>