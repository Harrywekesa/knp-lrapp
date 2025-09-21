<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get report data
global $pdo;

// User registration statistics
$userStats = [];
$stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count FROM users GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
while ($row = $stmt->fetch()) {
    $userStats[] = $row;
}

// Course enrollment statistics
$courseStats = [];
$stmt = $pdo->query("SELECT c.title, COUNT(ce.user_id) as enrollments FROM courses c LEFT JOIN (SELECT DISTINCT course_id, user_id FROM classes cl JOIN sessions s ON cl.id = s.class_id JOIN attendance_records ar ON s.id = ar.session_id) ce ON c.id = ce.course_id GROUP BY c.id ORDER BY enrollments DESC LIMIT 10");
while ($row = $stmt->fetch()) {
    $courseStats[] = $row;
}

// Revenue statistics
$revenueStats = [];
$stmt = $pdo->query("SELECT DATE(created_at) as date, SUM(amount) as revenue FROM payments WHERE status = 'completed' GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
while ($row = $stmt->fetch()) {
    $revenueStats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="programs.php">Manage Programs</a></li>
                    <li><a href="reports.php" class="active">Reports</a></li>
                    <li><a href="trainers.php">Approve Trainers</a></li>
                    <li><a href="sales.php">Commerce</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Analytics & Reports</h2>
                    <p>System performance and usage statistics</p>
                </div>
                
                <div class="grid">
                    <div class="card">
                        <h3>User Registration Trends</h3>
                        <div style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 4px; margin: 1rem 0;">
                            <div>
                                <div style="text-align: center; font-size: 3rem; color: var(--primary-color);">📈</div>
                                <p style="text-align: center; margin-top: 1rem;">Chart visualization would appear here</p>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>New Users</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($userStats, 0, 5) as $stat): ?>
                                <tr>
                                    <td><?php echo $stat['date']; ?></td>
                                    <td><?php echo $stat['count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card">
                        <h3>Course Enrollments</h3>
                        <div style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 4px; margin: 1rem 0;">
                            <div>
                                <div style="text-align: center; font-size: 3rem; color: var(--secondary-color);">📊</div>
                                <p style="text-align: center; margin-top: 1rem;">Chart visualization would appear here</p>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Enrollments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courseStats as $stat): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($stat['title']); ?></td>
                                    <td><?php echo $stat['enrollments']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="card">
                        <h3>Revenue Trends</h3>
                        <div style="height: 200px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 4px; margin: 1rem 0;">
                            <div>
                                <div style="text-align: center; font-size: 3rem; color: var(--accent-color);">💰</div>
                                <p style="text-align: center; margin-top: 1rem;">Chart visualization would appear here</p>
                            </div>
                        </div>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Revenue (KES)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($revenueStats, 0, 5) as $stat): ?>
                                <tr>
                                    <td><?php echo $stat['date']; ?></td>
                                    <td><?php echo number_format($stat['revenue'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 2rem;">
                    <h3>Export Reports</h3>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
                        <button class="btn">Export User Data (CSV)</button>
                        <button class="btn">Export Course Data (CSV)</button>
                        <button class="btn">Export Payment Data (CSV)</button>
                        <button class="btn">Generate PDF Report</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <style>
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
    </style>
</body>
</html>