<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get trainer's courses
$courses = getCoursesByTrainer($user['id']);

// Get statistics
$totalCourses = count($courses);
$totalStudents = 0; // This would need a more complex query in a real implementation

// Get recent classes for trainer's courses
$recentClasses = [];
foreach ($courses as $course) {
    $courseClasses = getClassesByCourse($course['id']);
    $recentClasses = array_merge($recentClasses, $courseClasses);
}

// Sort classes by date and limit to 5 most recent
usort($recentClasses, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});
$recentClasses = array_slice($recentClasses, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
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
                    <li><a href="programs.php">My Programs</a></li>
                    <li><a href="classes.php">Live Classes</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
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
                    <p>Your Trainer Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo $totalCourses; ?></div>
                        <div class="stat-label">Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo $totalStudents; ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎬</div>
                        <div class="stat-number"><?php echo count($recentClasses); ?></div>
                        <div class="stat-label">Upcoming Classes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number">92%</div>
                        <div class="stat-label">Avg. Rating</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>My Programs</h2>
                    <p>Programs you are teaching</p>
                </div>
                
                <?php if (empty($courses)): ?>
                    <div class="alert">You haven't created any programs yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="programs.php" class="btn">Create Your First Course</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($courses, 0, 3) as $course): ?>
                        <div class="course-card">
                            <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                                Course Image
                            </div>
                            <div class="course-card-content">
                                <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <p><strong>Price:</strong> KES <?php echo number_format($course['price'], 2); ?></p>
                                <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                    <a href="programs.php?action=edit&id=<?php echo $course['id']; ?>" class="btn" style="flex: 1;">Edit</a>
                                    <a href="classes.php?course_id=<?php echo $course['id']; ?>" class="btn btn-secondary" style="flex: 1;">Classes</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="programs.php" class="btn btn-secondary">View All Programs</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Upcoming Classes</h2>
                    <p>Your scheduled live sessions</p>
                </div>
                
                <?php if (empty($recentClasses)): ?>
                    <div class="alert">No upcoming classes scheduled.</div>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Class</th>
                                <th>Date & Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentClasses as $class): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $course = getCourseById($class['course_id']);
                                    echo htmlspecialchars($course['title'] ?? 'N/A');
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($class['title']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?></td>
                                <td>
                                    <a href="classes.php?action=edit&id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</a>
                                    <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Start Class</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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
    </style>
</body>
</html>