<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed(['admin', 'exam_officer']);

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
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
                    <?php if ($role === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a href="attendance.php">Attendance</a></li>
                    <?php endif; ?>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Reports & Analytics</h2>
                    <p>Generate and download reports</p>
                </div>
                
                <div class="grid">
                    <div class="card">
                        <h3>Attendance Report</h3>
                        <p>View attendance statistics for courses and sessions</p>
                        <div style="margin: 1rem 0;">
                            <label for="course-select">Select Course:</label>
                            <select id="course-select" class="form-control">
                                <option>All Courses</option>
                                <option>Web Development</option>
                                <option>Database Design</option>
                                <option>JavaScript Fundamentals</option>
                            </select>
                        </div>
                        <button class="btn">View Report</button>
                        <button class="btn btn-secondary" style="margin-left: 0.5rem;">Download PDF</button>
                    </div>
                    
                    <div class="card">
                        <h3>Purchase Report</h3>
                        <p>View e-book purchase statistics</p>
                        <div style="margin: 1rem 0;">
                            <label for="date-range">Date Range:</label>
                            <select id="date-range" class="form-control">
                                <option>Last 7 Days</option>
                                <option>Last 30 Days</option>
                                <option>Last 90 Days</option>
                                <option>Custom Range</option>
                            </select>
                        </div>
                        <button class="btn">View Report</button>
                        <button class="btn btn-secondary" style="margin-left: 0.5rem;">Download PDF</button>
                    </div>
                    
                    <div class="card">
                        <h3>Class Participation</h3>
                        <p>View student participation in live classes</p>
                        <div style="margin: 1rem 0;">
                            <label for="class-select">Select Class:</label>
                            <select id="class-select" class="form-control">
                                <option>All Classes</option>
                                <option>PHP Basics</option>
                                <option>Advanced JavaScript</option>
                                <option>Database Design</option>
                            </select>
                        </div>
                        <button class="btn">View Report</button>
                        <button class="btn btn-secondary" style="margin-left: 0.5rem;">Download PDF</button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Report Preview</h2>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Total Sessions</th>
                                <th>Attended</th>
                                <th>Attendance %</th>
                                <th>Last Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Smith</td>
                                <td>Web Development</td>
                                <td>12</td>
                                <td>10</td>
                                <td>83.3%</td>
                                <td>Oct 15, 2023</td>
                            </tr>
                            <tr>
                                <td>Sarah Johnson</td>
                                <td>Database Design</td>
                                <td>8</td>
                                <td>8</td>
                                <td>100%</td>
                                <td>Oct 15, 2023</td>
                            </tr>
                            <tr>
                                <td>Michael Brown</td>
                                <td>JavaScript Fundamentals</td>
                                <td>10</td>
                                <td>7</td>
                                <td>70%</td>
                                <td>Oct 12, 2023</td>
                            </tr>
                            <tr>
                                <td>Emily Davis</td>
                                <td>Web Development</td>
                                <td>12</td>
                                <td>11</td>
                                <td>91.7%</td>
                                <td>Oct 15, 2023</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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