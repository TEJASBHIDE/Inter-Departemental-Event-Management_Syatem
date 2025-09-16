<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['department_id'])) {
    header("Location: dept_login.php");
    exit();
}

$department_id = $_SESSION['department_id'];

// Fetch current department data
$stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
$stmt->execute([$department_id]);
$department = $stmt->fetch();

// Handle form submission
$errors = [];
$success = false;
$delete_errors = [];

// Clear any existing delete confirmation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['delete_confirmation']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile logic
        $department_name = trim($_POST['department_name'] ?? '');
        $admin_email = trim($_POST['admin_email'] ?? '');
        $current_password = trim($_POST['current_password'] ?? '');
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Validation
        if (empty($department_name)) {
            $errors['department_name'] = "Department name is required";
        }

        if (empty($admin_email)) {
            $errors['admin_email'] = "Email is required";
        } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
            $errors['admin_email'] = "Invalid email format";
        }

        $password_changed = false;
        if (!empty($new_password)) {
            if (!password_verify($current_password, $department['admin_password'])) {
                $errors['current_password'] = "Current password is incorrect";
            }

            if (strlen($new_password) < 8) {
                $errors['new_password'] = "Password must be at least 8 characters";
            } elseif ($new_password !== $confirm_password) {
                $errors['confirm_password'] = "Passwords do not match";
            } else {
                $password_changed = true;
            }
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                $update_data = [
                    'department_name' => $department_name,
                    'admin_email' => $admin_email,
                    'department_id' => $department_id
                ];

                $sql = "UPDATE departments SET department_name = :department_name, admin_email = :admin_email";
                if ($password_changed) {
                    $sql .= ", admin_password = :admin_password";
                    $update_data['admin_password'] = password_hash($new_password, PASSWORD_DEFAULT);
                }
                $sql .= " WHERE department_id = :department_id";

                $update_stmt = $pdo->prepare($sql);
                $update_stmt->execute($update_data);
                $pdo->commit();
                $success = true;
                
                // Refresh department data
                $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
                $stmt->execute([$department_id]);
                $department = $stmt->fetch();
                
                // Store success in session to prevent resubmission
                $_SESSION['update_success'] = true;
                header("Location: admin_profile.php");
                exit();
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors['general'] = "Error updating profile: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_profile'])) {
        // Delete profile logic
        if (!isset($_POST['confirm_delete'])) {
            $_SESSION['delete_confirmation'] = 'first';
        } elseif ($_POST['confirm_delete'] === 'first') {
            $entered_password = trim($_POST['delete_password'] ?? '');
            
            if (empty($entered_password)) {
                $delete_errors['delete_password'] = "Password is required";
            } elseif (!password_verify($entered_password, $department['admin_password'])) {
                $delete_errors['delete_password'] = "Incorrect password";
            } else {
                $_SESSION['delete_confirmation'] = 'second';
            }
        } elseif ($_POST['confirm_delete'] === 'second' && isset($_POST['final_confirm'])) {
            try {
                $pdo->beginTransaction();
                $delete_stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = ?");
                $delete_stmt->execute([$department_id]);
                $pdo->commit();
                
                session_destroy();
                header("Location: dept_login.php?account_deleted=1");
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $delete_errors['general'] = "Error deleting account: " . $e->getMessage();
            }
        }
    }
}

