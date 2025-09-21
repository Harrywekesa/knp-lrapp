<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all assignments for trainer's courses
$allAssignments = [];
foreach ($courses as $course) {
    $courseAssignments = getAssignmentsByCourse($course['id']);
    foreach ($courseAssignments as $assignment) {
        $assignment['course_name'] = $course['name'];
        $allAssignments[] = $assignment;
    }
}

// Separate active and past assignments
$activeAssignments = [];
$pastAssignments = [];

foreach ($allAssignments as $assignment) {
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
    <title>Browse Assignments - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
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
                    <li><a href="courses.php">My Courses</a></li>
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="assignments.php" class="active">Assignments</a></li>
                    <li><a href="forum.php">Discussion Forum</a></li>
                    <li><a href="ai_assistant.php">Your AI Assistant</a></li>
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
                    <h2>Browse Assignments</h2>
                    <p>Access all course assignments you've created</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search assignments by title or course...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Assignments (<?php echo count($allAssignments); ?>)</button>
                        <button class="tab-button" data-tab="active">Active (<?php echo count($activeAssignments); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Assignments (<?php echo count($pastAssignments); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($allAssignments)): ?>
                                <div class="alert">No assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php if (strtotime($assignment['due_date']) > time()): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Past</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <?php if (strtotime($assignment['due_date']) > time()): ?>
                                                        <a href="edit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Edit</a>
                                                    <?php else: ?>
                                                        <a href="grade_assignments.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Grade</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="active" class="tab-pane">
                            <?php if (empty($activeAssignments)): ?>
                                <div class="alert">No active assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Left</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activeAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
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
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <a href="edit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Edit</a>
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
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Overdue</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pastAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
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
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <a href="grade_assignments.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Grade</a>
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