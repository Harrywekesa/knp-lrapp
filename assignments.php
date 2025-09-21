<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Simulate assignments
$assignments = [
    [
        'id' => 1,
        'title' => 'PHP Basics Exercises',
        'course' => 'Web Development',
        'due_date' => '2023-10-20',
        'status' => 'pending',
        'submitted' => false,
        'grade' => null
    ],
    [
        'id' => 2,
        'title' => 'JavaScript Project',
        'course' => 'Frontend Development',
        'due_date' => '2023-10-25',
        'status' => 'in_progress',
        'submitted' => false,
        'grade' => null
    ],
    [
        'id' => 3,
        'title' => 'Database Design Assignment',
        'course' => 'Database Management',
        'due_date' => '2023-10-16',
        'status' => 'submitted',
        'submitted' => true,
        'grade' => 'A-'
    ],
    [
        'id' => 4,
        'title' => 'HTML/CSS Portfolio',
        'course' => 'Web Design',
        'due_date' => '2023-11-05',
        'status' => 'not_started',
        'submitted' => false,
        'grade' => null
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - <?php echo APP_NAME; ?></title>
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
                    <?php if ($role === 'trainee'): ?>
                        <li><a href="ebooks.php">E-Books</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                        <li><a href="assignments.php">Assignments</a></li>
                    <?php elseif ($role === 'trainer'): ?>
                        <li><a href="courses.php">My Courses</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                        <li><a href="assignments.php">Assignments</a></li>
                    <?php elseif ($role === 'exam_officer'): ?>
                        <li><a href="attendance.php">Attendance</a></li>
                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Assignments</h2>
                    <p>Manage your course assignments</p>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div>
                        <select class="form-control" style="width: auto; display: inline-block; margin-right: 0.5rem;">
                            <option>All Courses</option>
                            <option>Web Development</option>
                            <option>Frontend Development</option>
                            <option>Database Management</option>
                        </select>
                        <select class="form-control" style="width: auto; display: inline-block;">
                            <option>All Statuses</option>
                            <option>Pending</option>
                            <option>In Progress</option>
                            <option>Submitted</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" class="form-control" placeholder="Search assignments..." style="width: 200px; display: inline-block;">
                    </div>
                </div>
                
                <div class="grid">
                    <?php foreach ($assignments as $assignment): ?>
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                                <p style="color: #666; margin: 0.25rem 0;"><?php echo htmlspecialchars($assignment['course']); ?></p>
                            </div>
                            <div>
                                <?php if ($assignment['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif ($assignment['status'] === 'in_progress'): ?>
                                    <span class="badge badge-primary">In Progress</span>
                                <?php elseif ($assignment['status'] === 'submitted'): ?>
                                    <span class="badge badge-success">Submitted</span>
                                <?php else: ?>
                                    <span class="badge">Not Started</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div style="margin: 1rem 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Due Date:</span>
                                <span><?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></span>
                            </div>
                            
                            <?php if ($assignment['submitted'] && $assignment['grade']): ?>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Grade:</span>
                                <span style="font-weight: bold; color: #10b981;"><?php echo $assignment['grade']; ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="progress-bar" style="margin: 1rem 0;">
                                <div class="progress-fill" style="width: <?php 
                                    echo $assignment['status'] === 'not_started' ? '0%' : 
                                        ($assignment['status'] === 'pending' ? '25%' : 
                                        ($assignment['status'] === 'in_progress' ? '50%' : '100%')); 
                                ?>;"></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem;">
                            <?php if ($assignment['status'] === 'submitted'): ?>
                                <button class="btn" style="flex: 1;">View Feedback</button>
                            <?php else: ?>
                                <button class="btn" style="flex: 1;">Start Assignment</button>
                                <?php if ($assignment['status'] === 'in_progress'): ?>
                                    <button class="btn btn-secondary" style="flex: 1;">Submit</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
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