<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get enrolled programs
$enrolledPrograms = [];
foreach ($enrollments as $enrollment) {
    $program = getProgramById($enrollment['program_id']);
    if ($program) {
        $program['enrollment_id'] = $enrollment['id'];
        $enrolledPrograms[] = $program;
    }
}

// Get all available programs
$allPrograms = getAllPrograms();

// Group programs by department and level
$departmentPrograms = [];
foreach ($allPrograms as $program) {
    $deptId = $program['department_id'];
    $level = $program['level'];
    if (!isset($departmentPrograms[$deptId])) {
        $departmentPrograms[$deptId] = [
            'department' => getDepartmentById($deptId),
            'levels' => []
        ];
    }
    if (!isset($departmentPrograms[$deptId]['levels'][$level])) {
        $departmentPrograms[$deptId]['levels'][$level] = [];
    }
    $departmentPrograms[$deptId]['levels'][$level][] = $program;
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
    <title>My Programs - <?php echo APP_NAME; ?></title>
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
                    <li><a href="courses.php" class="active">My Programs</a></li>
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
                    <h2>My Academic Programs</h2>
                    <p>Programs you are currently enrolled in</p>
                </div>
                
                <?php if (empty($enrolledPrograms)): ?>
                    <div class="alert">You haven't enrolled in any programs yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <a href="#browse-programs" class="btn">Browse Available Programs</a>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($enrolledPrograms as $program): ?>
                        <div class="program-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($program['name']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;"><?php echo htmlspecialchars($program['code']); ?></p>
                            </div>
                            <div class="program-card-content">
                                <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span><?php echo $program['duration']; ?> years</span>
                                    <span class="badge badge-success">Active</span>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <a href="program.php?id=<?php echo $program['id']; ?>" class="btn btn-block">View Units</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div id="browse-programs" class="card">
                <div class="card-header">
                    <h2>Browse Academic Programs</h2>
                    <p>Explore all available programs organized by department and level</p>
                </div>
                
                <?php foreach ($departmentPrograms as $deptId => $deptData): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($deptData['department']['name']); ?>
                    </h3>
                    
                    <?php foreach ($deptData['levels'] as $level => $levelPrograms): ?>
                    <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                        <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);"><?php echo $levelNames[$level] ?? ucfirst($level); ?></h4>
                        <div class="grid">
                            <?php foreach ($levelPrograms as $program): ?>
                            <div class="program-card">
                                <div style="background: #ddd; height: 120px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                    <?php echo htmlspecialchars($program['code']); ?>
                                </div>
                                <div class="program-card-content">
                                    <h4><?php echo htmlspecialchars($program['name']); ?></h4>
                                    <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 80)) . '...'; ?></p>
                                    <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                        <span><?php echo $program['duration']; ?> years</span>
                                        <a href="program.php?id=<?php echo $program['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Units</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
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
        
        .program-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .program-card:hover {
            transform: translateY(-5px);
        }
        
        .program-card-content {
            padding: 1rem;
        }
        
        .program-card-content h4 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</body>
</html>