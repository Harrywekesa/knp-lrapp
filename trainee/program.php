<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get program ID from URL
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$program_id) {
    header('Location: courses.php');
    exit();
}

// Get program details
$program = getProgramById($program_id);
if (!$program) {
    header('Location: courses.php');
    exit();
}

// Check if user is already enrolled in this program
$isEnrolled = false;
$enrollment = null;
$enrollments = getUserEnrollments($user['id']);
foreach ($enrollments as $e) {
    if ($e['program_id'] == $program_id) {
        $isEnrolled = true;
        $enrollment = $e;
        break;
    }
}

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll'])) {
        if (enrollUserInProgram($user['id'], $program_id)) {
            $success = "Successfully enrolled in " . htmlspecialchars($program['name']);
            $isEnrolled = true;
            // Refresh enrollment data
            $enrollments = getUserEnrollments($user['id']);
            foreach ($enrollments as $e) {
                if ($e['program_id'] == $program_id) {
                    $enrollment = $e;
                    break;
                }
            }
        } else {
            $error = "Failed to enroll in program";
        }
    } elseif (isset($_POST['register_units'])) {
        $selectedUnits = $_POST['units'] ?? [];
        if (empty($selectedUnits)) {
            $error = "Please select at least one unit to register";
        } else {
            $registeredCount = 0;
            foreach ($selectedUnits as $unit_id) {
                if (registerUnitForEnrollment($enrollment['id'], $unit_id)) {
                    $registeredCount++;
                }
            }
            if ($registeredCount > 0) {
                $success = "Successfully registered $registeredCount units";
            } else {
                $error = "Failed to register units";
            }
        }
    }
}

// Get units for this program
$units = getUnitsByProgram($program['id']);

// Get registered units if enrolled
$registeredUnits = [];
if ($isEnrolled) {
    $registeredUnits = getRegisteredUnits($enrollment['id']);
}

// Get department details
$department = getDepartmentById($program['department_id']);

// Group units by year and semester
$unitStructure = [];
foreach ($units as $unit) {
    $year = $unit['year'];
    $semester = $unit['semester'];
    if (!isset($unitStructure[$year])) {
        $unitStructure[$year] = [];
    }
    if (!isset($unitStructure[$year][$semester])) {
        $unitStructure[$year][$semester] = [];
    }
    $unitStructure[$year][$semester][] = $unit;
}

