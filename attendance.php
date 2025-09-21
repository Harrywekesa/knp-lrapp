<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Attendance Records</h2>
                    <p>View and manage student attendance</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search by student name or course...">
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Smith</td>
                            <td>Web Development</td>
                            <td>PHP Basics</td>
                            <td>Oct 10, 2023</td>
                            <td><span style="color: green;">Present</span></td>
                        </tr>
                        <tr>
                            <td>Sarah Johnson</td>
                            <td>Database Design</td>
                            <td>SQL Queries</td>
                            <td>Oct 10, 2023</td>
                            <td><span style="color: green;">Present</span></td>
                        </tr>
                        <tr>
                            <td>Michael Brown</td>
                            <td>JavaScript Fundamentals</td>
                            <td>DOM Manipulation</td>
                            <td>Oct 10, 2023</td>
                            <td><span style="color: red;">Absent</span></td>
                        </tr>
                        <tr>
                            <td>Emily Davis</td>
                            <td>Web Development</td>
                            <td>PHP Basics</td>
                            <td>Oct 10, 2023</td>
                            <td><span style="color: green;">Present</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>