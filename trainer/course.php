<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$course_id) {
    header('Location: courses.php');
    exit();
}

// Get course details
$course = getCourseById($course_id);
if (!$course) {
    header('Location: courses.php');
    exit();
}

// Check if user teaches this course
$courseTrainer = getProgramById($course['program_id']);
if ($courseTrainer['trainer_id'] != $user['id']) {
    header('Location: courses.php');
    exit();
}

// Get units for this course
$units = getUnitsByProgram($course['program_id']);

// Get materials for each unit
$unitMaterials = [];
foreach ($units as $unit) {
    $unitMaterials[$unit['id']] = getMaterialsByUnit($unit['id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php" class="active">My Courses</a></li>
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
                    <li><a href="forum.php">Discussion Forum</a></li>
                    <li><a href="ai_assistant.php">Your AI Assistant</a></li>
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
                    <h2><?php echo htmlspecialchars($course['name']); ?></h2>
                    <p><?php echo htmlspecialchars($course['code']); ?> - Year <?php echo $course['year']; ?>, Semester <?php echo $course['semester']; ?></p>
                </div>
                
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <p><?php echo htmlspecialchars($course['description'] ?? 'No description available'); ?></p>
                        <p><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
                    </div>
                    <div>
                        <a href="courses.php" class="btn">Back to Courses</a>
                        <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">Edit Course</a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Course Units</h2>
                    <p>Units for <?php echo htmlspecialchars($course['name']); ?></p>
                </div>
                
                <?php if (empty($units)): ?>
                    <div class="alert">No units available for this course yet.</div>
                <?php else: ?>
                    <?php foreach ($units as $unit): ?>
                    <div class="card" style="margin-bottom: 1.5rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>)
                        </h3>
                        <p><?php echo htmlspecialchars($unit['description'] ?? 'No description available'); ?></p>
                        <p><strong>Year:</strong> <?php echo $unit['year']; ?> | <strong>Semester:</strong> <?php echo $unit['semester']; ?> | <strong>Credits:</strong> <?php echo $unit['credits']; ?></p>
                        
                        <div style="margin-top: 1rem;">
                            <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn">View Unit Details</a>
                            <a href="unit_materials.php?unit_id=<?php echo $unit['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">Manage Materials (<?php echo count($unitMaterials[$unit['id']] ?? []); ?>)</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Course Management</h2>
                    <p>Tools for managing this course</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="schedule_class.php?course_id=<?php echo $course['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎬</div>
                        <div>Schedule Live Class</div>
                    </a>
                    <a href="create_assignment.php?course_id=<?php echo $course['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                        <div>Create Assignment</div>
                    </a>
                    <a href="forum_topic.php?course_id=<?php echo $course['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">💬</div>
                        <div>Create Forum Topic</div>
                    </a>
                    <a href="course_students.php?course_id=<?php echo $course['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                        <div>Manage Students</div>
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
    </style>
</body>
</html>