// Check for success message from session
if (isset($_SESSION['update_success'])) {
    $success = true;
    unset($_SESSION['update_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Department Admin Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --success: #10b981;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }
        
        .profile-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .profile-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background-color: white;
        }
        
        .input-error {
            border-color: var(--danger);
            background-color: #fef2f2;
        }
        
        .input-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
        }
        
        .btn-primary {
            background-color: var(--primary);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background-color: var(--danger);
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-hover);
            transform: translateY(-1px);
        }
        
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
        }
        
        .password-toggle:hover {
            color: var(--primary);
        }
        
        .delete-modal {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }
        
        .delete-content {
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
        
        .floating-label {
            position: absolute;
            left: 12px;
            top: -10px;
            background: white;
            padding: 0 4px;
            font-size: 12px;
            color: var(--primary);
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
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Nav Toggle -->
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
        <main class="flex-1 md:ml-64 p-6 mt-16 md:mt-0">
            <div class="max-w-4xl mx-auto">
                <!-- Profile Update Card -->
                <div class="profile-card p-8 mb-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Department Profile</h1>
                            <p class="text-gray-600">Manage your department information and security settings</p>
                        </div>
                    </div>
                    
                    <?php if ($success): ?>
                        <div id="successAlert" class="alert mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center">
                            <i class="fas fa-check-circle mr-2 text-green-500"></i>
                            Profile updated successfully!
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors['general'])): ?>
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center">
                            <i class="fas fa-exclamation-circle mr-2 text-red-500"></i>
                            <?= htmlspecialchars($errors['general']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="update_profile" value="1">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="relative">
                                <label for="department_name" class="floating-label">Department Name</label>
                                <input type="text" id="department_name" name="department_name" 
                                       value="<?= htmlspecialchars($department['department_name']) ?>" 
                                       class="input-field w-full px-4 py-3 rounded-lg <?= isset($errors['department_name']) ? 'input-error' : '' ?>">
                                <?php if (isset($errors['department_name'])): ?>
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?= htmlspecialchars($errors['department_name']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="relative">
                                <label for="admin_email" class="floating-label">Admin Email</label>
                                <input type="email" id="admin_email" name="admin_email" 
                                       value="<?= htmlspecialchars($department['admin_email']) ?>" 
                                       class="input-field w-full px-4 py-3 rounded-lg <?= isset($errors['admin_email']) ? 'input-error' : '' ?>">
                                <?php if (isset($errors['admin_email'])): ?>
                                    <p class="mt-1 text-sm text-red-600 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        <?= htmlspecialchars($errors['admin_email']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h3>
                            <p class="text-gray-500 mb-6">Leave these fields blank if you don't want to change your password.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="relative password-container">
                                    <label for="current_password" class="floating-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" 
                                           class="input-field w-full px-4 py-3 rounded-lg <?= isset($errors['current_password']) ? 'input-error' : '' ?>">
                                    <span class="password-toggle" onclick="togglePassword('current_password')">
                                        <i class="far fa-eye"></i>
                                    </span>
                                    <?php if (isset($errors['current_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= htmlspecialchars($errors['current_password']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="relative password-container">
                                    <label for="new_password" class="floating-label">New Password</label>
                                    <input type="password" id="new_password" name="new_password" 
                                           class="input-field w-full px-4 py-3 rounded-lg <?= isset($errors['new_password']) ? 'input-error' : '' ?>">
                                    <span class="password-toggle" onclick="togglePassword('new_password')">
                                        <i class="far fa-eye"></i>
                                    </span>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= htmlspecialchars($errors['new_password']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="relative password-container">
                                    <label for="confirm_password" class="floating-label">Confirm Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" 
                                           class="input-field w-full px-4 py-3 rounded-lg <?= isset($errors['confirm_password']) ? 'input-error' : '' ?>">
                                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="far fa-eye"></i>
                                    </span>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <p class="mt-1 text-sm text-red-600 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            <?= htmlspecialchars($errors['confirm_password']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-center pt-6">
                            <button type="submit" class="btn-primary text-white px-6 py-3 rounded-lg font-medium flex items-center">
                                <i class="fas fa-save mr-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                    
                    <div class="flex justify-center pt-8">
                        <button onclick="toggleDeleteModal()" class="btn-danger text-white px-6 py-3 rounded-lg font-medium flex items-center">
                            <i class="fas fa-trash-alt mr-2"></i> Delete Profile
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Delete Profile Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 delete-modal hidden flex items-center justify-center p-4">
        <div class="delete-content bg-white rounded-xl shadow-xl w-full max-w-md">
            <form method="POST">
                <input type="hidden" name="delete_profile" value="1">
                
                <?php if (!isset($_SESSION['delete_confirmation']) || $_SESSION['delete_confirmation'] !== 'second'): ?>
                    <!-- First Confirmation -->
                    <input type="hidden" name="confirm_delete" value="first">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Delete Your Account?</h3>
                        <p class="text-gray-600 text-center mb-6">
                            Are you sure you want to delete your department profile? This action cannot be undone.
                        </p>
                        <div class="flex justify-center space-x-4">
                            <button type="button" onclick="toggleDeleteModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="btn-danger text-white px-6 py-2 rounded-lg flex items-center">
                                <i class="fas fa-trash mr-2"></i> Continue
                            </button>
                        </div>
                    </div>
                
                <?php elseif ($_SESSION['delete_confirmation'] === 'first'): ?>
                    <!-- Second Confirmation (Password) -->
                    <input type="hidden" name="confirm_delete" value="first">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                            <i class="fas fa-lock text-red-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Verify Your Identity</h3>
                        <p class="text-gray-600 text-center mb-4">
                            For security, please enter your current password to continue.
                        </p>
                        
                        <div class="mb-6 relative password-container">
                            <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <input type="password" id="delete_password" name="delete_password" 
                                   class="input-field w-full px-4 py-3 rounded-lg <?= isset($delete_errors['delete_password']) ? 'input-error' : '' ?>">
                            <span class="password-toggle" onclick="togglePassword('delete_password')">
                                <i class="far fa-eye"></i>
                            </span>
                            <?php if (isset($delete_errors['delete_password'])): ?>
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    <?= htmlspecialchars($delete_errors['delete_password']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex justify-center space-x-4">
                            <button type="button" onclick="toggleDeleteModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="btn-danger text-white px-6 py-2 rounded-lg flex items-center">
                                <i class="fas fa-check mr-2"></i> Verify
                            </button>
                        </div>
                    </div>
                
                <?php elseif ($_SESSION['delete_confirmation'] === 'second'): ?>
                    <!-- Final Confirmation -->
                    <input type="hidden" name="confirm_delete" value="second">
                    <input type="hidden" name="final_confirm" value="1">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                            <i class="fas fa-exclamation-circle text-red-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Final Confirmation</h3>
                        <p class="text-gray-600 text-center mb-6">
                            This is your last chance to cancel. All your data will be permanently deleted.
                        </p>
                        <div class="flex justify-center space-x-4">
                            <button type="button" onclick="toggleDeleteModal()" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="btn-danger text-white px-6 py-2 rounded-lg flex items-center">
                                <i class="fas fa-trash mr-2"></i> Delete Permanently
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        }

        function toggleDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.toggle('hidden');
        }

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                toggleDeleteModal();
            }
        }

        // Auto-hide success alert after 5 seconds
        const successAlert = document.getElementById('successAlert');
        if (successAlert) {
            setTimeout(() => {
                successAlert.classList.add('fade-out');
                setTimeout(() => successAlert.remove(), 500);
            }, 5000);
        }

        // Prevent form resubmission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>