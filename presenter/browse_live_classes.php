<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all classes for trainer's courses
$allClasses = [];
foreach ($courses as $course) {
    $courseClasses = getClassesByCourse($course['id']);
    foreach ($courseClasses as $class) {
        $class['course_name'] = $course['name'];
        $allClasses[] = $class;
    }
}

// Separate upcoming and past classes
$upcomingClasses = [];
$pastClasses = [];

foreach ($allClasses as $class) {
    if (strtotime($class['start_time']) > time()) {
        $upcomingClasses[] = $class;
    } else {
        $pastClasses[] = $class;
    }
}

// Sort classes by date
usort($upcomingClasses, function($a, $b) {
    return strtotime($a['start_time']) - strtotime($b['start_time']);
});

usort($pastClasses, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Live Classes - <?php echo APP_NAME; ?></title>
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
                    <h2>Browse Live Classes</h2>
                    <p>Access all your scheduled and past live sessions</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search classes by title or course...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Classes (<?php echo count($allClasses); ?>)</button>
                        <button class="tab-button" data-tab="upcoming">Upcoming (<?php echo count($upcomingClasses); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Classes (<?php echo count($pastClasses); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($allClasses)): ?>
                                <div class="alert">No classes found.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($allClasses as $class): ?>
                                    <div class="class-card">
                                        <div style="background: <?php echo (strtotime($class['start_time']) > time()) ? 'var(--primary-color)' : '#ddd'; ?>; color: <?php echo (strtotime($class['start_time']) > time()) ? 'white' : '#333'; ?>; padding: 1rem; border-radius: 4px 4px 0 0;">
                                            <h3 style="margin: 0;"><?php echo htmlspecialchars($class['title']); ?></h3>
                                            <p style="margin: 0.5rem 0 0;">
                                                <?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?>
                                            </p>
                                        </div>
                                        <div class="class-card-content">
                                            <p><strong>Course:</strong> <?php echo htmlspecialchars($class['course_name']); ?></p>
                                            <p><?php echo htmlspecialchars(substr($class['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                                <span><?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?></span>
                                                <div>
                                                    <?php if (strtotime($class['start_time']) > time()): ?>
                                                        <span class="badge badge-success">Upcoming</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Completed</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div style="margin-top: 1rem;">
                                                <a href="class.php?id=<?php echo $class['id']; ?>" class="btn btn-block">
                                                    <?php echo (strtotime($class['start_time']) > time()) ? 'Manage Class' : 'View Details'; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="upcoming" class="tab-pane">
                            <?php if (empty($upcomingClasses)): ?>
                                <div class="alert">No upcoming classes scheduled.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($upcomingClasses as $class): ?>
                                    <div class="class-card">
                                        <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                            <h3 style="margin: 0;"><?php echo htmlspecialchars($class['title']); ?></h3>
                                            <p style="margin: 0.5rem 0 0;">
                                                <?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?>
                                            </p>
                                        </div>
                                        <div class="class-card-content">
                                            <p><strong>Course:</strong> <?php echo htmlspecialchars($class['course_name']); ?></p>
                                            <p><?php echo htmlspecialchars(substr($class['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                                <span><?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?></span>
                                                <span class="badge badge-success">Upcoming</span>
                                            </div>
                                            <div style="margin-top: 1rem;">
                                                <a href="class.php?id=<?php echo $class['id']; ?>" class="btn btn-block">Manage Class</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="past" class="tab-pane">
                            <?php if (empty($pastClasses)): ?>
                                <div class="alert">No past classes found.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($pastClasses as $class): ?>
                                    <div class="class-card">
                                        <div style="background: #ddd; padding: 1rem; border-radius: 4px 4px 0 0;">
                                            <h3 style="margin: 0;"><?php echo htmlspecialchars($class['title']); ?></h3>
                                            <p style="margin: 0.5rem 0 0; color: #666;">
                                                <?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?>
                                            </p>
                                        </div>
                                        <div class="class-card-content">
                                            <p><strong>Course:</strong> <?php echo htmlspecialchars($class['course_name']); ?></p>
                                            <p><?php echo htmlspecialchars(substr($class['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                                <span>Completed</span>
                                                <span class="badge badge-secondary">Past</span>
                                            </div>
                                            <div style="margin-top: 1rem;">
                                                <a href="class.php?id=<?php echo $class['id']; ?>" class="btn btn-block">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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
        
        .tabs {
            margin-top: 1.5rem;
        }
        
        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-button {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #666;
        }
        
        .tab-button.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .tab-pane {
            display: none;
            padding: 1.5rem 0;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .class-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .class-card:hover {
            transform: translateY(-5px);
        }
        
        .class-card-content {
            padding: 1rem;
        }
        
        .class-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>