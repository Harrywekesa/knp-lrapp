<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get statistics
$departments = getAllDepartments();
$programs = getAllPrograms();
$users = getAllUsers();

// Get active programs
$activePrograms = array_filter($programs, function($p) {
    return $p['status'] === 'active';
});

// Get enrolled students
$enrolledStudents = array_filter($users, function($u) {
    return $u['role'] === 'trainee' && $u['status'] === 'active';
});

// Get recent attendance records
$recentAttendance = getRecentAttendanceRecords(10);

// Get recent assignments
$recentAssignments = getRecentAssignments(10);

// Get recent exams
$recentExams = getRecentExams(10);

// Get recent results
$recentResults = getRecentResults(10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Officer Dashboard - <?php echo APP_NAME; ?></title>
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
                    <li><a href="dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="exams.php">Exams</a></li>
                    <li><a href="results.php">Results</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="students.php">Students</a></li>
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
                    <h2>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p>Exam Officer Dashboard</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-number"><?php echo count($departments); ?></div>
                        <div class="stat-label">Departments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-number"><?php echo count($activePrograms); ?></div>
                        <div class="stat-label">Active Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👥</div>
                        <div class="stat-number"><?php echo count($enrolledStudents); ?></div>
                        <div class="stat-label">Enrolled Students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-number"><?php echo count($recentAttendance); ?></div>
                        <div class="stat-label">Recent Attendance</div>
                    </div>
                </div>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Attendance</h3>
                        <p>Latest attendance records</p>
                    </div>
                    
                    <?php if (empty($recentAttendance)): ?>
                        <div class="alert">No attendance records found.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentAttendance as $record): ?>
                            <div class="attendance-record" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($record['student_name']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j, g:i A', strtotime($record['joined_at'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($record['session_title']); ?> - 
                                    <?php echo htmlspecialchars($record['class_title']); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($record['unit_name']); ?> (<?php echo htmlspecialchars($record['program_name']); ?>)
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <?php if ($record['status'] === 'present'): ?>
                                        <span class="badge badge-success">Present</span>
                                    <?php elseif ($record['status'] === 'late'): ?>
                                        <span class="badge badge-warning">Late</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Absent</span>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <a href="attendance.php?session_id=<?php echo $record['session_id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Assignments</h3>
                        <p>Latest submitted assignments</p>
                    </div>
                    
                    <?php if (empty($recentAssignments)): ?>
                        <div class="alert">No assignments submitted recently.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentAssignments as $assignment): ?>
                            <div class="assignment-record" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($assignment['assignment_title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j, g:i A', strtotime($assignment['submitted_at'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($assignment['student_name']); ?> - 
                                    <?php echo htmlspecialchars($assignment['unit_name']); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    Points: <?php echo $assignment['points_awarded'] ?? 'N/A'; ?>/<?php echo $assignment['max_points']; ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="assignments.php?assignment_id=<?php echo $assignment['assignment_id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Grade Assignment</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Exams</h3>
                        <p>Latest exam schedules</p>
                    </div>
                    
                    <?php if (empty($recentExams)): ?>
                        <div class="alert">No exams scheduled recently.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentExams as $exam): ?>
                            <div class="exam-record" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($exam['title']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($exam['unit_name']); ?> (<?php echo htmlspecialchars($exam['program_name']); ?>)
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    <?php echo date('g:i A', strtotime($exam['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($exam['end_time'])); ?>
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <?php if ($exam['status'] === 'scheduled'): ?>
                                        <span class="badge badge-success">Scheduled</span>
                                    <?php elseif ($exam['status'] === 'in_progress'): ?>
                                        <span class="badge badge-warning">In Progress</span>
                                    <?php elseif ($exam['status'] === 'completed'): ?>
                                        <span class="badge badge-info">Completed</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?php echo ucfirst($exam['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top: 0.5rem;">
                                    <a href="exams.php?exam_id=<?php echo $exam['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Results</h3>
                        <p>Latest exam results</p>
                    </div>
                    
                    <?php if (empty($recentResults)): ?>
                        <div class="alert">No results recorded recently.</div>
                    <?php else: ?>
                        <div style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($recentResults as $result): ?>
                            <div class="result-record" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <h4 style="margin: 0; font-size: 1rem;">
                                        <?php echo htmlspecialchars($result['student_name']); ?>
                                    </h4>
                                    <span style="color: var(--accent-color); font-size: 0.85rem; font-weight: 500;">
                                        <?php echo date('M j, Y', strtotime($result['graded_at'])); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($result['exam_title']); ?> - 
                                    <?php echo htmlspecialchars($result['unit_name']); ?>
                                </p>
                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.85rem;">
                                    Points: <?php echo $result['points_awarded']; ?>/<?php echo $result['max_points']; ?> 
                                    (<?php echo round(($result['points_awarded'] / $result['max_points']) * 100, 2); ?>%)
                                </p>
                                <div style="margin-top: 0.5rem;">
                                    <a href="results.php?exam_id=<?php echo $result['exam_id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Results</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                    <p>Access important exam officer functions</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <a href="attendance.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📋</div>
                        <div>Attendance</div>
                    </a>
                    <a href="exams.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📝</div>
                        <div>Exams</div>
                    </a>
                    <a href="results.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📊</div>
                        <div>Results</div>
                    </a>
                    <a href="reports.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">📈</div>
                        <div>Reports</div>
                    </a>
                    <a href="students.php" class="btn" style="text-align: center; padding: 1.5rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">👥</div>
                        <div>Students</div>
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
        
        .attendance-record:hover, .assignment-record:hover, .exam-record:hover, .result-record:hover {
            background-color: #f8fafc;
        }
    </style>
</body>
</html>