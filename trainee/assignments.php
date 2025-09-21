<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get registered units
$registeredUnits = [];
foreach ($enrollments as $enrollment) {
    $units = getRegisteredUnits($enrollment['id']);
    $registeredUnits = array_merge($registeredUnits, $units);
}

// Get assignments for registered units
$assignments = [];
foreach ($registeredUnits as $unit) {
    $unitAssignments = getAssignmentsByUnit($unit['unit_id']);
    foreach ($unitAssignments as $assignment) {
        $assignment['unit_name'] = $unit['unit_name'];
        $assignments[] = $assignment;
    }
}

// Separate active and past assignments
$activeAssignments = [];
$pastAssignments = [];

foreach ($assignments as $assignment) {
    if (strtotime($assignment['due_date']) > time()) {
        $activeAssignments[] = $assignment;
    } else {
        $pastAssignments[] = $assignment;
    }
}

// Sort assignments by due date
usort($activeAssignments, function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});

usort($pastAssignments, function($a, $b) {
    return strtotime($b['due_date']) - strtotime($a['due_date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - <?php echo APP_NAME; ?></title>
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
                    <li><a href="assignments.php" class="active">Assignments</a></li>
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
                    <h2>Assignments</h2>
                    <p>View and submit your course assignments</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search assignments by title, unit, or program...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="active">Active Assignments (<?php echo count($activeAssignments); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Assignments (<?php echo count($pastAssignments); ?>)</button>
                        <button class="tab-button" data-tab="all">All Assignments (<?php echo count($assignments); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="active" class="tab-pane active">
                            <?php if (empty($activeAssignments)): ?>
                                <div class="alert">No active assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Left</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activeAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['unit_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php 
                                                    $daysLeft = ceil((strtotime($assignment['due_date']) - time()) / (60 * 60 * 24));
                                                    echo $daysLeft . ' days';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?");
                                                    $stmt->execute([$assignment['id'], $user['id']]);
                                                    $submission = $stmt->fetch();
                                                    
                                                    if ($submission) {
                                                        if ($submission['status'] === 'graded') {
                                                            echo '<span class="badge badge-success">Graded (' . $submission['points_awarded'] . '/' . $assignment['max_points'] . ')</span>';
                                                        } else {
                                                            echo '<span class="badge badge-warning">Submitted</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="badge badge-danger">Not Submitted</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="past" class="tab-pane">
                            <?php if (empty($pastAssignments)): ?>
                                <div class="alert">No past assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Overdue</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pastAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['unit_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php 
                                                    $daysOverdue = floor((time() - strtotime($assignment['due_date'])) / (60 * 60 * 24));
                                                    echo $daysOverdue . ' days';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE assignment_id = ? AND user_id = ?");
                                                    $stmt->execute([$assignment['id'], $user['id']]);
                                                    $submission = $stmt->fetch();
                                                    
                                                    if ($submission) {
                                                        if ($submission['status'] === 'graded') {
                                                            echo '<span class="badge badge-success">Graded (' . $submission['points_awarded'] . '/' . $assignment['max_points'] . ')</span>';
                                                        } else {
                                                            echo '<span class="badge badge-warning">Submitted</span>';
                                                        }
                                                    } else {
                                                        echo '<span class="badge badge-danger">Not Submitted</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="all" class="tab-pane">
                            <?php if (empty($assignments)): ?>
                                <div class="alert">No assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['unit_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php 
                                                    if (strtotime($assignment['due_date']) > time()) {
                                                        echo '<span class="badge badge-success">Active</span>';
                                                    } else {
                                                        echo '<span class="badge badge-secondary">Past</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
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