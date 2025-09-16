<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Eventify - Departmental Event System</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Custom Inline CSS -->
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      scroll-behavior: smooth;
    }

    .hero-bg {
      background: linear-gradient(to right, #4f46e5, #3b82f6);
    }

    .hero-img {
      max-height: 420px;
      object-fit: cover;
      border-radius: 1rem;
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .hover-zoom:hover {
      transform: scale(1.05);
      transition: 0.3s ease-in-out;
    }

    .btn {
      transition: all 0.3s ease-in-out;
    }

    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800">

  <!-- HEADER -->
  <header class="bg-white shadow-md fixed top-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-indigo-600">Eventify</h1>
      <nav class="hidden md:flex space-x-6">
        <a href="#about" class="hover:text-indigo-600 transition">About</a>
        <a href="#features" class="hover:text-indigo-600 transition">Features</a>
        <a href="#cta" class="bg-indigo-600 text-white px-4 py-2 rounded btn">Get Started</a>
      </nav>
      <button class="md:hidden text-indigo-600 text-2xl" onclick="toggleMenu()">☰</button>
    </div>
    <div id="mobileMenu" class="md:hidden px-4 pb-4 hidden">
      <a href="#about" class="block py-2">About</a>
      <a href="#features" class="block py-2">Features</a>
      <a href="#cta" class="block py-2 text-indigo-600 font-semibold">Get Started</a>
    </div>
  </header>

  <!-- HERO -->
  <section class="hero-bg text-white pt-28 pb-20">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center">
      <div class="md:w-1/2 text-center md:text-left mb-10 md:mb-0">
        <h2 class="text-4xl md:text-5xl font-bold leading-tight mb-4">
          Your Department Events,<br/> All in One Place.
        </h2>
        <p class="text-lg mb-6">View, enroll, and track all departmental events with ease.</p>
        <div class="space-x-4">
          <a href="./student/signup.php" class="btn bg-white text-indigo-600 px-6 py-3 font-semibold rounded hover:bg-gray-100">Student Signup</a>
          <a href="./student/login.php" class="btn border border-white px-6 py-3 rounded text-white hover:bg-white hover:text-indigo-600">Student Login</a>
        </div>
      </div>
      <div class="md:w-1/2">
        <img src="https://img.freepik.com/free-vector/tiny-people-high-school-students-dresses-suits-chatting-promenade-dance-prom-party-prom-night-invitation-promenade-school-dance-concept-pinkish-coral-bluevector-isolated-illustration_335657-1471.jpg?ga=GA1.1.264532609.1750502203&semt=ais_hybrid&w=740" alt="Hero Image" class="hero-img w-full">
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about" class="bg-white py-16 px-4 text-center">
    <div class="max-w-3xl mx-auto">
      <h3 class="text-3xl font-bold text-indigo-700 mb-4">What is Eventify?</h3>
      <p class="text-lg text-gray-600">
        Eventify is a dedicated platform built for students to manage and participate in departmental events. From real-time updates to participation history, everything is managed under one intuitive dashboard.
      </p>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="bg-gray-100 py-16 px-4">
    <div class="max-w-6xl mx-auto text-center">
      <h3 class="text-3xl font-bold text-indigo-700 mb-10">Features You’ll Love</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow hover-zoom">
          <img src="https://cdn-icons-png.flaticon.com/512/9068/9068669.png" class="w-16 mx-auto mb-4" alt="Events">
          <h4 class="text-xl font-semibold text-indigo-600 mb-2">Upcoming Events</h4>
          <p class="text-gray-600">Easily browse upcoming departmental events curated for you.</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover-zoom">
          <img src="https://cdn-icons-png.flaticon.com/512/942/942748.png" class="w-16 mx-auto mb-4" alt="Enroll">
          <h4 class="text-xl font-semibold text-indigo-600 mb-2">Enroll Instantly</h4>
          <p class="text-gray-600">Register with one click and stay informed with reminders.</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow hover-zoom">
          <img src="https://cdn-icons-png.flaticon.com/512/1077/1077192.png" class="w-16 mx-auto mb-4" alt="Feedback">
          <h4 class="text-xl font-semibold text-indigo-600 mb-2">Feedback System</h4>
          <p class="text-gray-600">Submit feedback to help departments improve future events.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section id="cta" class="bg-indigo-600 text-white py-16 text-center px-4">
    <h3 class="text-3xl font-bold mb-2">Ready to Explore?</h3>
    <p class="text-lg mb-6">Join now and participate in exciting departmental activities!</p>
    <div class="space-x-4">
      <a href="./student/signup.php" class="btn bg-white text-indigo-600 font-semibold px-6 py-3 rounded hover:bg-gray-100">Student Signup</a>
      <a href="./student/login.php" class="btn border border-white px-6 py-3 rounded hover:bg-white hover:text-indigo-600">Student Login</a>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-800 text-white py-6 text-center text-sm">
    &copy; <?php echo date('Y'); ?> Eventify — Inter-Departmental Event Management System
  </footer>

  <!-- JavaScript -->
  <script>
    function toggleMenu() {
      const menu = document.getElementById("mobileMenu");
      menu.classList.toggle("hidden");
    }
  </script>
</body>
</html>
