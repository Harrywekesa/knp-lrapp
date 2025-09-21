<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle trainer approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_trainer'])) {
        $trainer_id = $_POST['trainer_id'];
        if (approveTrainer($trainer_id)) {
            $success = "Trainer approved successfully";
        } else {
            $error = "Failed to approve trainer";
        }
    } elseif (isset($_POST['reject_trainer'])) {
        $trainer_id = $_POST['trainer_id'];
        if (deleteUser($trainer_id)) {
            $success = "Trainer application rejected";
        } else {
            $error = "Failed to reject trainer";
        }
    }
}

// Get pending trainers
$pendingTrainers = getPendingTrainers();
$approvedTrainers = [];
global $pdo;
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'trainer' AND status = 'active' ORDER BY created_at DESC");
$stmt->execute();
$approvedTrainers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Trainers - <?php echo APP_NAME; ?></title>
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
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="Programs.php">Manage Programs</a></li>
                    <li><a href="trainers.php" class="active">Approve Trainers</a></li>
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
                    <h2>Trainer Management</h2>
                    <p>Approve new trainers and manage existing ones</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="margin-bottom: 2rem;">
                    <h3>Pending Trainer Applications</h3>
                    <?php if (empty($pendingTrainers)): ?>
                        <div class="alert alert-success">No pending trainer applications</div>
                    <?php else: ?>
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
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3>Approved Trainers</h3>
                    <?php if (empty($approvedTrainers)): ?>
                        <div class="alert">No approved trainers yet</div>
                    <?php else: ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedTrainers as $trainer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($trainer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($trainer['email']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($trainer['created_at'])); ?></td>
                                    <td>
                                        <button class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                        <button class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Suspend</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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

    <script src="../assets/js/main.js"></script>
    <style>
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
    </style>
</body>
</html>