// Level display names
$levelNames = [
    'certificate' => 'Certificate Programs',
    'diploma' => 'Diploma Programs',
    'degree' => 'Degree Programs',
    'masters' => 'Masters Programs',
    'phd' => 'PhD Programs'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($program['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <h2><?php echo htmlspecialchars($program['name']); ?></h2>
                    <p><?php echo htmlspecialchars($program['code']); ?> - <?php echo $program['duration']; ?> years</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2rem;">
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Overview</h3>
                            </div>
                            <p><?php echo htmlspecialchars($program['description'] ?? 'No description available for this program.'); ?></p>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                                <div>
                                    <strong>Department:</strong>
                                    <p><?php echo htmlspecialchars($department['name'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <strong>Level:</strong>
                                    <p><?php echo $levelNames[$program['level']] ?? ucfirst($program['level']); ?></p>
                                </div>
                                <div>
                                    <strong>Duration:</strong>
                                    <p><?php echo $program['duration']; ?> years</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Enrollment Status</h3>
                            </div>
                            
                            <?php if ($isEnrolled): ?>
                                <div class="alert alert-success">
                                    You are currently enrolled in this program.
                                </div>
                                
                                <div style="margin-top: 1rem;">
                                    <h4>Registered Units</h4>
                                    <?php if (empty($registeredUnits)): ?>
                                        <div class="alert">You haven't registered any units for this program yet.</div>
                                        <div style="text-align: center; margin: 1rem 0;">
                                            <button id="show-register-units" class="btn">Register Units</button>
                                        </div>
                                    <?php else: ?>
                                        <ul style="list-style: none; padding: 0;">
                                            <?php foreach ($registeredUnits as $reg): ?>
                                            <li style="padding: 0.75rem; border-bottom: 1px solid #eee;">
                                                <strong><?php echo htmlspecialchars($reg['unit_name']); ?></strong>
                                                <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                                    <?php echo htmlspecialchars($reg['unit_code']); ?> - 
                                                    Year <?php echo $reg['year']; ?>, Semester <?php echo $reg['semester']; ?> (<?php echo $reg['credits']; ?> credits)
                                                </p>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div style="text-align: center; margin: 1rem 0;">
                                            <button id="show-register-units" class="btn">Register More Units</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert">
                                    You are not currently enrolled in this program.
                                </div>
                                
                                <form method="POST" style="margin-top: 1rem;">
                                    <button type="submit" name="enroll" class="btn btn-block">Enroll in This Program</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Coordinator</h3>
                            </div>
                            <div style="text-align: center; padding: 1rem;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    <?php echo strtoupper(substr($program['trainer_name'] ?? 'T', 0, 1)); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($program['trainer_name'] ?? 'Not Assigned'); ?></h4>
                                <p style="margin: 0.25rem 0 0; color: #666;">Program Coordinator</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="courses.php" class="btn btn-block">Back to Programs</a>
                                <a href="ebooks.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View E-Books</a>
                                <a href="live_classes.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View Live Classes</a>
                                <a href="assignments.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View Assignments</a>
                                <a href="forum.php?program=<?php echo $program['id']; ?>" class="btn btn-block">Discussion Forum</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($isEnrolled): ?>
                <div id="register-units-section" class="card" style="display: none;">
                    <div class="card-header">
                        <h3>Register Units</h3>
                        <p>Select units to register for this program</p>
                    </div>
                    
                    <form method="POST">
                        <?php if (empty($units)): ?>
                            <div class="alert">No units available for this program.</div>
                        <?php else: ?>
                            <?php foreach ($unitStructure as $year => $semesters): ?>
                            <div class="card" style="margin-bottom: 2rem;">
                                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Year <?php echo $year; ?></h3>
                                
                                <?php foreach ($semesters as $semester => $semesterUnits): ?>
                                <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                                    <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);">Semester <?php echo $semester; ?></h4>
                                    
                                    <?php if (empty($semesterUnits)): ?>
                                        <div class="alert">No units available for this semester.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Select</th>
                                                        <th>Unit Code</th>
                                                        <th>Unit Name</th>
                                                        <th>Credits</th>
                                                        <th>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($semesterUnits as $unit): ?>
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="units[]" value="<?php echo $unit['id']; ?>" 
                                                                   <?php 
                                                                   foreach ($registeredUnits as $reg) {
                                                                       if ($reg['unit_id'] == $unit['id']) {
                                                                           echo 'checked disabled';
                                                                           break;
                                                                       }
                                                                   }
                                                                   ?>>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($unit['code']); ?></td>
                                                        <td><?php echo htmlspecialchars($unit['name']); ?></td>
                                                        <td><?php echo $unit['credits']; ?></td>
                                                        <td><?php echo htmlspecialchars(substr($unit['description'] ?? 'No description', 0, 80)) . '...'; ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="form-group" style="margin-top: 1.5rem;">
                                <button type="submit" name="register_units" class="btn btn-block">Register Selected Units</button>
                                <button type="button" id="cancel-register" class="btn btn-block btn-secondary" style="margin-top: 0.5rem;">Cancel</button>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Program Units</h2>
                    <p>All units available in this program</p>
                </div>
                
                <?php if (empty($units)): ?>
                    <div class="alert">No units available for this program.</div>
                <?php else: ?>
                    <?php foreach ($unitStructure as $year => $semesters): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Year <?php echo $year; ?></h3>
                        
                        <?php foreach ($semesters as $semester => $semesterUnits): ?>
                        <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                            <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);">Semester <?php echo $semester; ?></h4>
                            
                            <?php if (empty($semesterUnits)): ?>
                                <div class="alert">No units available for this semester.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($semesterUnits as $unit): ?>
                                    <div class="unit-card">
                                        <div style="background: #ddd; height: 120px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                            <?php echo htmlspecialchars($unit['code']); ?>
                                        </div>
                                        <div class="unit-card-content">
                                            <h4><?php echo htmlspecialchars($unit['name']); ?></h4>
                                            <p><?php echo htmlspecialchars(substr($unit['description'] ?? 'No description available', 0, 80)) . '...'; ?></p>
                                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                                <span><?php echo $unit['credits']; ?> credits</span>
                                                <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Materials</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
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
        
        .unit-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .unit-card:hover {
            transform: translateY(-5px);
        }
        
        .unit-card-content {
            padding: 1rem;
        }
        
        .unit-card-content h4 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showRegisterBtn = document.getElementById('show-register-units');
            const registerSection = document.getElementById('register-units-section');
            const cancelRegisterBtn = document.getElementById('cancel-register');
            
            if (showRegisterBtn) {
                showRegisterBtn.addEventListener('click', function() {
                    registerSection.style.display = 'block';
                    showRegisterBtn.style.display = 'none';
                });
            }
            
            if (cancelRegisterBtn) {
                cancelRegisterBtn.addEventListener('click', function() {
                    registerSection.style.display = 'none';
                    if (showRegisterBtn) {
                        showRegisterBtn.style.display = 'inline-block';
                    }
                });
            }
        });
    </script>
</body>
</html>