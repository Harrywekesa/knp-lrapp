<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get statistics
$users = getAllUsers();
$programs = getAllPrograms();
$ebooks = getAllEbooks();
$pendingTrainers = getPendingTrainers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <li><a href="dashboard.php" class="active">Admin Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="Programs.php">Manage Programs</a></li>
                    <li><a href="trainers.php">Approve Trainers</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="ai_assistant.php">Your AI Assistant</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="sales.php">Commerce</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Admin Dashboard</h2>
                    <p>Manage your learning platform</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo count($users); ?></div>
                        <div class="stat-label">Total Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo count($programs); ?></div>
                        <div class="stat-label">Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📖</div>
                        <div class="stat-number"><?php echo count($ebooks); ?></div>
                        <div class="stat-label">E-Books</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👨‍🏫</div>
                        <div class="stat-number"><?php echo count($pendingTrainers); ?></div>
                        <div class="stat-label">Pending Trainers</div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($pendingTrainers)): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Pending Trainer Approvals</h2>
                    <p>Approve new trainer accounts</p>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingTrainers as $trainer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                            <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($trainer['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                    <button type="submit" name="approve_trainer" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Approve</button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="trainer_id" value="<?php echo $trainer['id']; ?>">
                                    <button type="submit" name="reject_trainer" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="users.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                        <div>Manage Users</div>
                    </a>
                    <a href="programs.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📚</div>
                        <div>Manage Programs</div>
                    </a>
                    <a href="ebooks.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📚</div>
                        <div>Manage Ebooks</div>
                    </a>
                    <a href="trainers.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍🏫</div>
                        <div>Approve Trainers</div>
                    </a>
                    <a href="settings.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎨</div>
                        <div>Theme Settings</div>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <style>
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
    </style>
</body>
</html>