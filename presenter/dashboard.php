<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

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

// Get recent assignments
$recentAssignments = [];
foreach ($courses as $course) {
    $courseAssignments = getAssignmentsByCourse($course['id']);
    $recentAssignments = array_merge($recentAssignments, $courseAssignments);
}

// Sort assignments by due date and limit to 5 most recent
usort($recentAssignments, function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});
$recentAssignments = array_slice($recentAssignments, 0, 5);

// Get forum topics for trainer's courses
$forumTopics = [];
foreach ($courses as $course) {
    $courseTopics = getForumTopics($course['id']);
    foreach ($courseTopics as $topic) {
        $topic['course_name'] = $course['name'];
        $forumTopics[] = $topic;
    }
}

// Sort topics by date and limit to 5 most recent
usort($forumTopics, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
$forumTopics = array_slice($forumTopics, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presenter Dashboard - <?php echo APP_NAME; ?></title>
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
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p>Your Presenter Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo count($courses); ?></div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎬</div>
                        <div class="stat-number"><?php echo count($recentClasses); ?></div>
                        <div class="stat-label">Classes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number"><?php echo count($recentAssignments); ?></div>
                        <div class="stat-label">Assignments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">💬</div>
                        <div class="stat-number"><?php echo count($forumTopics); ?></div>
                        <div class="stat-label">Forum Topics</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>My Courses</h2>
                    <p>Courses you are teaching</p>
                </div>
                
                <?php if (empty($courses)): ?>
                    <div class="alert">You haven't created any courses yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="courses.php" class="btn">Create Your First Course</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach (array_slice($courses, 0, 3) as $course): ?>
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
                    
                    <?php if (empty($recentClasses)): ?>
                        <div class="alert">No upcoming classes scheduled.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentClasses as $class): ?>
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
                                    <?php 
                                    $course = getProgramById($class['program_id']);
                                    echo htmlspecialchars($course['name'] ?? 'N/A');
                                    ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('g:i A', strtotime($class['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($class['end_time'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="live_class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Manage Class</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Recent Assignments</h2>
                        <p>Assignments you've created</p>
                    </div>
                    
                    <?php if (empty($recentAssignments)): ?>
                        <div class="alert">No assignments created yet.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentAssignments as $assignment): ?>
                            <div class="assignment-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($assignment['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j', strtotime($assignment['due_date'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php 
                                    $course = getProgramById($assignment['program_id']);
                                    echo htmlspecialchars($course['name'] ?? 'N/A');
                                    ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    Max Points: <?php echo $assignment['max_points']; ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Submissions</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Discussion Forum Topics</h2>
                    <p>Recent discussions in your courses</p>
                </div>
                
                <?php if (empty($forumTopics)): ?>
                    <div class="alert">No forum topics yet.</div>
                <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($forumTopics as $topic): ?>
                        <div class="forum-topic" style="padding: 1rem; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 1rem;">
                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars($topic['title']); ?>
                                    </a>
                                </h4>
                                <span style="color: #666; font-size: 0.85rem;">
                                    <?php echo date('M j', strtotime($topic['created_at'])); ?>
                                </span>
                            </div>
                            <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($topic['course_name']); ?>
                            </p>
                            <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                By <?php echo htmlspecialchars($topic['author']); ?>
                            </p>
                            <div style="margin-top: 0.5rem;">
                                <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Discussion</a>
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
        
        .forum-topic:hover, .class-item:hover, .assignment-item:hover {
            background-color: #f8fafc;
        }
    </style>
</body>
</html>