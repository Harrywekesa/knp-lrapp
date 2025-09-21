<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get class ID from URL
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$class_id) {
    header('Location: live_classes.php');
    exit();
}

// Get class details
$class = getClassById($class_id);
if (!$class) {
    header('Location: live_classes.php');
    exit();
}

// Get unit details
$unit = getUnitById($class['unit_id']);
if (!$unit) {
    header('Location: live_classes.php');
    exit();
}

// Get course details
$course = getProgramById($unit['program_id']);
if (!$course) {
    header('Location: live_classes.php');
    exit();
}

// Check if user teaches this course
if ($course['trainer_id'] != $user['id']) {
    header('Location: live_classes.php');
    exit();
}

// Get attendance records for this class
global $pdo;
$stmt = $pdo->prepare("SELECT ar.*, u.name as user_name FROM attendance_records ar JOIN users u ON ar.user_id = u.id WHERE ar.session_id = ? ORDER BY ar.joined_at DESC");
$stmt->execute([$class['id']]);
$attendanceRecords = $stmt->fetchAll();

// Get enrolled students for this course
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN enrollments e ON u.id = e.user_id WHERE e.program_id = ? AND e.status = 'active'");
$stmt->execute([$course['id']]);
$enrolledStudents = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($class['title']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="courses.php">My Courses</a></li>
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php" class="active">Live Classes</a></li>
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
                    <h2><?php echo htmlspecialchars($class['title']); ?></h2>
                    <p>Live Class Session</p>
                </div>
                
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                        <p><strong>Unit:</strong> <?php echo htmlspecialchars($unit['name']); ?></p>
                        <p><strong>Date & Time:</strong> <?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($class['description'] ?? 'No description available'); ?></p>
                        <?php if ($class['meeting_link']): ?>
                            <p><strong>Meeting Link:</strong> <a href="<?php echo htmlspecialchars($class['meeting_link']); ?>" target="_blank" style="color: var(--primary-color);"><?php echo htmlspecialchars($class['meeting_link']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <a href="live_classes.php" class="btn">Back to Live Classes</a>
                        <a href="edit_class.php?id=<?php echo $class['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">Edit Class</a>
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem; text-align: center;">
                    <?php if (strtotime($class['start_time']) > time()): ?>
                        <div class="alert alert-info">
                            <strong>Upcoming Class</strong> - This class is scheduled to start on <?php echo date('M j, Y \a\t g:i A', strtotime($class['start_time'])); ?>
                        </div>
                        <button class="btn btn-accent" style="padding: 0.75rem 2rem; font-size: 1.1rem;">Start Class Now</button>
                    <?php elseif (strtotime($class['end_time']) > time()): ?>
                        <div class="alert alert-success">
                            <strong>Class in Progress</strong> - This class is currently running
                        </div>
                        <button class="btn btn-danger" style="padding: 0.75rem 2rem; font-size: 1.1rem;">End Class Now</button>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <strong>Class Completed</strong> - This class ended on <?php echo date('M j, Y \a\t g:i A', strtotime($class['end_time'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h2>Attendance</h2>
                        <p>Students attending this class</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem; display: flex; justify-content: space-between;">
                        <div>
                            <strong>Students Present:</strong> <?php echo count(array_filter($attendanceRecords, function($record) { return $record['status'] === 'present'; })); ?>
                        </div>
                        <div>
                            <strong>Total Enrolled:</strong> <?php echo count($enrolledStudents); ?>
                        </div>
                    </div>
                    
                    <?php if (empty($attendanceRecords)): ?>
                        <div class="alert">No attendance records yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $record): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($record['user_name']); ?></td>
                                        <td>
                                            <?php if ($record['status'] === 'present'): ?>
                                                <span class="badge badge-success">Present</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $record['joined_at'] ? date('g:i A', strtotime($record['joined_at'])) : '-'; ?></td>
                                        <td><?php echo $record['left_at'] ? date('g:i A', strtotime($record['left_at'])) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Class Resources</h2>
                        <p>Materials and resources for this class</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <a href="upload_material.php?class_id=<?php echo $class['id']; ?>" class="btn">Upload Class Material</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Resource</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Class Presentation</td>
                                    <td>Presentation</td>
                                    <td>
                                        <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                        <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Class Recording</td>
                                    <td>Video</td>
                                    <td>
                                        <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                        <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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