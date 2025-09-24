<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle student actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll_student'])) {
        $user_id = $_POST['user_id'];
        $program_id = $_POST['program_id'];
        
        if (enrollUser($user_id, $program_id)) {
            $success = "Student enrolled successfully";
        } else {
            $error = "Failed to enroll student";
        }
    } elseif (isset($_POST['update_enrollment'])) {
        $enrollment_id = $_POST['enrollment_id'];
        $status = $_POST['status'];
        
        if (updateEnrollmentStatus($enrollment_id, $status)) {
            $success = "Enrollment status updated successfully";
        } else {
            $error = "Failed to update enrollment status";
        }
    } elseif (isset($_POST['register_unit'])) {
        $enrollment_id = $_POST['enrollment_id'];
        $unit_id = $_POST['unit_id'];
        
        if (registerUnit($enrollment_id, $unit_id)) {
            $success = "Unit registered successfully";
        } else {
            $error = "Failed to register unit";
        }
    }
}

// Get students
$students = getStudents();

// Get programs
$programs = getAllPrograms();

// Get enrollments
$enrollments = getAllEnrollments();

// Get registered units
$registeredUnits = getAllRegisteredUnits();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - <?php echo APP_NAME; ?></title>
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
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="students.php" class="active">Students</a></li>
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
                    <h2>Student Management</h2>
                    <p>Manage student enrollments and registrations</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search students by name or email...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Students (<?php echo count($students); ?>)</button>
                        <button class="tab-button" data-tab="enrolled">Enrolled Students (<?php echo count($enrollments); ?>)</button>
                        <button class="tab-button" data-tab="units">Unit Registrations (<?php echo count($registeredUnits); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($students)): ?>
                                <div class="alert">No students found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
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
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo $student['id']; ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td>
                                                    <?php echo ucfirst($student['role']); ?>
                                                </td>
                                                <td>
                                                    <?php if ($student['status'] === 'active'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php elseif ($student['status'] === 'pending'): ?>
                                                        <span class="badge badge-warning">Pending</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Suspended</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($student['status'] === 'active'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                                            <button type="submit" name="suspend_user" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;" onclick="return confirm('Are you sure you want to suspend this user?')">Suspend</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                                            <button type="submit" name="activate_user" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Activate</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                                        <button type="submit" name="delete_user" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="enrolled" class="tab-pane">
                            <?php if (empty($enrollments)): ?>
                                <div class="alert">No student enrollments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Program</th>
                                                <th>Department</th>
                                                <th>Enrollment Date</th>
                                                <th>Status</th>
                                                <th>Units Registered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($enrollments as $enrollment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($enrollment['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($enrollment['program_name']); ?></td>
                                                <td><?php echo htmlspecialchars($enrollment['department_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                                <td>
                                                    <?php if ($enrollment['status'] === 'active'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php elseif ($enrollment['status'] === 'completed'): ?>
                                                        <span class="badge badge-info">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Dropped</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $units = getRegisteredUnits($enrollment['id']);
                                                    echo count($units);
                                                    ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                                        <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                                            <option value="active" <?php echo ($enrollment['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                                            <option value="completed" <?php echo ($enrollment['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="dropped" <?php echo ($enrollment['status'] === 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                                                        </select>
                                                        <input type="hidden" name="update_enrollment" value="1">
                                                    </form>
                                                    <a href="enrollment.php?id=<?php echo $enrollment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">View Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="units" class="tab-pane">
                            <?php if (empty($registeredUnits)): ?>
                                <div class="alert">No unit registrations found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Unit</th>
                                                <th>Program</th>
                                                <th>Registration Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($registeredUnits as $registration): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($registration['student_name']); ?></td>
                                                <td><?php echo htmlspecialchars($registration['unit_name']); ?></td>
                                                <td><?php echo htmlspecialchars($registration['program_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($registration['registration_date'])); ?></td>
                                                <td>
                                                    <?php if ($registration['status'] === 'registered'): ?>
                                                        <span class="badge badge-success">Registered</span>
                                                    <?php elseif ($registration['status'] === 'completed'): ?>
                                                        <span class="badge badge-info">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Dropped</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="registration_id" value="<?php echo $registration['id']; ?>">
                                                        <select name="status" onchange="this.form.submit()" class="form-control" style="width: auto; display: inline-block;">
                                                            <option value="registered" <?php echo ($registration['status'] === 'registered') ? 'selected' : ''; ?>>Registered</option>
                                                            <option value="completed" <?php echo ($registration['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="dropped" <?php echo ($registration['status'] === 'dropped') ? 'selected' : ''; ?>>Dropped</option>
                                                        </select>
                                                        <input type="hidden" name="update_registration" value="1">
                                                    </form>
                                                    <a href="registration.php?id=<?php echo $registration['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">View Details</a>
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