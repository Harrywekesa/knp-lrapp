<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Get courses for this trainer
$courses = getCoursesByTrainer($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - <?php echo APP_NAME; ?></title>
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
                    <li><a href="courses.php">My Courses</a></li>
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
                    <h2>My Courses</h2>
                    <p>Manage your courses and classes</p>
                </div>
                
                <button class="btn" style="margin-bottom: 1.5rem;">Create New Course</button>
                
                <div class="grid">
                    <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                            Course Image
                        </div>
                        <div class="course-card-content">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                            <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($course['created_at'])); ?></p>
                            <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                <button class="btn" style="flex: 1;">Edit</button>
                                <button class="btn btn-secondary" style="flex: 1;">Classes</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($courses)): ?>
                    <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                        <h3>You haven't created any courses yet</h3>
                        <p style="margin: 1rem 0;">Create your first course to get started</p>
                        <button class="btn">Create New Course</button>
                    </div>
                    <?php endif; ?>
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