<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        
        if (updateUserRole($user_id, $new_role)) {
            $success = "User role updated successfully";
        } else {
            $error = "Failed to update user role";
        }
    } elseif (isset($_POST['suspend_user'])) {
        $user_id = $_POST['user_id'];
        
        if (suspendUser($user_id)) {
            $success = "User suspended successfully";
        } else {
            $error = "Failed to suspend user";
        }
    } elseif (isset($_POST['activate_user'])) {
        $user_id = $_POST['user_id'];
        
        if (activateUser($user_id)) {
            $success = "User activated successfully";
        } else {
            $error = "Failed to activate user";
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        
        if (deleteUser($user_id)) {
            $success = "User deleted successfully";
        } else {
            $error = "Failed to delete user";
        }
    } elseif (isset($_POST['create_user'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        // Check if email already exists
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            if (createUser($name, $email, $password, $role)) {
                $success = "User created successfully";
            } else {
                $error = "Failed to create user";
            }
        }
    }
}

// Get all users
$users = getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo APP_NAME; ?></title>
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
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="users.php" class="active">Manage Users</a></li>
                    <li><a href="Programs.php">Manage Programs</a></li>
                    <li><a href="trainers.php">Approve Trainers</a></li>
                    <li><a href="sales.php">Commerce</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Manage Users</h2>
                    <p>View and manage all users in the system</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New User</button>
                
                <div id="create-user-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New User</h3>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="trainee">Trainee</option>
                                <option value="trainer">Trainer</option>
                                <option value="Presenter">Presenter</option>
                                <option value="exam_officer">Exam Officer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_user" class="btn">Create User</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search users...">
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                        <option value="trainee" <?php echo $u['role'] === 'trainee' ? 'selected' : ''; ?>>Trainee</option>
                                        <option value="trainer" <?php echo $u['role'] === 'trainer' ? 'selected' : ''; ?>>Trainer</option>
                                        <option value="presenter" <?php echo $u['role'] === 'presenter' ? 'selected' : ''; ?>>Presenter</option>
                                        <option value="exam_officer" <?php echo $u['role'] === 'exam_officer' ? 'selected' : ''; ?>>Exam Officer</option>
                                        <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            </td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php elseif ($u['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <?php if ($u['status'] === 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="suspend_user" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Suspend</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" name="activate_user" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Activate</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <style>
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-user-form');
            const cancelBtn = document.getElementById('cancel-create');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
        });
    </script>
</body>
</html>