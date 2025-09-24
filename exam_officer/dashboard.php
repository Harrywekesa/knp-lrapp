<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get statistics
$departments = getAllDepartments();
$programs = getAllPrograms();
$units = getAllUnits();
$students = getAllStudents();
$exams = getAllExams();

// Calculate totals
$totalDepartments = count($departments);
$totalPrograms = count($programs);
$totalUnits = count($units);
$totalStudents = count($students);
$totalExams = count($exams);

// Get recent exams
$recentExams = array_slice($exams, 0, 5);

// Get recent notifications
$notifications = getUnreadNotifications($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Officer Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent-color']; ?>;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="exams.php">Exams</a></li>
                    <li><a href="results.php">Results</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p>Exam Officer Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-number"><?php echo $totalDepartments; ?></div>
                        <div class="stat-label">Departments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-number"><?php echo $totalPrograms; ?></div>
                        <div class="stat-label">Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo $totalUnits; ?></div>
                        <div class="stat-label">Units</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo $totalStudents; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Exams</h3>
                        <p>Upcoming and recent examinations</p>
                    </div>
                    
                    <?php if (empty($recentExams)): ?>
                        <div class="alert">No exams scheduled yet.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentExams as $exam): ?>
                            <div class="exam-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($exam['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($exam['program_name'] ?? 'N/A'); ?>)
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('g:i A', strtotime($exam['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($exam['end_time'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <?php if ($exam['status'] === 'scheduled'): ?>
                                        <span class="badge badge-warning">Scheduled</span>
                                    <?php elseif ($exam['status'] === 'in_progress'): ?>
                                        <span class="badge badge-success">In Progress</span>
                                    <?php elseif ($exam['status'] === 'completed'): ?>
                                        <span class="badge badge-secondary">Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?php echo ucfirst($exam['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <a href="exam.php?id=<?php echo $exam['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Manage Exam</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Notifications</h3>
                        <p>Important updates and alerts</p>
                    </div>
                    
                    <?php if (empty($notifications)): ?>
                        <div class="alert">No unread notifications.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                            <div class="notification-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem; color: var(--primary-color);">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h4>
                                    <span style="color: #666; font-size: 0.85rem;">
                                        <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #555; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                        <button type="submit" name="mark_read" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Mark as Read</button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                    <p>Common exam officer tasks</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="attendance.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                        <div>Attendance</div>
                    </a>
                    <a href="exams.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📝</div>
                        <div>Exams</div>
                    </a>
                    <a href="results.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                        <div>Results</div>
                    </a>
                    <a href="reports.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📈</div>
                        <div>Reports</div>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-dark text-light">
        <div class="container">
            <div style="border-top: 1px solid #444; padding: 1rem 0; text-align: center; color: #aaa;">
                <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1 0 auto;
        }
        
        footer {
            flex-shrink: 0;
        }
        
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
        
        .exam-item:hover, .notification-item:hover {
            background-color: #f8fafc;
        }
    </style>
</body>
</html>