<?php
require_once '../includes/db.php';
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Department Dashboard - Eventify</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --secondary: #8b5cf6;
    }

    body {
      background-color: #f8fafc;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
    }

    .gradient-bg {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      position: relative;
      overflow: hidden;
    }

    .gradient-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      opacity: 0.2;
    }

    .dashboard-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
      position: relative;
    }

    .dashboard-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      transition: all 0.3s ease;
    }

    .dashboard-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .dashboard-card:hover::after {
      height: 6px;
    }
    .overlay {
      background-color: rgba(0, 0, 0, 0.4);
      z-index: 40;
    }

    .card-icon {
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      background: rgba(79, 70, 229, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary);
      margin-bottom: 1rem;
    }

    .card-action {
      display: inline-flex;
      align-items: center;
      color: var(--primary);
      font-weight: 500;
      transition: all 0.2s ease;
    }

    .card-action:hover {
      color: var(--primary-dark);
      transform: translateX(4px);
    }

    .card-action i {
      margin-left: 0.25rem;
      transition: all 0.2s ease;
    }

    .card-action:hover i {
      transform: translateX(2px);
    }

    .card-action-danger {
      color: #ef4444;
    }

    .card-action-danger:hover {
      color: #dc2626;
    }

    .welcome-header {
      position: relative;
      z-index: 10;
    }

    .floating-element {
      position: absolute;
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background: rgba(99, 102, 241, 0.05);
      z-index: 0;
    }

    .element-1 {
      top: -50px;
      right: -50px;
    }

    .element-2 {
      bottom: -80px;
      left: -80px;
      width: 300px;
      height: 300px;
    }
  </style>
</head>

<body class="min-h-screen">

  <!-- Floating decorative elements -->
  <!-- <div class="floating-element element-1"></div>
  <div class="floating-element element-2"></div> -->

  <!-- Mobile Nav -->
  <div class="md:hidden bg-white shadow px-4 py-3 flex justify-between items-center">
    <h1 class="text-xl font-bold text-indigo-600">Eventify Admin</h1>
    <button onclick="toggleSidebar()" class="text-indigo-600 focus:outline-none">☰ Menu</button>
  </div>

  <div class="flex">

    <!-- Sidebar Include -->
    <?php include 'sidebar.php'; ?>

    <!-- Overlay for Mobile Sidebar -->
    <div id="overlay" class="fixed inset-0 bg-black bg-opacity-40 z-40 hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-6">
      <div class="gradient-bg rounded-xl shadow-lg p-8 text-white mb-8">
        <div class="welcome-header">
          <h1 class="text-3xl font-bold mb-2">Welcome, <?= htmlspecialchars($_SESSION['department_name']) ?>!</h1>
          <p class="text-indigo-100">Manage your departmental events efficiently with Eventify.</p>
        </div>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Manage Events Card -->
        <div class="dashboard-card p-6">
          <div class="card-icon">
            <i class="fas fa-calendar-alt text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Manage Events</h3>
          <p class="text-gray-600 text-sm mb-4">Create, update, or delete your department's events.</p>
          <a href="manage_events.php" class="card-action">
            Go to Manage Events <i class="fas fa-arrow-right"></i>
          </a>
        </div>

        <!-- Notifications Card -->
        <div class="dashboard-card p-6">
          <div class="card-icon">
            <i class="fas fa-bell text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Send Notifications</h3>
          <p class="text-gray-600 text-sm mb-4">Notify students about upcoming events and announcements.</p>
          <a href="event_notify.php" class="card-action">
            Send Notifications <i class="fas fa-arrow-right"></i>
          </a>
        </div>

        <!-- Logout Card -->
        <div class="dashboard-card p-6">
          <div class="card-icon">
            <i class="fas fa-sign-out-alt text-xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Logout</h3>
          <p class="text-gray-600 text-sm mb-4">End your session securely.</p>
          <a href="dept_logout.php" class="card-action card-action-danger">
            Logout Now <i class="fas fa-arrow-right"></i>
          </a>
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
}
  </script>
</body>

</html>