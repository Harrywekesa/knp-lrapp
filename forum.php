<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Simulate forum topics
$topics = [
    [
        'id' => 1,
        'title' => 'Help with PHP Arrays',
        'author' => 'John Smith',
        'replies' => 12,
        'views' => 45,
        'last_post' => '2 hours ago',
        'category' => 'PHP'
    ],
    [
        'id' => 2,
        'title' => 'Best practices for database design',
        'author' => 'Sarah Johnson',
        'replies' => 8,
        'views' => 32,
        'last_post' => '5 hours ago',
        'category' => 'Database'
    ],
    [
        'id' => 3,
        'title' => 'JavaScript frameworks comparison',
        'author' => 'Michael Brown',
        'replies' => 24,
        'views' => 87,
        'last_post' => '1 day ago',
        'category' => 'JavaScript'
    ],
    [
        'id' => 4,
        'title' => 'How to create responsive layouts?',
        'author' => 'Emily Davis',
        'replies' => 15,
        'views' => 56,
        'last_post' => '1 day ago',
        'category' => 'CSS'
    ]
];

$categories = ['All', 'PHP', 'JavaScript', 'Database', 'CSS', 'HTML'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Forum - <?php echo APP_NAME; ?></title>
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
                    <li><a href="forum.php">Forum</a></li>
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
                    <h2>Discussion Forum</h2>
                    <p>Connect with other students and instructors</p>
                </div>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <select class="form-control" style="width: auto; display: inline-block; margin-right: 0.5rem;">
                            <option>Sort by: Latest Activity</option>
                            <option>Sort by: Most Replies</option>
                            <option>Sort by: Most Views</option>
                        </select>
                        <select class="form-control" style="width: auto; display: inline-block;">
                            <?php foreach ($categories as $category): ?>
                            <option><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button class="btn">New Topic</button>
                    </div>
                </div>
                
                <div style="border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; background: #f8fafc; padding: 0.75rem; font-weight: bold; border-bottom: 1px solid #ddd;">
                        <div>Topic</div>
                        <div style="text-align: center;">Replies</div>
                        <div style="text-align: center;">Views</div>
                        <div style="text-align: center;">Last Post</div>
                    </div>
                    
                    <?php foreach ($topics as $index => $topic): ?>
                    <div style="display: grid; grid-template-columns: 3fr 1fr 1fr 1fr; padding: 1rem; border-bottom: 1px solid #eee; <?php echo $index === count($topics) - 1 ? 'border-bottom: none;' : ''; ?>">
                        <div>
                            <div>
                                <a href="#" style="font-weight: bold; color: var(--primary-color); text-decoration: none;"><?php echo htmlspecialchars($topic['title']); ?></a>
                            </div>
                            <div style="font-size: 0.85rem; color: #666; margin-top: 0.25rem;">
                                by <?php echo htmlspecialchars($topic['author']); ?> in <?php echo htmlspecialchars($topic['category']); ?>
                            </div>
                        </div>
                        <div style="text-align: center; display: flex; align-items: center; justify-content: center;">
                            <?php echo $topic['replies']; ?>
                        </div>
                        <div style="text-align: center; display: flex; align-items: center; justify-content: center;">
                            <?php echo $topic['views']; ?>
                        </div>
                        <div style="text-align: center; display: flex; align-items: center; justify-content: center; color: #666;">
                            <?php echo $topic['last_post']; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
                    <div class="pagination">
                        <a href="#" class="btn" style="padding: 0.5rem 1rem;">Previous</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.25rem;">1</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.25rem;">2</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.25rem;">3</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.25rem;">...</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.25rem;">10</a>
                        <a href="#" class="btn" style="padding: 0.5rem 1rem;">Next</a>
                    </div>
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
        .pagination a {
            margin: 0 0.1rem;
        }
        
        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }
    </style>
</body>
</html>