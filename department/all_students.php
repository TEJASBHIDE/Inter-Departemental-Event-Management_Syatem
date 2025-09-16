<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}

$department_id = $_SESSION['department_id'];
$department_name = $_SESSION['department_name'];

// Fetch all students in the department
$stmt = $pdo->prepare("SELECT * FROM students WHERE department_id = ? ORDER BY created_at DESC");
$stmt->execute([$department_id]);
$students = $stmt->fetchAll();

// Handle password reveal requests
$password_errors = [];
$revealed_password = null;
$revealed_student_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reveal_password'])) {
    $student_id = $_POST['student_id'];
    $admin_password = $_POST['admin_password'];
    
    // Verify admin password
    $admin_stmt = $pdo->prepare("SELECT admin_password FROM departments WHERE department_id = ?");
    $admin_stmt->execute([$department_id]);
    $admin = $admin_stmt->fetch();
    
    if (password_verify($admin_password, $admin['admin_password'])) {
        // Get student password
        $student_stmt = $pdo->prepare("SELECT password FROM students WHERE student_id = ?");
        $student_stmt->execute([$student_id]);
        $student = $student_stmt->fetch();
        
        $revealed_password = $student['password'];
        $revealed_student_id = $student_id;
    } else {
        $password_errors[] = "Incorrect admin password";
    }
}

