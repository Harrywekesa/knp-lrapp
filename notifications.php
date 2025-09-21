<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Simulate notifications
$notifications = [
    [
        'id' => 1,
        'title' => 'New Course Available',
        'message' => 'The "Advanced JavaScript" course is now available for enrollment.',
        'type' => 'info',
        'time' => '2 hours ago',
        'read' => false
    ],
    [
        'id' => 2,
        'title' => 'Live Class Reminder',
        'message' => 'Your "PHP Basics" class starts in 30 minutes.',
        'type' => 'warning',
        'time' => '1 day ago',
        'read' => true
    ],
    [
        'id' => 3,
        'title' => 'Payment Successful',
        'message' => 'Your purchase of "Complete Guide to PHP Development" was successful.',
        'type' => 'success',
        'time' => '2 days ago',
        'read' => true
    ],
    [
        'id' => 4,
        'title' => 'Assignment Due',
        'message' => 'The assignment for "Database Design" is due tomorrow.',
        'type' => 'danger',
        'time' => '3 days ago',
        'read' => false
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo APP_NAME; ?></title>
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
                    <?php elseif ($role === 'trainer'): ?>
                        <li><a href="courses.php">My Courses</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                    <?php elseif ($role === 'exam_officer'): ?>
                        <li><a href="attendance.php">Attendance</a></li>
                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="notifications.php">Notifications</a></li>
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
                    <h2>Notifications</h2>
                    <p>Stay updated with important announcements and reminders</p>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <button class="btn">Mark All as Read</button>
                        <button class="btn btn-secondary" style="margin-left: 0.5rem;">Clear All</button>
                    </div>
                    <div>
                        <select class="form-control" style="width: auto; display: inline-block;">
                            <option>All Notifications</option>
                            <option>Unread Only</option>
                            <option>Read Only</option>
                        </select>
                    </div>
                </div>
                
                <div style="border: 1px solid #ddd; border-radius: 4px;">
                    <?php foreach ($notifications as $index => $notification): ?>
                    <div class="notification-item <?php echo !$notification['read'] ? 'unread' : ''; ?>" 
                         style="padding: 1rem; border-bottom: 1px solid #eee; <?php echo $index === count($notifications) - 1 ? 'border-bottom: none;' : ''; ?>">
                        <div style="display: flex; align-items: flex-start;">
                            <div style="margin-right: 1rem; margin-top: 0.25rem;">
                                <?php if ($notification['type'] === 'info'): ?>
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; color: white;">ℹ️</div>
                                <?php elseif ($notification['type'] === 'warning'): ?>
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #f59e0b; display: flex; align-items: center; justify-content: center; color: white;">⚠️</div>
                                <?php elseif ($notification['type'] === 'success'): ?>
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #10b981; display: flex; align-items: center; justify-content: center; color: white;">✓</div>
                                <?php elseif ($notification['type'] === 'danger'): ?>
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; color: white;">✕</div>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between;">
                                    <h4 style="margin: 0; <?php echo !$notification['read'] ? 'font-weight: bold;' : ''; ?>">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h4>
                                    <span style="color: #666; font-size: 0.85rem;"><?php echo $notification['time']; ?></span>
                                </div>
                                <p style="margin: 0.5rem 0 0; color: #555;"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <div style="margin-top: 0.5rem;">
                                    <button class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</button>
                                    <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Mark as Read</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($notifications)): ?>
                    <div style="padding: 2rem; text-align: center; color: #666;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🔔</div>
                        <h3>No notifications</h3>
                        <p>You're all caught up!</p>
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
    <style>
        .notification-item.unread {
            background-color: #f0f9ff;
        }
    </style>
</body>
</html>