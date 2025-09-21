<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get enrolled courses for this trainee
$enrollments = getUserEnrollments($user['id']);

// Get courses from enrollments
$enrolledCourses = [];
foreach ($enrollments as $enrollment) {
    $course = getCourseById($enrollment['program_id']);
    if ($course) {
        $enrolledCourses[] = $course;
    }
}

// Get recent materials (ebooks)
$ebooks = getAllEbooks();

// Get upcoming classes for enrolled courses
$upcomingClasses = [];
foreach ($enrolledCourses as $course) {
    $courseClasses = getClassesByCourse($course['id']);
    foreach ($courseClasses as $class) {
        // Only include future classes
        if (strtotime($class['start_time']) > time()) {
            $class['course_title'] = $course['name'];
            $upcomingClasses[] = $class;
        }
    }
}

// Sort classes by date and limit to 5 most recent
usort($upcomingClasses, function($a, $b) {
    return strtotime($a['start_time']) - strtotime($b['start_time']);
});
$upcomingClasses = array_slice($upcomingClasses, 0, 5);

// Get recent assignments
$recentAssignments = [];
foreach ($enrolledCourses as $course) {
    $courseAssignments = getAssignmentsByCourse($course['id']);
    $recentAssignments = array_merge($recentAssignments, $courseAssignments);
}

// Sort assignments by due date and limit to 5 most recent
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
                        <div class="stat-number"><?php echo count($enrolledCourses); ?></div>
                        <div class="stat-label">Enrolled Courses</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📖</div>
                        <div class="stat-number"><?php echo count($ebooks); ?></div>
                        <div class="stat-label">Available E-Books</div>
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
                    <h2>My Courses</h2>
                    <p>Courses you are currently enrolled in</p>
                </div>
                
                <?php if (empty($enrolledCourses)): ?>
                    <div class="alert">You haven't enrolled in any courses yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="courses.php" class="btn">Browse Available Courses</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($enrolledCourses, 0, 3) as $course): ?>
                        <div class="course-card">
                            <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                <?php echo htmlspecialchars($course['code'] ?? 'COURSE'); ?>
                            </div>
                            <div class="course-card-content">
                                <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span><?php echo $course['duration']; ?> years</span>
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="courses.php" class="btn btn-secondary">View All Courses</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
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
                                    <?php echo htmlspecialchars($class['course_title']); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="live_class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Join Class</a>
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
                                    Due: <?php echo date('M j, Y', strtotime($assignment['due_date'])); ?>
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
            
            <div class="card">
                <div class="card-header">
                    <h2>Recommended E-Books</h2>
                    <p>Suggested reading materials</p>
                </div>
                
                <?php if (empty($ebooks)): ?>
                    <div class="alert">No e-books available at this time.</div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($ebooks, 0, 4) as $ebook): ?>
                        <div class="ebook-card">
                            <div style="position: relative;">
                                <?php if ($ebook['cover_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px 4px 0 0;">
                                <?php else: ?>
                                    <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                        📖
                                    </div>
                                <?php endif; ?>
                                <?php if ($ebook['price'] > 0): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        KES <?php echo number_format($ebook['price'], 2); ?>
                                    </div>
                                <?php else: ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(16, 185, 129, 0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        FREE
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ebook-card-content">
                                <h3><?php echo htmlspecialchars($ebook['title']); ?></h3>
                                <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author'] ?? 'Unknown'); ?></p>
                                <p><?php echo htmlspecialchars(substr($ebook['description'] ?? 'No description', 0, 80)) . '...'; ?></p>
                                <div style="margin-top: 1rem;">
                                    <a href="ebook.php?id=<?php echo $ebook['id']; ?>" class="btn btn-block">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
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
        
        .notification-item:hover, .class-item:hover, .assignment-item:hover {
            background-color: #f8fafc;
        }
    </style>
</body>
</html>