// Handle student removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_student'])) {
    $student_id = $_POST['student_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Remove student from department (set department_id to NULL or delete based on your requirements)
        $update_stmt = $pdo->prepare("UPDATE students SET department_id = NULL WHERE student_id = ?");
        $update_stmt->execute([$student_id]);
        
        $pdo->commit();
        
        // Refresh page to show updated list
        header("Location: all_students.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $password_errors[] = "Error removing student: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Students - <?= htmlspecialchars($department_name) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --danger: #ef4444;
            --danger-hover: #dc2626;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        
        .student-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background: white;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 68%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            animation: fadeInUp 0.3s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-primary {
            background-color: var(--primary);
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: var(--danger);
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-hover);
            transform: translateY(-1px);
        }
        
        .empty-state {
            background-color: #f9fafb;
            border: 1px dashed #e5e7eb;
        }
        
        .floating-action-btn {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s;
        }
        
        .floating-action-btn:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50">
<!-- Mobile Nav Toggle -->
<div class="md:hidden bg-white shadow-lg px-4 py-3 flex justify-between items-center sticky top-0 z-30">
    <h1 class="text-xl font-bold text-indigo-600">Eventify Admin</h1>
    <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none text-2xl">☰</button>
</div>
<div class="flex">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Overlay -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>
    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">All Students</h1>
                    <p class="text-gray-600">Department: <?= htmlspecialchars($department_name) ?></p>
                </div>
                <div class="relative">
                    <button onclick="toggleSearch()" class="btn-primary text-white px-4 py-2 rounded-lg flex items-center">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div id="searchBar" class="mb-6 hidden">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search students..." 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                    <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                </div>
            </div>

            <?php if (!empty($password_errors)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                    <?= htmlspecialchars($password_errors[0]) ?>
                </div>
            <?php endif; ?>

            <?php if ($students): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($students as $student): ?>
                        <div class="student-card p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mr-3">
                                    <span class="text-lg font-medium"><?= strtoupper(substr(htmlspecialchars($student['full_name']), 0, 1)) ?></span>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($student['full_name']) ?></h3>
                                    <p class="text-sm text-gray-500">Joined: <?= date('M d, Y', strtotime($student['created_at'])) ?></p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Email</p>
                                    <a href="mailto:<?= htmlspecialchars($student['email']) ?>?subject=Regarding Your Account&body=Dear <?= htmlspecialchars(explode(' ', $student['full_name'])[0]) ?>," 
                                       class="text-indigo-600 hover:text-indigo-800 hover:underline flex items-center">
                                        <?= htmlspecialchars($student['email']) ?>
                                        <i class="fas fa-external-link-alt ml-2 text-xs"></i>
                                    </a>
                                </div>
                                
                                <!-- <div class="password-container">
                                    <p class="text-xs text-gray-500 mb-1">Password</p>
                                    <div class="flex items-center">
                                        <input type="password" 
                                               value="<?= $revealed_student_id == $student['student_id'] ? htmlspecialchars($revealed_password) : '••••••••' ?>" 
                                               class="bg-gray-100 px-3 py-2 rounded w-full" 
                                               readonly>
                                        <button onclick="showPasswordModal('<?= $student['student_id'] ?>')" 
                                                class="password-toggle ml-2">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div> -->
                                
                                <div class="flex justify-end space-x-2 pt-2">
                                    <button onclick="confirmRemove('<?= $student['student_id'] ?>', '<?= htmlspecialchars(addslashes($student['full_name'])) ?>')"
                                            class="btn-danger text-white px-3 py-1 text-sm rounded-lg flex items-center">
                                        <i class="fas fa-user-minus mr-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state rounded-lg text-center py-12">
                    <i class="fas fa-user-graduate text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No students found</h3>
                    <p class="text-gray-500 mt-1">There are currently no students in your department.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Password Reveal Modal -->
<div id="passwordModal" class="fixed inset-0 z-50 modal-overlay hidden flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-md">
        <form method="POST">
            <input type="hidden" name="reveal_password" value="1">
            <input type="hidden" id="modalStudentId" name="student_id" value="">
            
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-indigo-100 rounded-full mb-4">
                    <i class="fas fa-lock text-indigo-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Verify Your Identity</h3>
                <p class="text-gray-600 text-center mb-4">
                    Enter your admin password to view this student's password
                </p>
                
                <div class="mb-6 relative password-container">
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Admin Password</label>
                    <input type="password" id="admin_password" name="admin_password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500">
                </div>
                
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="hidePasswordModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-check mr-2"></i> Verify
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Remove Student Modal -->
<div id="removeModal" class="fixed inset-0 z-50 modal-overlay hidden flex items-center justify-center p-4">
    <div class="modal-content bg-white rounded-xl shadow-xl w-full max-w-md">
        <form method="POST">
            <input type="hidden" name="remove_student" value="1">
            <input type="hidden" id="removeStudentId" name="student_id" value="">
            
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2" id="removeStudentName"></h3>
                <p class="text-gray-600 text-center mb-6">
                    Are you sure you want to remove this student from your department?
                </p>
                
                <div class="flex justify-center space-x-4">
                    <button type="button" onclick="hideRemoveModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="btn-danger text-white px-6 py-2 rounded-lg flex items-center">
                        <i class="fas fa-trash mr-2"></i> Remove
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

function toggleSearch() {
    const searchBar = document.getElementById('searchBar');
    searchBar.classList.toggle('hidden');
    if (!searchBar.classList.contains('hidden')) {
        document.getElementById('searchInput').focus();
    }
}

// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.student-card');
    
    cards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const email = card.querySelector('a[href^="mailto:"]').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// Password reveal modal
function showPasswordModal(studentId) {
    document.getElementById('modalStudentId').value = studentId;
    document.getElementById('passwordModal').classList.remove('hidden');
}

function hidePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
}

// Remove student modal
function confirmRemove(studentId, studentName) {
    document.getElementById('removeStudentId').value = studentId;
    document.getElementById('removeStudentName').textContent = `Remove ${studentName}?`;
    document.getElementById('removeModal').classList.remove('hidden');
}

function hideRemoveModal() {
    document.getElementById('removeModal').classList.add('hidden');
}

// Close modals when clicking outside
window.onclick = function(event) {
    if (event.target === document.getElementById('passwordModal')) {
        hidePasswordModal();
    }
    if (event.target === document.getElementById('removeModal')) {
        hideRemoveModal();
    }
}
</script>
</body>
</html>