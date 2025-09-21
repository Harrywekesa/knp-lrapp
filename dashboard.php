<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Redirect based on user role
switch ($role) {
    case 'admin':
        header('Location: admin/dashboard.php');
        exit();
    case 'trainer':
        header('Location: trainer/dashboard.php');
        exit();
    case 'exam_officer':
        header('Location: exam_officer/dashboard.php');
        exit();
    case 'presenter':
        header('Location: presenter/dashboard.php');
        exit();
    case 'trainee':
        header('Location: trainee/dashboard.php');
        exit();
    default:
        header('Location: dashboard.php');
        exit();
}

// Get ebooks
$ebooks = getAllEbooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Welcome, <?php echo $user['name']; ?>!</h2>
                    <p>Your <?php echo ucfirst($role); ?> Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo count($courses); ?></div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📖</div>
                        <div class="stat-number"><?php echo count($ebooks); ?></div>
                        <div class="stat-label">E-Books</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number">142</div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number">87%</div>
                        <div class="stat-label">Attendance</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent Courses</h2>
                </div>
                <div class="grid">
                    <?php foreach (array_slice($courses, 0, 3) as $course): ?>
                    <div class="course-card">
                        <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                            Course Image
                        </div>
                        <div class="course-card-content">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                            <p><strong>Trainer:</strong> <?php echo htmlspecialchars($course['trainer_name'] ?? 'N/A'); ?></p>
                            <a href="#" class="btn">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Recent E-Books</h2>
                </div>
                <div class="grid">
                    <?php foreach (array_slice($ebooks, 0, 3) as $ebook): ?>
                    <div class="ebook-card">
                        <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                            E-Book Cover
                        </div>
                        <div class="ebook-card-content">
                            <h3><?php echo htmlspecialchars($ebook['title']); ?></h3>
                            <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author'] ?? 'Unknown'); ?></p>
                            <p class="price">KES <?php echo number_format($ebook['price'], 2); ?></p>
                            <a href="#" class="btn">View Details</a>
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