<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get unit ID from URL
$unit_id = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;

if (!$unit_id) {
    header('Location: dashboard.php');
    exit();
}

// Get unit details
$unit = getUnitById($unit_id);
if (!$unit) {
    header('Location: dashboard.php');
    exit();
}

// Get program details
$program = getProgramById($unit['program_id']);
if (!$program) {
    header('Location: dashboard.php');
    exit();
}

// Check if user is enrolled in this program
$enrollments = getUserEnrollments($user['id']);
$enrolledProgramIds = array_column($enrollments, 'program_id');
$isEnrolledInProgram = in_array($program['id'], $enrolledProgramIds);

// Check if already registered for this unit
$unitAlreadyRegistered = false;
if ($isEnrolledInProgram) {
    foreach ($enrollments as $enrollment) {
        if ($enrollment['program_id'] == $program['id']) {
            $registeredUnits = getRegisteredUnits($enrollment['id']);
            foreach ($registeredUnits as $regUnit) {
                if ($regUnit['unit_id'] == $unit_id) {
                    $unitAlreadyRegistered = true;
                    break 2;
                }
            }
        }
    }
}

// Handle unit registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_unit'])) {
    if (!$isEnrolledInProgram) {
        $error = "You must be enrolled in the program to register for units.";
    } elseif ($unitAlreadyRegistered) {
        $error = "You are already registered for this unit.";
    } else {
        // Find the enrollment for this program
        $enrollment_id = null;
        foreach ($enrollments as $enrollment) {
            if ($enrollment['program_id'] == $program['id']) {
                $enrollment_id = $enrollment['id'];
                break;
            }
        }
        
        if ($enrollment_id && registerUnitForEnrollment($enrollment_id, $unit_id)) {
            $success = "Successfully registered for " . htmlspecialchars($unit['name']);
            $unitAlreadyRegistered = true;
        } else {
            $error = "Failed to register for unit. Please try again.";
        }
    }
}

// Get materials for this unit
$materials = getMaterialsByUnit($unit_id);

// Group materials by type
$materialGroups = [
    'lecture_note' => [],
    'assignment' => [],
    'video' => [],
    'ebook' => [],
    'other' => []
];

foreach ($materials as $material) {
    $type = $material['type'];
    if (!isset($materialGroups[$type])) {
        $materialGroups[$type] = [];
    }
    $materialGroups[$type][] = $material;
}

// Material type display names
$materialTypeNames = [
    'lecture_note' => 'Lecture Notes',
    'assignment' => 'Assignments',
    'video' => 'Videos',
    'ebook' => 'E-books',
    'other' => 'Other Resources'
];

