<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    if ($stmt->execute([$name, $email, $user['id']])) {
        $success = "Profile updated successfully";
        // Refresh user data
        $user = getUserById($_SESSION['user_id']);
    } else {
        $error = "Failed to update profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
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
                    <h2>User Profile</h2>
                    <p>Manage your account information</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                    <div style="flex: 1; min-width: 300px;">
                        <div style="text-align: center; margin-bottom: 2rem;">
                            <div style="width: 120px; height: 120px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                            <p style="color: #666;"><?php echo ucfirst($user['role']); ?></p>
                        </div>
                        
                        <div class="card">
                            <h3>Account Information</h3>
                            <table class="table">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td><?php echo ucfirst($user['role']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Member Since:</strong></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <h3>Edit Profile</h3>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn">Update Profile</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card">
                            <h3>Change Password</h3>
                            <form>
                                <div class="form-group">
                                    <label for="current-password">Current Password</label>
                                    <input type="password" id="current-password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new-password">New Password</label>
                                    <input type="password" id="new-password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm-password">Confirm New Password</label>
                                    <input type="password" id="confirm-password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn">Change Password</button>
                                </div>
                            </form>
                        </div>
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
</body>
</html>