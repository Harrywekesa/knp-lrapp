<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get all departments and programs
$departments = getAllDepartments();
$programs = getAllPrograms();

// Group programs by department
$departmentPrograms = [];
foreach ($programs as $program) {
    $deptId = $program['department_id'];
    if (!isset($departmentPrograms[$deptId])) {
        $departmentPrograms[$deptId] = [
            'department' => getDepartmentById($deptId),
            'programs' => []
        ];
    }
    $departmentPrograms[$deptId]['programs'][] = $program;
}

// Level display names
$levelNames = [
    'certificate' => 'Certificate Programs',
    'diploma' => 'Diploma Programs',
    'degree' => 'Degree Programs',
    'masters' => 'Masters Programs',
    'phd' => 'PhD Programs'
];

// Handle enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enroll_program'])) {
        $program_id = $_POST['program_id'];
        
        if (enrollUser($user['id'], $program_id)) {
            $success = "Successfully enrolled in program";
        } else {
            $error = "Failed to enroll in program";
        }
    }
}

// Get user's current enrollments
$enrollments = getUserEnrollments($user['id']);
$enrolledProgramIds = array_column($enrollments, 'program_id');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll in Programs - <?php echo APP_NAME; ?></title>
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
                    <li><a href="assignments.php">Assignments</a></li>
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
                    <h2>Enroll in Programs</h2>
                    <p>Browse and enroll in academic programs</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search programs...">
                </div>
                
                <?php if (!empty($enrollments)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Your Current Enrollments</h3>
                        <p>Programs you are currently enrolled in</p>
                    </div>
                    
                    <div class="grid">
                        <?php foreach ($enrollments as $enrollment): ?>
                        <div class="enrollment-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($enrollment['program_name']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;">
                                    <?php echo htmlspecialchars($enrollment['department_name']); ?>
                                </p>
                            </div>
                            <div class="enrollment-card-content">
                                <p><strong>Enrolled:</strong> <?php echo date('M j, Y', strtotime($enrollment['enrollment_date'])); ?></p>
                                <p><strong>Status:</strong> 
                                    <?php if ($enrollment['status'] === 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?php echo ucfirst($enrollment['status']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <div style="margin-top: 1rem;">
                                    <a href="program.php?id=<?php echo $enrollment['program_id']; ?>" class="btn btn-block">View Program Details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Available Programs</h3>
                        <p>Browse programs organized by department and level</p>
                    </div>
                    
                    <?php foreach ($departmentPrograms as $deptId => $deptData): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($deptData['department']['name']); ?>
                        </h3>
                        
                        <?php foreach ($deptData['programs'] as $program): ?>
                        <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <h4 style="margin: 0; color: var(--accent-color);">
                                    <?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['code']); ?>)
                                </h4>
                                <span style="color: #666;">
                                    <?php echo $program['duration']; ?> years
                                </span>
                            </div>
                            
                            <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 150)) . '...'; ?></p>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                <span>
                                    <?php echo $levelNames[$program['level']] ?? ucfirst($program['level']); ?>
                                </span>
                                <div>
                                    <?php if (in_array($program['id'], $enrolledProgramIds)): ?>
                                        <span class="badge badge-success">Enrolled</span>
                                    <?php else: ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                                            <button type="submit" name="enroll_program" class="btn">Enroll in Program</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
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
        
        .enrollment-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .enrollment-card:hover {
            transform: translateY(-5px);
        }
        
        .enrollment-card-content {
            padding: 1rem;
        }
        
        .enrollment-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</body>
</html>