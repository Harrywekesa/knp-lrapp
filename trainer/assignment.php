<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get assignment ID from URL
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$assignment_id) {
    header('Location: assignments.php');
    exit();
}

// Get assignment details
$assignment = getAssignmentById($assignment_id);
if (!$assignment) {
    header('Location: assignments.php');
    exit();
}

// Get course details
$course = getProgramById($assignment['program_id']);
if (!$course) {
    header('Location: assignments.php');
    exit();
}

// Check if user teaches this course
if ($course['trainer_id'] != $user['id']) {
    header('Location: assignments.php');
    exit();
}

// Get submissions for this assignment
global $pdo;
$stmt = $pdo->prepare("SELECT s.*, u.name as student_name FROM assignment_submissions s JOIN users u ON s.user_id = u.id WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC");
$stmt->execute([$assignment_id]);
$submissions = $stmt->fetchAll();

// Get enrolled students for this course
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN enrollments e ON u.id = e.user_id WHERE e.program_id = ? AND e.status = 'active'");
$stmt->execute([$course['id']]);
$enrolledStudents = $stmt->fetchAll();

// Calculate submission statistics
$totalSubmissions = count($submissions);
$totalStudents = count($enrolledStudents);
$submissionRate = $totalStudents > 0 ? round(($totalSubmissions / $totalStudents) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assignment['title']); ?> - <?php echo APP_NAME; ?></title>
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
                    <h2><?php echo htmlspecialchars($assignment['title']); ?></h2>
                    <p>Assignment Details</p>
                </div>
                
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                        <p><strong>Due Date:</strong> <?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></p>
                        <p><strong>Max Points:</strong> <?php echo $assignment['max_points']; ?></p>
                        <p><strong>Status:</strong> 
                            <?php if ($assignment['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactive</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description'] ?? 'No description available'); ?></p>
                    </div>
                    <div>
                        <a href="assignments.php" class="btn">Back to Assignments</a>
                        <a href="edit_assignment.php?id=<?php echo $assignment['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">Edit Assignment</a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Submission Statistics</h2>
                    <p>Overview of student submissions</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-number"><?php echo $totalSubmissions; ?></div>
                        <div class="stat-label">Submissions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo $totalStudents; ?></div>
                        <div class="stat-label">Enrolled Students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-number"><?php echo $submissionRate; ?>%</div>
                        <div class="stat-label">Submission Rate</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number">
                            <?php echo count(array_filter($submissions, function($s) { return $s['points_awarded'] !== null; })); ?>
                        </div>
                        <div class="stat-label">Graded</div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Student Submissions</h2>
                    <p>Submissions for <?php echo htmlspecialchars($assignment['title']); ?></p>
                </div>
                
                <?php if (empty($submissions)): ?>
                    <div class="alert">No submissions yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Submitted At</th>
                                    <th>Status</th>
                                    <th>Points Awarded</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                    <td><?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?></td>
                                    <td>
                                        <?php if ($submission['points_awarded'] !== null): ?>
                                            <span class="badge badge-success">Graded</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $submission['points_awarded'] !== null ? $submission['points_awarded'] . '/' . $assignment['max_points'] : 'Not graded'; ?>
                                    </td>
                                    <td>
                                        <a href="view_submission.php?id=<?php echo $submission['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                        <?php if ($submission['points_awarded'] === null): ?>
                                            <a href="grade_submission.php?id=<?php echo $submission['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Grade</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Assignment Actions</h2>
                    <p>Manage this assignment</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="grade_all_submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📝</div>
                        <div>Grade All Submissions</div>
                    </a>
                    <a href="download_submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📥</div>
                        <div>Download All Submissions</div>
                    </a>
                    <a href="send_reminder.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">✉️</div>
                        <div>Send Reminder</div>
                    </a>
                    <a href="extend_deadline.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📅</div>
                        <div>Extend Deadline</div>
                    </a>
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
    </style>
</body>
</html>