// Material type icons
$materialIcons = [
    'lecture_note' => '📝',
    'assignment' => '📋',
    'video' => '🎬',
    'ebook' => '📖',
    'other' => '📁'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Unit - <?php echo APP_NAME; ?></title>
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
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2>Register for Unit</h2>
                            <p><?php echo htmlspecialchars($unit['name']); ?></p>
                        </div>
                        <div>
                            <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn">Back to Unit</a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Unit Registration</h3>
                            </div>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                        <h3 style="margin: 0;"><?php echo htmlspecialchars($unit['name']); ?></h3>
                                        <p style="margin: 0.5rem 0 0; opacity: 0.9;">
                                            <?php echo htmlspecialchars($unit['code']); ?>
                                        </p>
                                    </div>
                                    <div class="unit-card-content">
                                        <p><strong>Program:</strong> <?php echo htmlspecialchars($program['name']); ?></p>
                                        <p><strong>Year:</strong> <?php echo $unit['year']; ?></p>
                                        <p><strong>Semester:</strong> <?php echo $unit['semester']; ?></p>
                                        <p><strong>Credits:</strong> <?php echo $unit['credits']; ?></p>
                                        <p><?php echo htmlspecialchars(substr($unit['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                    </div>
                                </div>
                                
                                <div style="flex: 1; min-width: 200px;">
                                    <div class="card">
                                        <div class="card-header">
                                            <h3>Registration Status</h3>
                                        </div>
                                        
                                        <?php if (!$isEnrolledInProgram): ?>
                                            <div class="alert alert-warning">
                                                You are not enrolled in the <?php echo htmlspecialchars($program['name']); ?> program.
                                            </div>
                                            <div style="margin-top: 1rem;">
                                                <a href="enroll.php?program_id=<?php echo $program['id']; ?>" class="btn btn-block">Enroll in Program</a>
                                            </div>
                                        <?php elseif ($unitAlreadyRegistered): ?>
                                            <div class="alert alert-success">
                                                You are already registered for this unit.
                                            </div>
                                            <div style="margin-top: 1rem;">
                                                <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn btn-block">View Unit Materials</a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                Confirm your registration for this unit.
                                            </div>
                                            <form method="POST">
                                                <div style="margin-top: 1rem;">
                                                    <button type="submit" name="register_unit" class="btn btn-block">Register for Unit</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="card">
                                        <div class="card-header">
                                            <h3>Unit Materials</h3>
                                        </div>
                                        
                                        <div style="margin-top: 1rem;">
                                            <?php foreach ($materialGroups as $type => $typeMaterials): ?>
                                                <?php if (!empty($typeMaterials)): ?>
                                                <div style="margin-bottom: 1rem;">
                                                    <h4 style="margin: 0 0 0.5rem 0; color: var(--accent-color);">
                                                        <?php echo $materialTypeNames[$type] ?? ucfirst($type); ?> (<?php echo count($typeMaterials); ?>)
                                                    </h4>
                                                    <ul style="list-style: none; padding: 0;">
                                                        <?php foreach (array_slice($typeMaterials, 0, 3) as $material): ?>
                                                        <li style="padding: 0.5rem 0; border-bottom: 1px solid #eee;">
                                                            <div style="display: flex; align-items: center;">
                                                                <div style="margin-right: 0.75rem; font-size: 1.2rem;">
                                                                    <?php echo $materialIcons[$material['type']] ?? '📁'; ?>
                                                                </div>
                                                                <div style="flex: 1;">
                                                                    <a href="material.php?id=<?php echo $material['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                                        <?php echo htmlspecialchars($material['title']); ?>
                                                                    </a>
                                                                    <?php if ($material['price'] > 0): ?>
                                                                        <span style="margin-left: 0.5rem; background: rgba(0,0,0,0.7); color: white; padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.75rem;">
                                                                            KES <?php echo number_format($material['price'], 2); ?>
                                                                        </span>
                                                                    <?php elseif ($material['access_level'] === 'public'): ?>
                                                                        <span style="margin-left: 0.5rem; background: rgba(16, 185, 129, 0.8); color: white; padding: 0.1rem 0.3rem; border-radius: 4px; font-size: 0.75rem;">
                                                                            FREE
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Information</h3>
                            </div>
                            
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    <?php echo strtoupper(substr($program['name'] ?? 'P', 0, 1)); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($program['name']); ?></h4>
                                <p style="margin: 0.25rem 0 0; color: #666;"><?php echo htmlspecialchars($program['code']); ?></p>
                            </div>
                            
                            <div style="margin-top: 1rem;">
                                <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 150)) . '...'; ?></p>
                                
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span><strong>Duration:</strong> <?php echo $program['duration']; ?> years</span>
                                    <span><strong>Level:</strong> <?php echo ucfirst($program['level']); ?></span>
                                </div>
                                
                                <div style="margin-top: 1rem;">
                                    <a href="program.php?id=<?php echo $program['id']; ?>" class="btn btn-block">View Program Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Registration Benefits</h3>
                            </div>
                            
                            <ul style="list-style: none; padding: 0;">
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Access to Materials</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">View all unit materials and resources</p>
                                    </div>
                                </li>
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Live Classes</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Join scheduled live sessions</p>
                                    </div>
                                </li>
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Assignments</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Submit assignments and get feedback</p>
                                    </div>
                                </li>
                                <li style="display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Certification</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Earn certificates upon completion</p>
                                    </div>
                                </li>
                            </ul>
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
        
        .unit-card-content {
            padding: 1rem;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }
        
        .form-col {
            flex: 1;
            padding: 0 0.5rem;
            min-width: 250px;
        }
        
        @media (max-width: 768px) {
            .form-col {
                min-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
</body>
</html>