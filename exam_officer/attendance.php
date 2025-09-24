<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle attendance actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['export_attendance'])) {
        $session_id = $_POST['session_id'];
        exportAttendanceToCSV($session_id);
        exit();
    } elseif (isset($_POST['update_attendance'])) {
        $record_id = $_POST['record_id'];
        $status = $_POST['status'];
        updateAttendanceStatus($record_id, $status);
        $success = "Attendance record updated successfully";
    }
}

// Get sessions for attendance tracking
$sessions = getAllSessions();

// Get attendance records for selected session
$selected_session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$attendanceRecords = [];
$sessionDetails = null;

if ($selected_session_id > 0) {
    $attendanceRecords = getAttendanceRecordsBySession($selected_session_id);
    $sessionDetails = getSessionById($selected_session_id);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management - <?php echo APP_NAME; ?></title>
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
                    <li><a href="attendance.php" class="active">Attendance</a></li>
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
                    <h2>Attendance Management</h2>
                    <p>Track and manage student attendance</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <form method="GET" style="display: inline-block; margin-right: 1rem;">
                        <label for="session_id">Select Session:</label>
                        <select id="session_id" name="session_id" class="form-control" style="width: auto; display: inline-block; margin: 0 0.5rem;">
                            <option value="0">Select a session</option>
                            <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo $session['id']; ?>" <?php echo ($session['id'] == $selected_session_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($session['title']); ?> - 
                                <?php echo date('M j, Y g:i A', strtotime($session['start_time'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="padding: 0.5rem 1rem;">Load Attendance</button>
                    </form>
                    
                    <?php if ($selected_session_id > 0): ?>
                    <form method="POST" style="display: inline-block;">
                        <input type="hidden" name="session_id" value="<?php echo $selected_session_id; ?>">
                        <button type="submit" name="export_attendance" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Export to CSV</button>
                    </form>
                    <?php endif; ?>
                </div>
                
                <?php if ($selected_session_id > 0 && $sessionDetails): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($sessionDetails['title']); ?></h3>
                        <p><?php echo date('M j, Y g:i A', strtotime($sessionDetails['start_time'])); ?> - 
                           <?php echo date('g:i A', strtotime($sessionDetails['end_time'])); ?></p>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <strong>Class:</strong>
                            <p><?php echo htmlspecialchars($sessionDetails['class_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <strong>Unit:</strong>
                            <p><?php echo htmlspecialchars($sessionDetails['unit_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <strong>Program:</strong>
                            <p><?php echo htmlspecialchars($sessionDetails['program_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <strong>Department:</strong>
                            <p><?php echo htmlspecialchars($sessionDetails['department_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($selected_session_id > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Attendance Records</h3>
                        <p>Student attendance for this session</p>
                    </div>
                    
                    <?php if (empty($attendanceRecords)): ?>
                        <div class="alert">No attendance records found for this session.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Joined At</th>
                                        <th>Left At</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $record): ?>
                                    <tr>
                                        <td><?php echo $record['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                                        <td><?php echo $record['joined_at'] ? date('g:i A', strtotime($record['joined_at'])) : 'N/A'; ?></td>
                                        <td><?php echo $record['left_at'] ? date('g:i A', strtotime($record['left_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <?php if ($record['status'] === 'present'): ?>
                                                <span class="badge badge-success">Present</span>
                                            <?php elseif ($record['status'] === 'late'): ?>
                                                <span class="badge badge-warning">Late</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Absent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="record_id" value="<?php echo $record['id']; ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                                    <option value="present" <?php echo ($record['status'] === 'present') ? 'selected' : ''; ?>>Present</option>
                                                    <option value="late" <?php echo ($record['status'] === 'late') ? 'selected' : ''; ?>>Late</option>
                                                    <option value="absent" <?php echo ($record['status'] === 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                </select>
                                                <input type="hidden" name="update_attendance" value="1">
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>Total Students:</strong> <?php echo count($attendanceRecords); ?>
                            </div>
                            <div>
                                <?php 
                                $presentCount = count(array_filter($attendanceRecords, function($r) { return $r['status'] === 'present'; }));
                                $lateCount = count(array_filter($attendanceRecords, function($r) { return $r['status'] === 'late'; }));
                                $absentCount = count(array_filter($attendanceRecords, function($r) { return $r['status'] === 'absent'; }));
                                ?>
                                <strong>Present:</strong> <?php echo $presentCount; ?> | 
                                <strong>Late:</strong> <?php echo $lateCount; ?> | 
                                <strong>Absent:</strong> <?php echo $absentCount; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Select a Session</h3>
                        <p>Choose a session to view attendance records</p>
                    </div>
                    
                    <div class="alert">Please select a session from the dropdown above to view attendance records.</div>
                    
                    <div class="grid">
                        <?php foreach (array_slice($sessions, 0, 6) as $session): ?>
                        <div class="session-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($session['title']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;">
                                    <?php echo date('M j, Y g:i A', strtotime($session['start_time'])); ?>
                                </p>
                            </div>
                            <div class="session-card-content">
                                <p><strong>Class:</strong> <?php echo htmlspecialchars($session['class_name'] ?? 'N/A'); ?></p>
                                <p><strong>Unit:</strong> <?php echo htmlspecialchars($session['unit_name'] ?? 'N/A'); ?></p>
                                <p><strong>Program:</strong> <?php echo htmlspecialchars($session['program_name'] ?? 'N/A'); ?></p>
                                <div style="margin-top: 1rem;">
                                    <a href="?session_id=<?php echo $session['id']; ?>" class="btn btn-block">View Attendance</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
        
        .session-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .session-card:hover {
            transform: translateY(-5px);
        }
        
        .session-card-content {
            padding: 1rem;
        }
        
        .session-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</body>
</html>