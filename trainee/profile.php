<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        $phone = $_POST['phone'];
        $department_id = $_POST['department_id'];
        
        if (updateUserProfile($user['id'], $name, $email, $bio, $phone, $department_id)) {
            $success = "Profile updated successfully";
            // Refresh user data
            $user = getUserById($_SESSION['user_id']);
        } else {
            $error = "Failed to update profile";
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (updateUserPassword($user['id'], $new_password)) {
                    $success = "Password updated successfully";
                } else {
                    $error = "Failed to update password";
                }
            } else {
                $error = "New passwords do not match";
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get enrolled programs
$enrolledPrograms = [];
foreach ($enrollments as $enrollment) {
    $program = getProgramById($enrollment['program_id']);
    if ($program) {
        $program['enrollment_id'] = $enrollment['id'];
        $enrolledPrograms[] = $program;
    }
}

// Get departments for dropdown
$departments = getAllDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary-color']; ?>;
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
                    <li><a href="courses.php">My Programs</a></li>
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="assignments.php">Assignments</a></li>
                    <li><a href="forum.php">Discussion Forum</a></li>
                    <li><a href="profile.php" class="active">Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
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
                        <div class="card">
                            <div class="card-header">
                                <h3>Profile Picture</h3>
                            </div>
                            <div style="text-align: center; margin-bottom: 2rem;">
                                <div style="width: 120px; height: 120px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 3rem;">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p style="margin: 0.5rem 0 0; color: #666;"><?php echo ucfirst($user['role']); ?></p>
                                <p style="margin: 0.25rem 0 0; color: #666;">Member since <?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h3>Account Information</h3>
                                </div>
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
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?php if ($user['status'] === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php elseif ($user['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending Approval</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Suspended</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Member Since:</strong></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h3>Enrolled Programs</h3>
                                </div>
                                <?php if (empty($enrolledPrograms)): ?>
                                    <div class="alert">You haven't enrolled in any programs yet.</div>
                                <?php else: ?>
                                    <ul style="list-style: none; padding: 0;">
                                        <?php foreach ($enrolledPrograms as $program): ?>
                                        <li style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                                            <strong><?php echo htmlspecialchars($program['name']); ?></strong>
                                            <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($program['code']); ?> - 
                                                <?php echo $program['duration']; ?> years
                                            </p>
                                            <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                                <?php echo htmlspecialchars($program['department_name']); ?>
                                            </p>
                                            <div style="margin-top: 0.5rem;">
                                                <a href="program.php?id=<?php echo $program['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Units</a>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Edit Profile</h3>
                                <p>Update your personal information</p>
                            </div>
                            <form method="POST">
                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="name">Full Name *</label>
                                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="email">Email Address *</label>
                                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="department_id">Department</label>
                                            <select id="department_id" name="department_id" class="form-control">
                                                <option value="">Select a department</option>
                                                <?php foreach ($departments as $department): ?>
                                                <option value="<?php echo $department['id']; ?>" <?php echo ($user['department_id'] == $department['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($department['name']); ?> (<?php echo htmlspecialchars($department['code']); ?>)
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea id="bio" name="bio" class="form-control" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="update_profile" class="btn">Update Profile</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Change Password</h3>
                                <p>Update your account password</p>
                            </div>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="new_password">New Password *</label>
                                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password *</label>
                                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="update_password" class="btn">Change Password</button>
                                </div>
                            </form>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Account Preferences</h3>
                                <p>Manage your notification and privacy settings</p>
                            </div>
                            <form method="POST">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="email_notifications" <?php echo ($user['email_notifications'] ?? 1) ? 'checked' : ''; ?>> 
                                        Receive email notifications
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="sms_notifications" <?php echo ($user['sms_notifications'] ?? 0) ? 'checked' : ''; ?>> 
                                        Receive SMS notifications
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="forum_notifications" <?php echo ($user['forum_notifications'] ?? 1) ? 'checked' : ''; ?>> 
                                        Receive forum notifications
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="update_preferences" class="btn">Save Preferences</button>
                                </div>
                            </form>
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
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }
        
        .form-col {
            flex: 1;
            padding: 0 0.5rem;
            min-width: 250px;
        }
        
        @media (max-width: 768px) {
            .form-col {
                min-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>