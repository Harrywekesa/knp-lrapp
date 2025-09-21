<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get enrolled courses
$enrollments = getUserEnrollments($user['id']);
$enrolledCourses = [];
foreach ($enrollments as $enrollment) {
    $course = getProgramById($enrollment['program_id']);
    if ($course) {
        $course['enrollment_id'] = $enrollment['id'];
        $enrolledCourses[] = $course;
    }
}

// Get registered units for each enrollment
$courseUnits = [];
foreach ($enrolledCourses as $course) {
    $registeredUnits = getRegisteredUnits($course['enrollment_id']);
    $courseUnits[$course['id']] = $registeredUnits;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - <?php echo APP_NAME; ?></title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php" class="active">My Courses</a></li>
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
                    <li><a href="forum.php">Discussion Forum</a></li>
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
                    <h2>My Courses</h2>
                    <p>Courses you are currently enrolled in</p>
                </div>
                
                <?php if (empty($enrolledCourses)): ?>
                    <div class="alert">You haven't enrolled in any courses yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="browse_courses.php" class="btn">Browse Available Courses</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="margin: 0; color: var(--primary-color);">
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </h3>
                                <p style="margin: 0.25rem 0 0; color: #666;">
                                    <?php echo htmlspecialchars($course['code']); ?> - 
                                    <?php echo $course['duration']; ?> years
                                </p>
                            </div>
                            <div>
                                <span class="badge badge-success">Active</span>
                            </div>
                        </div>
                        
                        <p><?php echo htmlspecialchars($course['description'] ?? 'No description available'); ?></p>
                        
                        <div style="margin: 1rem 0;">
                            <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);">Registered Units</h4>
                            
                            <?php if (empty($courseUnits[$course['id']])): ?>
                                <div class="alert">No units registered for this course yet.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($courseUnits[$course['id']] as $unitReg): ?>
                                        <?php 
                                        $unit = getUnitById($unitReg['unit_id']);
                                        if ($unit):
                                        ?>
                                        <div class="unit-card">
                                            <div style="background: #ddd; height: 100px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                                <?php echo htmlspecialchars($unit['code']); ?>
                                            </div>
                                            <div class="unit-card-content">
                                                <h4><?php echo htmlspecialchars($unit['name']); ?></h4>
                                                <p>Year <?php echo $unit['year']; ?>, Semester <?php echo $unit['semester']; ?></p>
                                                <p><?php echo $unit['credits']; ?> credits</p>
                                                <div style="margin-top: 1rem;">
                                                    <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn btn-block">View Materials</a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div style="text-align: center; margin-top: 1rem;">
                            <a href="course_details.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary">Course Details</a>
                            <a href="register_units.php?course_id=<?php echo $course['id']; ?>" class="btn" style="margin-left: 1rem;">Register Units</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
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