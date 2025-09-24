<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get report data
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
$recentAttendance = getRecentAttendanceRecords(20);

// Get recent assignments
$recentAssignments = getRecentAssignments(20);

// Get recent exams
$recentExams = getRecentExams(20);

// Get recent results
$recentResults = getRecentResults(20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - <?php echo APP_NAME; ?></title>
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
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="exams.php">Exams</a></li>
                    <li><a href="results.php">Results</a></li>
                    <li><a href="reports.php" class="active">Reports</a></li>
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
                    <h2>Reports & Analytics</h2>
                    <p>Generate and export detailed reports</p>
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
                        <h3>Attendance Reports</h3>
                        <p>Generate attendance statistics</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="attendance-date-range">Date Range:</label>
                        <select id="attendance-date-range" class="form-control">
                            <option value="week">Last Week</option>
                            <option value="month">Last Month</option>
                            <option value="quarter">Last Quarter</option>
                            <option value="year">Last Year</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="attendance-program">Program:</label>
                        <select id="attendance-program" class="form-control">
                            <option value="all">All Programs</option>
                            <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['id']; ?>">
                                <?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['code']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn" style="flex: 1;">Generate Report</button>
                        <button class="btn btn-secondary" style="flex: 1;">Export CSV</button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Exam Results Reports</h3>
                        <p>Generate exam performance reports</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="exam-results-date-range">Date Range:</label>
                        <select id="exam-results-date-range" class="form-control">
                            <option value="week">Last Week</option>
                            <option value="month">Last Month</option>
                            <option value="quarter">Last Quarter</option>
                            <option value="year">Last Year</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="exam-results-program">Program:</label>
                        <select id="exam-results-program" class="form-control">
                            <option value="all">All Programs</option>
                            <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['id']; ?>">
                                <?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['code']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn" style="flex: 1;">Generate Report</button>
                        <button class="btn btn-secondary" style="flex: 1;">Export CSV</button>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Student Performance Reports</h3>
                        <p>Generate individual student reports</p>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="student-performance-date-range">Date Range:</label>
                        <select id="student-performance-date-range" class="form-control">
                            <option value="semester">Current Semester</option>
                            <option value="year">Current Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <label for="student-performance-program">Program:</label>
                        <select id="student-performance-program" class="form-control">
                            <option value="all">All Programs</option>
                            <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['id']; ?>">
                                <?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['code']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn" style="flex: 1;">Generate Report</button>
                        <button class="btn btn-secondary" style="flex: 1;">Export CSV</button>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Recent Reports</h3>
                    <p>Recently generated reports</p>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Date Generated</th>
                                <th>Generated By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Monthly Attendance Report</td>
                                <td>Attendance</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-2 days')); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>
                                    <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                    <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Final Exam Results</td>
                                <td>Exam Results</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-1 week')); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>
                                    <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                    <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Student Performance Summary</td>
                                <td>Performance</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-3 weeks')); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td>
                                    <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                    <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Quarterly Attendance Analysis</td>
                                <td>Attendance</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-1 month')); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><span class="badge badge-warning">Processing</span></td>
                                <td>
                                    <a href="#" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                    <a href="#" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Download</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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