# Inter-Departemental-Event-Management_Syatem

🎉 Event Management System
A web-based Event Management System built with PHP, MySQL, and Tailwind CSS, designed for colleges/universities to manage inter-department events efficiently.
This system provides separate dashboards for Students, Departments, and Admins, enabling event creation, enrollment, feedback, reports, and communication seamlessly.

📌 Features
👨‍🎓 Student Module

-  Student login and signup
- View upcoming events from their department
- Enroll/Unenroll in events before the deadline
- Submit event feedback after completion
- Suggest new events to their department
- Track participation history & report downloads


🏫 Department Module

-  Different department’s  Login and signup
- Create & manage events with deadlines and participant limits
- Notify students via automated email (PHPMailer integration)
- View and reply to student messages
- Generate PDF event reports using FPDF
- Track event suggestions from students


⚡ Additional Highlights

- Fully Responsive UI (Tailwind CSS)
- Role-based access control (Student / Department / Admin)
- Event status auto-updates (Upcoming, Enrollment Closed, Completed)
- Attractive and user-friendly dashboard for all roles
- Security measures: Sessions, input validation, password hashing


🛠️ Tech Stack

- Frontend: HTML5, CSS3, Tailwind CSS
- Backend: PHP 8+, PDO for database interaction
- Database: MySQL (phpMyAdmin for management)
- Libraries:
  - PHPMailer – Email Notifications
  - FPDF – PDF Report Generation


📂 Project Structure

event-management-system/
│── admin/               # Admin dashboard pages
│── department/          # Department dashboard pages
│── student/             # Student dashboard pages
│── includes/            # DB connection & reusable files
│── fpdf186/             # FPDF library for reports
│── PHPMailer/           # PHPMailer library for emails
│── assets/              # Images, logos, css/js (if any)
│── index.php            # Landing page
│── README.md            # Project Documentation


⚙️ Installation & Setup

1. Clone the repository
   git clone https://github.com/your-username/event-management-system.git
   cd event-management-system

2. Move to XAMPP/htdocs
   Place the project inside C:/xampp/htdocs/

3. Create Database
   - Open phpMyAdmin
   - Create a new database (e.g., event_management)
   - Import event_management.sql (provided in repo)

4. Configure Database Connection
   Update includes/db.php with your MySQL credentials:
   $pdo = new PDO("mysql:host=localhost;dbname=event_management", "root", "");

5. Start the Server
   Run Apache & MySQL in XAMPP, then open:
   http://localhost/event-management-system/


📸 Screenshots (Optional)
Add screenshots such as Student Dashboard, Department Event Creation, Email Notification Example, Event Report (PDF).
🚀 Future Enhancements

- Event reminder emails before event start
- Student voting system for suggested events
- Export participation lists to Excel/CSV
- Push Notifications for mobile users
- Analytics Dashboard (graphs for participation trends)


👨‍💻 Author

Developed by [Tejas Mahesh Bhide] 🎓
Master of Computer Applications (MCA)

📧 Contact: tejasbhide21@gmail.com
🌐 GitHub:


✨ If you like this project, don’t forget to ⭐ star this repository and contribute!
