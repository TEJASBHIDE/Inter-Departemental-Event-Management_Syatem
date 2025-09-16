<?php
if (!isset($_SESSION['department_id'])) {
  header("Location: dept_login.php");
  exit();
}
?>

<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
  /* Custom scrollbar for the sidebar */
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.1);
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(255,255,255,0.4);
}
</style>
</head>
<div id="sidebar"
  class="sidebar bg-gradient-to-b from-indigo-500 to-indigo-800 w-70 px-6 py-8 fixed inset-y-0 left-0 shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out z-50">

  <div class="flex items-center mb-10 pb-6 border-b border-indigo-900">
    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3">
      <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
    </div>
    <div>
      <h2 class="text-xl font-bold text-white">Eventify Admin</h2>
      <p class="text-xs text-indigo-200">Department Portal</p>
    </div>
  </div>

  <div class="flex items-center mb-8 px-3 py-3 bg-white rounded-lg">
    <div class="w-10 h-10 bg-indigo-400 rounded-full flex items-center justify-center text-white mr-3">
      <span class="text-lg font-medium"><?= substr(htmlspecialchars($_SESSION['department_name']), 0, 1) ?></span>
    </div>
    <div>
      <p class="text-sm font-medium text-indigo-500"><?= htmlspecialchars($_SESSION['department_name']) ?></p>
      <p class="text-xs text-indigo-500">Admin</p>
    </div>
  </div>
  <div class="overflow-y-auto h-[calc(90vh-200px)]">
    <nav class="space-y-2">
      <a href="dept_dashboard.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-home mr-3 text-white-300 group-hover:text-white"></i>
        <span>Dashboard</span>
      </a>

      <a href="manage_events.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-calendar-check mr-3 text-white-300 group-hover:text-white"></i>
        <span>Manage Events</span>
      </a>

      <a href="event_notify.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-bell mr-3 text-white-300 group-hover:text-white"></i>
        <span>Send Notifications</span>
      </a>

      <a href="enrolled_students.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-users mr-3 text-white-300 group-hover:text-white"></i>
        <span>Enrolled Students</span>
      </a>

      <a href="event_feedbacks.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-comment-alt mr-3 text-white-300 group-hover:text-white"></i>
        <span>Event Feedbacks</span>
      </a>

      <a href="contact_reply.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-comment-dots mr-3 text-white-300 group-hover:text-white"></i>
        <span>Contact / Reply</span>
      </a>

      <a href="reports.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-chart-bar mr-3 text-white-300 group-hover:text-white"></i>
        <span>Generate Reports</span>
      </a>

      <a href="view_suggestions.php"
        class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
        <i class="fas fa-eye mr-3 text-white-300 group-hover:text-white"></i>
        <span> View Suggestions </span>
      </a>

      <div class="pt-4 mt-4 border-t border-white-1000">
        <a href="all_students.php"
          class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
          <i class="fas fa-user-graduate mr-3 text-white-300 group-hover:text-white"></i>
          <span>All Students</span>
        </a>

        <a href="admin_profile.php"
          class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-white/20 transition-all group">
          <i class="fas fa-circle-user mr-3 text-white-300 group-hover:text-white"></i>
          <span>My Profile</span>
        </a>

        <a href="dept_logout.php"
          class="flex items-center px-4 py-3 rounded-lg text-red-500 hover:bg-red-500/20 hover:text-red-500 transition-all group">
          <i class="fas fa-sign-out-alt mr-3"></i>
          <span>Logout</span>
        </a>
      </div>
    </nav>
  </div>
</div>