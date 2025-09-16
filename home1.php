<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <title>Eventify - Departmental Event System</title>

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  
  <!-- Animate.css CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom Inline CSS -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
    
    :root {
      --primary: #4f46e5;
      --primary-light: #6366f1;
      --primary-dark: #4338ca;
      --secondary: #f59e0b;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      scroll-behavior: smooth;
      overflow-x: hidden;
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
      width: 8px;
    }
    ::-webkit-scrollbar-track {
      background: #f1f1f1;
    }
    ::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: var(--primary-dark);
    }

    .hero-bg {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      position: relative;
      overflow: hidden;
    }
    
    .hero-bg::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      opacity: 0.3;
    }

    .hero-img {
      max-height: 420px;
      object-fit: cover;
      border-radius: 1.5rem;
      box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
      transform: perspective(1000px) rotateY(-10deg);
      border: 8px solid rgba(255,255,255,0.1);
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    .hero-img:hover {
      transform: perspective(1000px) rotateY(0deg) scale(1.03);
      box-shadow: 0 35px 60px -15px rgba(0,0,0,0.3);
    }

    .feature-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
      transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .feature-card::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--primary), var(--secondary));
      transition: all 0.3s ease;
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 20px 40px -15px rgba(0,0,0,0.2);
    }
    
    .feature-card:hover::after {
      height: 10px;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      transition: all 0.4s ease;
      z-index: -1;
    }
    
    .btn-primary:hover::before {
      left: 0;
    }
    
    .btn-outline {
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .btn-outline::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: white;
      transition: all 0.4s ease;
      z-index: -1;
    }
    
    .btn-outline:hover::before {
      left: 0;
    }
    
    .btn-outline:hover {
      color: var(--primary);
    }
    
    /* Pulse animation */
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .pulse {
      animation: pulse 2s infinite;
    }
    
    /* Floating animation */
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-15px); }
      100% { transform: translateY(0px); }
    }
    
    .float {
      animation: float 6s ease-in-out infinite;
    }
    
    /* Wave animation */
    .wave {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 100px;
      background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="%23ffffff" opacity=".25"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="%23ffffff" opacity=".5"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23ffffff"/></svg>');
      background-size: cover;
      background-repeat: no-repeat;
    }
    
    /* Stats counter */
    .stat-item {
      position: relative;
      padding: 1.5rem;
      border-radius: 1rem;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
    }
    
    /* Testimonial cards */
    .testimonial-card {
      background: white;
      border-radius: 1rem;
      box-shadow: 0 10px 30px -10px rgba(0,0,0,0.1);
      position: relative;
    }
    
    .testimonial-card::before {
      content: '"';
      position: absolute;
      top: -1rem;
      left: 1rem;
      font-size: 5rem;
      color: rgba(79, 70, 229, 0.1);
      font-family: serif;
      line-height: 1;
    }
    
    /* Departments grid */
    .department-logo {
      width: 80px;
      height: 80px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      border-radius: 50%;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      margin: 0 auto 1rem;
      transition: all 0.3s ease;
    }
    
    .department-logo:hover {
      transform: scale(1.1) rotate(10deg);
    }
    
    /* FAQ accordion */
    .faq-item {
      border-bottom: 1px solid #e2e8f0;
    }
    
    .faq-question {
      cursor: pointer;
      padding: 1rem 0;
      position: relative;
    }
    
    .faq-question::after {
      content: '+';
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      font-size: 1.5rem;
      color: var(--primary);
      transition: all 0.3s ease;
    }
    
    .faq-item.active .faq-question::after {
      content: '-';
    }
    
    .faq-answer {
      max-height: 0;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .faq-item.active .faq-answer {
      max-height: 500px;
      padding-bottom: 1rem;
    }
    
    /* Floating elements */
    .floating-element {
      position: absolute;
      border-radius: 50%;
      opacity: 0.1;
      z-index: 0;
    }
    
    /* Gradient text */
    .gradient-text {
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }
    
    /* Mobile menu */
    #mobileMenu {
      max-height: 0;
      transition: max-height 0.3s ease-out;
      overflow: hidden;
    }
    
    #mobileMenu.show {
      max-height: 500px;
      transition: max-height 0.5s ease-in;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
      .hero-bg {
        padding-top: 6rem;
        padding-bottom: 4rem;
      }
      
      .hero-img {
        max-height: 300px;
        transform: perspective(1000px) rotateY(0deg);
        margin-top: 2rem;
      }
      
      .hero-img:hover {
        transform: scale(1.02);
      }

      section {
        padding: 2rem 1rem !important;
      }

      .max-w-7xl {
        padding-left: 1rem;
        padding-right: 1rem;
      }

      .lg\:flex-row {
        flex-direction: column;
      }
      
      .lg\:w-1\/2 {
        width: 100%;
      }

      #departments .grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
      }
      
      .department-logo {
        width: 60px;
        height: 60px;
      }

      .feature-card {
        padding: 1.5rem;
      }
    
      .feature-card:hover {
        transform: none;
      }

      .faq-question {
        padding-right: 2rem;
      }

      #cta .flex {
        flex-direction: column;
        gap: 1rem;
      }
      
      #cta a {
        width: 100%;
        text-align: center;
      }

      footer .grid {
        grid-template-columns: 1fr;
        gap: 2rem;
      }

      h1, h2, h3 {
        font-size: 1.5rem !important;
      }
      
      p {
        font-size: 0.9rem !important;
      }

      header {
        padding: 1rem 0 !important;
      }
    }

    @media (max-width: 640px) {
      .flex-col {
        gap: 1rem !important;
      }

      .btn-primary, .btn-outline {
        padding: 0.75rem 1.5rem !important;
        font-size: 0.9rem;
      }
    }

    img {
      max-width: 100%;
      height: auto;
    }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 relative overflow-x-hidden">

  <!-- Floating decorative elements -->
  <div class="floating-element w-64 h-64 bg-indigo-100 rounded-full -left-32 -top-32 fixed"></div>
  <div class="floating-element w-96 h-96 bg-amber-100 rounded-full -right-48 bottom-1/3 fixed"></div>
  <div class="floating-element w-80 h-80 bg-indigo-200 rounded-full right-1/4 top-1/4 fixed"></div>

  <!-- HEADER -->
  <header class="bg-white shadow-md fixed top-0 w-full z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center mr-3">
          <i class="fas fa-calendar-alt text-white"></i>
        </div>
        <h1 class="text-2xl font-bold text-indigo-600">Eventify</h1>
      </div>
      <nav class="hidden md:flex space-x-6 items-center">
        <a href="#about" class="hover:text-indigo-600 transition relative group">
          About
          <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="#features" class="hover:text-indigo-600 transition relative group">
          Features
          <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="#departments" class="hover:text-indigo-600 transition relative group">
          Departments
          <span class="absolute left-0 bottom-0 w-0 h-0.5 bg-indigo-600 transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="./student/signup.php" class="btn-primary text-white px-6 py-2 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all">
          Get Started
        </a>
      </nav>
      <button class="md:hidden text-indigo-600 text-2xl focus:outline-none" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    <div id="mobileMenu" class="md:hidden px-6 bg-white shadow-lg">
      <a href="#about" class="block py-3 border-b border-gray-100 hover:text-indigo-600 transition">About</a>
      <a href="#features" class="block py-3 border-b border-gray-100 hover:text-indigo-600 transition">Features</a>
      <a href="#departments" class="block py-3 border-b border-gray-100 hover:text-indigo-600 transition">Departments</a>
      <a href="./student/signup.php" class="block py-3 text-indigo-600 font-semibold">Get Started</a>
    </div>
  </header>

  <!-- HERO -->
  <section class="hero-bg text-white pt-32 pb-32 relative">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center relative z-10">
      <div class="md:w-1/2 text-center md:text-left mb-10 md:mb-0 animate__animated animate__fadeInLeft">
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-4">
          <span class="gradient-text  text-yellow-500" >Your Department Events,</span><br/> All in One Place.
        </h2>
        <p class="text-lg md:text-xl mb-6 opacity-90">View, enroll, and track all departmental events with our intuitive platform designed for students.</p>
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
          <a href="./student/signup.php" class="btn-primary text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all text-center">
            <i class="fas fa-user-plus mr-2"></i> Student Signup
          </a>
          <a href="./student/login.php" class="btn-outline border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:text-indigo-600 transition-all text-center">
            <i class="fas fa-sign-in-alt mr-2"></i> Student Login
          </a>
        </div>
      </div>
      <div class="md:w-1/2 animate__animated animate__fadeInRight">
        <img src="https://img.freepik.com/free-vector/tiny-people-high-school-students-dresses-suits-chatting-promenade-dance-prom-party-prom-night-invitation-promenade-school-dance-concept-pinkish-coral-bluevector-isolated-illustration_335657-1471.jpg" 
             alt="Hero Image" 
             class="hero-img w-full float">
      </div>
    </div>
    
    <!-- Wave divider -->
    <div class="wave"></div>
  </section>

  <!-- ABOUT -->
  <section id="about" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <span class="text-indigo-600 font-semibold">ABOUT EVENTIFY</span>
        <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Revolutionizing Departmental Events</h3>
        <div class="w-20 h-1 bg-indigo-600 mx-auto mt-4"></div>
      </div>
      
      <div class="flex flex-col lg:flex-row items-center gap-12">
        <div class="lg:w-1/2 animate__animated">
          <div class="relative">
            <img src="https://img.freepik.com/free-vector/group-students-hanging-out_74855-5251.jpg" 
                 alt="About Eventify" 
                 class="rounded-xl shadow-2xl w-full">
          </div>
        </div>
        <div class="lg:w-1/2 animate__animated">
          <h4 class="text-2xl font-bold text-gray-800 mb-4">A Platform Built for Students, by Students</h4>
          <p class="text-gray-600 mb-6">
            Eventify was born out of a need to simplify event management across university departments. 
            We noticed students were missing out on great opportunities simply because they didn't know 
            about them or the signup process was too complicated.
          </p>
          <p class="text-gray-600 mb-6">
            Our platform brings all departmental events together in one place, with real-time updates, 
            easy enrollment, and personalized recommendations based on your interests and department.
          </p>
          
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4 mt-1">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold text-gray-800">Centralized Event Hub</h5>
                <p class="text-gray-600">All events from all departments in one dashboard</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4 mt-1">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold text-gray-800">Smart Recommendations</h5>
                <p class="text-gray-600">Personalized event suggestions based on your interests</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-indigo-100 p-2 rounded-full mr-4 mt-1">
                <i class="fas fa-check text-indigo-600"></i>
              </div>
              <div>
                <h5 class="font-semibold text-gray-800">Seamless Integration</h5>
                <p class="text-gray-600">Works with your existing university credentials</p>
              </div>
            </div>
          </div>
          
          <a href="#features" class="btn-primary inline-block mt-8 text-white px-6 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all">
            Explore Features <i class="fas fa-arrow-right ml-2"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <span class="text-indigo-600 font-semibold">WHY CHOOSE EVENTIFY</span>
        <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Features Designed for Student Success</h3>
        <p class="text-gray-600 max-w-2xl mx-auto mt-4">
          Our platform is packed with powerful features to enhance your university experience
        </p>
        <div class="w-20 h-1 bg-indigo-600 mx-auto mt-4"></div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-calendar-day text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">Upcoming Events</h4>
          <p class="text-gray-600 text-center">
            Easily browse upcoming departmental events curated for you with detailed descriptions, dates, and locations.
          </p>
        </div>
        
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-user-check text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">One-Click Enrollment</h4>
          <p class="text-gray-600 text-center">
            Register with one click and stay informed with automated reminders and calendar integration.
          </p>
        </div>
        
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-comment-dots text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">Feedback System</h4>
          <p class="text-gray-600 text-center">
            Submit feedback to help departments improve future events and shape the student experience.
          </p>
        </div>
        
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-bell text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">Personalized Alerts</h4>
          <p class="text-gray-600 text-center">
            Get notifications for events matching your interests, department, and schedule.
          </p>
        </div>
        
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-chart-line text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">Participation History</h4>
          <p class="text-gray-600 text-center">
            Track all your event participation in one place, perfect for building your portfolio.
          </p>
        </div>
        
        <div class="feature-card p-8 animate__animated">
          <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i class="fas fa-users text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="text-xl font-semibold text-center text-gray-800 mb-3">Social Features</h4>
          <p class="text-gray-600 text-center">
            See which friends are attending, share events, and connect with like-minded students.
          </p>
        </div>
      </div>
      
      <div class="text-center mt-16">
        <a href="./student/signup.php" class="btn-primary inline-block text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all text-lg">
          Start Exploring Events Now <i class="fas fa-arrow-right ml-2"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- DEPARTMENTS -->
  <section id="departments" class="py-20 px-4 sm:px-6 lg:px-8 bg-gray-50">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <span class="text-indigo-600 font-semibold">DEPARTMENT INTEGRATION</span>
        <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Participating Departments</h3>
        <p class="text-gray-600 max-w-2xl mx-auto mt-4">
          Eventify connects students with events across all major university departments
        </p>
        <div class="w-20 h-1 bg-indigo-600 mx-auto mt-4"></div>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8">
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fa-sharp fa-solid fa-bolt text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Electrical</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-laptop-code text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Computer Science</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fa-solid fa-desktop text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Information Technology</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-solid fa-gears text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold"> Mechanical</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-tower-cell text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">EXTC</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-user-secret text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">CSE</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-flask-vial text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Chemical Engineering</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-file-code text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">MCA</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-leaf text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Environmental</h4>
        </div>
        
        <div class="text-center animate__animated">
          <div class="department-logo">
            <i class="fas fa-heartbeat text-indigo-600 text-2xl"></i>
          </div>
          <h4 class="font-semibold">Medicine</h4>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <span class="text-indigo-600 font-semibold">GET STARTED</span>
        <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">How Eventify Works</h3>
        <p class="text-gray-600 max-w-2xl mx-auto mt-4">
          Getting started with Eventify is quick and easy
        </p>
        <div class="w-20 h-1 bg-indigo-600 mx-auto mt-4"></div>
      </div>
      
      <div class="flex flex-col lg:flex-row gap-12 items-center">
        <div class="lg:w-1/2">
          <div class="relative">
            <div class="absolute -top-6 -left-6 w-24 h-24 bg-indigo-100 rounded-lg z-0"></div>
            <div class="absolute -bottom-6 -right-6 w-24 h-24 bg-amber-100 rounded-lg z-0"></div>
            <div class="relative z-10">
              <img src="https://img.freepik.com/free-vector/mobile-login-concept-illustration_114360-83.jpg" 
                   alt="How it works" 
                   class="rounded-xl shadow-lg w-full">
            </div>
          </div>
        </div>
        
        <div class="lg:w-1/2">
          <div class="space-y-8">
            <div class="flex items-start animate__animated">
              <div class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0 mr-4 mt-1">
                <span class="font-bold">1</span>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-800 mb-2">Create Your Account</h4>
                <p class="text-gray-600">
                  Sign up using your university email to verify your student status. It takes less than 2 minutes.
                </p>
              </div>
            </div>
            
            <div class="flex items-start animate__animated">
              <div class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0 mr-4 mt-1">
                <span class="font-bold">2</span>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-800 mb-2">Select Your Departments</h4>
                <p class="text-gray-600">
                  Choose which departments you want to follow to get personalized event recommendations.
                </p>
              </div>
            </div>
            
            <div class="flex items-start animate__animated">
              <div class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0 mr-4 mt-1">
                <span class="font-bold">3</span>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-800 mb-2">Browse & Register</h4>
                <p class="text-gray-600">
                  Explore upcoming events, read details, and register with a single click.
                </p>
              </div>
            </div>
            
            <div class="flex items-start animate__animated">
              <div class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center flex-shrink-0 mr-4 mt-1">
                <span class="font-bold">4</span>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-800 mb-2">Get Reminders & Attend</h4>
                <p class="text-gray-600">
                  Receive automatic reminders before events and track your participation history.
                </p>
              </div>
            </div>
          </div>
          
          <div class="mt-10">
            <a href="./student/signup.php" class="btn-primary inline-block text-white px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all">
              Create Your Free Account <i class="fas fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-16">
        <span class="text-indigo-600 font-semibold">HAVE QUESTIONS?</span>
        <h3 class="text-3xl md:text-4xl font-bold text-gray-800 mt-2">Frequently Asked Questions</h3>
        <p class="text-gray-600 max-w-2xl mx-auto mt-4">
          Find answers to common questions about Eventify
        </p>
        <div class="w-20 h-1 bg-indigo-600 mx-auto mt-4"></div>
      </div>
      
      <div class="space-y-4">
        <div class="faq-item animate__animated">
          <div class="faq-question">
            <h4 class="text-lg font-semibold">Is Eventify free for students?</h4>
          </div>
          <div class="faq-answer">
            <p class="text-gray-600">
              Yes! Eventify is completely free for all university students. Our platform is supported by the university and participating departments to enhance student engagement.
            </p>
          </div>
        </div>
        
        <div class="faq-item animate__animated">
          <div class="faq-question">
            <h4 class="text-lg font-semibold">How do I verify my student status?</h4>
          </div>
          <div class="faq-answer">
            <p class="text-gray-600">
              Simply sign up with your university email address (@youruniversity.edu). This automatically verifies you as a current student. If you're having trouble, contact our support team.
            </p>
          </div>
        </div>
        
        <div class="faq-item animate__animated">
          <div class="faq-question">
            <h4 class="text-lg font-semibold">Can I sync events with my personal calendar?</h4>
          </div>
          <div class="faq-answer">
            <p class="text-gray-600">
              Absolutely. Once you register for an event, you'll have the option to download an .ics file or connect your Google/Outlook calendar for automatic syncing.
            </p>
          </div>
        </div>
        
        <div class="faq-item animate__animated">
          <div class="faq-question">
            <h4 class="text-lg font-semibold">What if I need to cancel my registration?</h4>
          </div>
          <div class="faq-answer">
            <p class="text-gray-600">
              You can cancel your registration for any event up to 24 hours before it starts through your dashboard. This helps departments manage attendance numbers.
            </p>
          </div>
        </div>
        
        <div class="faq-item animate__animated">
          <div class="faq-question">
            <h4 class="text-lg font-semibold">How can my department join Eventify?</h4>
          </div>
          <div class="faq-answer">
            <p class="text-gray-600">
              Department administrators can contact our integration team through the "Department Integration" link in the footer. We'll guide you through the simple onboarding process.
            </p>
          </div>
        </div>
      </div>
      
      <div class="text-center mt-12">
        <p class="text-gray-600 mb-4">Still have questions?</p>
        <a href="student/contact_department.php" class="btn-outline border-2 border-indigo-600 text-indigo-600 px-6 py-2 rounded-full font-semibold hover:bg-indigo-600 hover:text-white transition-all">
          Contact Our Support Team
        </a>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section id="cta" class="py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-indigo-600 to-indigo-500 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto text-center relative z-10">
      <h3 class="text-3xl md:text-4xl font-bold mb-4">Ready to Transform Your University Experience?</h3>
      <p class="text-xl opacity-90 max-w-3xl mx-auto mb-8">
        Join thousands of students who never miss out on amazing departmental events and opportunities.
      </p>
      <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
        <a href="./student/signup.php" class="btn-primary bg-white text-white-600 px-8 py-3 rounded-full font-semibold shadow-lg hover:shadow-xl transition-all inline-block">
          <i class="fas fa-user-plus mr-2"></i> Student Signup
        </a>
        <a href="./student/login.php" class="btn-outline border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-indigo-600 transition-all inline-block">
          <i class="fas fa-sign-in-alt mr-2"></i> Student Login
        </a>
      </div>
    </div>
    
    <!-- Floating elements for decoration -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
      <div class="absolute top-20 left-20 w-16 h-16 bg-white rounded-full opacity-10 animate-float"></div>
      <div class="absolute bottom-1/4 right-32 w-24 h-24 bg-white rounded-full opacity-10 animate-float animation-delay-2000"></div>
      <div class="absolute top-1/3 right-1/4 w-20 h-20 bg-white rounded-full opacity-10 animate-float animation-delay-4000"></div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-900 text-gray-300 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <div>
          <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center mr-2">
              <i class="fas fa-calendar-alt text-white"></i>
            </div>
            <h3 class="text-xl font-bold text-white">Eventify</h3>
          </div>
          <p class="text-gray-400 mb-4">
            The premier platform for university departmental event management and student engagement.
          </p>
          <div class="flex space-x-4">
            <a href="#" class="text-gray-400 hover:text-white transition">
              <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition">
              <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition">
              <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-white transition">
              <i class="fab fa-linkedin-in"></i>
            </a>
          </div>
        </div>
        
        <div>
          <h4 class="text-white font-semibold mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li><a href="#about" class="text-gray-400 hover:text-white transition">About</a></li>
            <li><a href="#features" class="text-gray-400 hover:text-white transition">Features</a></li>
            <li><a href="#departments" class="text-gray-400 hover:text-white transition">Departments</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-white font-semibold mb-4">For Students</h4>
          <ul class="space-y-2">
            <li><a href="./student/signup.php" class="text-gray-400 hover:text-white transition">Sign Up</a></li>
            <li><a href="./student/login.php" class="text-gray-400 hover:text-white transition">Login</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition">Event Guidelines</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="text-white font-semibold mb-4">Contact Us</h4>
          <ul class="space-y-2">
            <li class="flex items-start">
              <i class="fas fa-map-marker-alt text-indigo-400 mt-1 mr-2"></i>
              <span class="text-gray-400">University Campus, 123 College Ave, City</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-envelope text-indigo-400 mt-1 mr-2"></i>
              <span class="text-gray-400">support@eventify.edu</span>
            </li>
            <li class="flex items-start">
              <i class="fas fa-phone-alt text-indigo-400 mt-1 mr-2"></i>
              <span class="text-gray-400">(123) 456-7890</span>
            </li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
        <p>&copy; <?php echo date('Y'); ?> Eventify — Inter-Departmental Event Management System. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="backToTop" class="fixed bottom-8 right-8 bg-indigo-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 opacity-0 invisible hover:bg-indigo-700">
    <i class="fas fa-arrow-up"></i>
  </button>

  <!-- JavaScript -->
  <script>
    // Mobile menu toggle
    function toggleMenu() {
      const menu = document.getElementById("mobileMenu");
      menu.classList.toggle("show");
    }
    
    // Back to top button
    const backToTopButton = document.getElementById("backToTop");
    
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        backToTopButton.classList.remove('opacity-0', 'invisible');
        backToTopButton.classList.add('opacity-100', 'visible');
      } else {
        backToTopButton.classList.remove('opacity-100', 'visible');
        backToTopButton.classList.add('opacity-0', 'invisible');
      }
    });
    
    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
    
    // FAQ accordion
    document.querySelectorAll('.faq-question').forEach(question => {
      question.addEventListener('click', () => {
        const item = question.parentElement;
        item.classList.toggle('active');
      });
    });
    
    // Scroll animations
    function animateOnScroll() {
      const elements = document.querySelectorAll('.animate__animated');
      
      elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (elementPosition < screenPosition) {
          element.classList.add('animate__fadeInUp');
        }
      });
    }
    
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);
    
    // Counter animation for stats
    function animateCounters() {
      const counters = document.querySelectorAll('.counter');
      const speed = 200;
      
      counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
          counter.innerText = Math.ceil(count + increment);
          setTimeout(animateCounters, 1);
        } else {
          counter.innerText = target;
        }
      });
    }
    
    // Start counter animation when stats section is in view
    const statsSection = document.querySelector('.bg-white');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounters();
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    
    if (statsSection) {
      observer.observe(statsSection);
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;
        
        const targetElement = document.querySelector(targetId);
        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop - 80,
            behavior: 'smooth'
          });
          
          // Close mobile menu if open
          const mobileMenu = document.getElementById('mobileMenu');
          if (mobileMenu.classList.contains('show')) {
            mobileMenu.classList.remove('show');
          }
        }
      });
    });
    
    // Header scroll effect
    const header = document.querySelector('header');
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      
      if (currentScroll <= 0) {
        header.classList.remove('shadow-lg');
        return;
      }
      
      if (currentScroll > lastScroll && currentScroll > 100) {
        // Scroll down
        header.classList.add('-translate-y-full');
        header.classList.remove('shadow-lg');
      } else if (currentScroll < lastScroll && currentScroll > 100) {
        // Scroll up
        header.classList.remove('-translate-y-full');
        header.classList.add('shadow-lg');
      }
      
      lastScroll = currentScroll;
    });
  </script>
</body>
</html>