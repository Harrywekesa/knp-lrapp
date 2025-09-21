<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed(['trainee', 'trainer']);

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Classes - <?php echo APP_NAME; ?></title>
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
                    <?php if ($role === 'trainee'): ?>
                        <li><a href="ebooks.php">E-Books</a></li>
                    <?php endif; ?>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Live Classes</h2>
                    <p>Join your upcoming live sessions</p>
                </div>
                
                <div class="card" style="margin-bottom: 1.5rem;">
                    <h3 style="margin-bottom: 1rem;">QR Check-in</h3>
                    <button id="qr-scanner" class="btn btn-accent">Scan QR Code for Attendance</button>
                    <div id="attendance-status" class="alert" style="margin-top: 1rem; display: none;"></div>
                </div>
                
                <div class="card">
                    <h3 style="margin-bottom: 1rem;">Upcoming Sessions</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Session</th>
                                <th>Course</th>
                                <th>Date & Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Introduction to PHP</td>
                                <td>Web Development Basics</td>
                                <td>Oct 15, 2023 10:00 AM</td>
                                <td><button id="join-class-btn" class="btn">Join Class</button></td>
                            </tr>
                            <tr>
                                <td>Advanced JavaScript</td>
                                <td>Frontend Development</td>
                                <td>Oct 17, 2023 2:00 PM</td>
                                <td><button class="btn">Join Class</button></td>
                            </tr>
                            <tr>
                                <td>Database Design</td>
                                <td>Database Management</td>
                                <td>Oct 20, 2023 11:00 AM</td>
                                <td><button class="btn">Join Class</button></td>
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