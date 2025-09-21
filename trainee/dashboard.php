<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get enrolled programs
$enrolledPrograms = [];
foreach ($enrollments as $enrollment) {
    $program = getProgramById($enrollment['program_id']);
    if ($program) {
        $enrolledPrograms[] = $program;
    }
}

// Get registered units
$registeredUnits = [];
foreach ($enrollments as $enrollment) {
    $units = getRegisteredUnits($enrollment['id']);
    $registeredUnits = array_merge($registeredUnits, $units);
}

// Get recent materials
$recentMaterials = [];
foreach ($registeredUnits as $unit) {
    $unitMaterials = getMaterialsByUnit($unit['unit_id']);
    $recentMaterials = array_merge($recentMaterials, $unitMaterials);
}

// Sort by date and limit to 5 most recent
usort($recentMaterials, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$recentMaterials = array_slice($recentMaterials, 0, 5);

// Get upcoming classes
$upcomingClasses = [];
foreach ($registeredUnits as $unit) {
    $unitClasses = getClassesByUnit($unit['unit_id']);
    foreach ($unitClasses as $class) {
        if (strtotime($class['start_time']) > time()) {
            $class['unit_name'] = $unit['unit_name'];
            $upcomingClasses[] = $class;
        }
    }
}

// Sort by date and limit to 5 most recent
usort($upcomingClasses, function($a, $b) {
    return strtotime($a['start_time']) - strtotime($b['start_time']);
});
$upcomingClasses = array_slice($upcomingClasses, 0, 5);

// Get recent assignments
$recentAssignments = [];
foreach ($registeredUnits as $unit) {
    $unitAssignments = getAssignmentsByUnit($unit['unit_id']);
    $recentAssignments = array_merge($recentAssignments, $unitAssignments);
}

// Sort by due date and limit to 5 most recent
usort($recentAssignments, function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});
$recentAssignments = array_slice($recentAssignments, 0, 5);

// Get unread notifications
$notifications = getUnreadNotifications($user['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainee Dashboard - <?php echo APP_NAME; ?></title>
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
                    <li><a href="courses.php">My Courses</a></li>
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
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p>Your Learning Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo count($enrolledPrograms); ?></div>
                        <div class="stat-label">Enrolled Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📖</div>
                        <div class="stat-number"><?php echo count($registeredUnits); ?></div>
                        <div class="stat-label">Registered Units</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎬</div>
                        <div class="stat-number"><?php echo count($upcomingClasses); ?></div>
                        <div class="stat-label">Upcoming Classes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number"><?php echo count($recentAssignments); ?></div>
                        <div class="stat-label">Active Assignments</div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($notifications)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Recent Notifications</h2>
                    <p>Important updates and announcements</p>
                </div>
                
                <div style="max-height: 200px; overflow-y: auto;">
                    <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                    <div class="notification-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4 style="margin: 0; color: var(--primary-color);">
                                <?php echo htmlspecialchars($notification['title']); ?>
                            </h4>
                            <span style="color: #666; font-size: 0.85rem;">
                                <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <p style="margin: 0.5rem 0 0; color: #555;">
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
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>My Programs</h2>
                    <p>Programs you are currently enrolled in</p>
                </div>
                
                <?php if (empty($enrolledPrograms)): ?>
                    <div class="alert">You haven't enrolled in any programs yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="courses.php" class="btn">Browse Available Programs</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($enrolledPrograms, 0, 3) as $program): ?>
                        <div class="program-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($program['name']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;"><?php echo htmlspecialchars($program['code']); ?></p>
                            </div>
                            <div class="program-card-content">
                                <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <div style="margin-top: 1rem;">
                                    <a href="program.php?id=<?php echo $program['id']; ?>" class="btn btn-block">View Units</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Materials</h2>
                        <p>Latest learning materials</p>
                    </div>
                    
                    <?php if (empty($recentMaterials)): ?>
                        <div class="alert">No recent materials available.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentMaterials as $material): ?>
                            <div class="material-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($material['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php 
                                        switch($material['type']) {
                                            case 'lecture_note': echo 'Lecture Note'; break;
                                            case 'assignment': echo 'Assignment'; break;
                                            case 'video': echo 'Video'; break;
                                            case 'ebook': echo 'E-book'; break;
                                            default: echo 'Resource'; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('M j, Y', strtotime($material['created_at'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="material.php?id=<?php echo $material['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Material</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Upcoming Classes</h2>
                        <p>Your scheduled live sessions</p>
                    </div>
                    
                    <?php if (empty($upcomingClasses)): ?>
                        <div class="alert">No upcoming classes scheduled.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($upcomingClasses as $class): ?>
                            <div class="class-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($class['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j', strtotime($class['start_time'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($class['unit_name'] ?? 'N/A'); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Join Class</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Assignments</h2>
                        <p>Assignments due soon</p>
                    </div>
                    
                    <?php if (empty($recentAssignments)): ?>
                        <div class="alert">No assignments available.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentAssignments as $assignment): ?>
                            <div class="assignment-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($assignment['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php 
                                        $dueDate = strtotime($assignment['due_date']);
                                        $today = time();
                                        $daysUntilDue = ceil(($dueDate - $today) / (60 * 60 * 24));
                                        
                                        if ($daysUntilDue < 0) {
                                            echo "Overdue";
                                        } elseif ($daysUntilDue == 0) {
                                            echo "Due Today";
                                        } elseif ($daysUntilDue == 1) {
                                            echo "Tomorrow";
                                        } else {
                                            echo "In $daysUntilDue days";
                                        }
                                        ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($assignment['unit_name'] ?? 'N/A'); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    Due: <?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    Max Points: <?php echo $assignment['max_points']; ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
        
        .program-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .program-card:hover {
            transform: translateY(-5px);
        }
        
        .program-card-content {
            padding: 1rem;
        }
        
        .program-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .material-item:hover, .class-item:hover, .assignment-item:hover {
            background-color: #f8fafc;
        }
    </style>
</body>
</html>