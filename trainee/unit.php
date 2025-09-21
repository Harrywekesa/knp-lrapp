<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get unit ID from URL
$unit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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

// Get department details
$department = getDepartmentById($program['department_id']);

// Check if user is enrolled in this program
$enrollments = getUserEnrollments($user['id']);
$enrolledProgramIds = array_column($enrollments, 'program_id');
$isEnrolledInProgram = in_array($program['id'], $enrolledProgramIds);

// Check if user is registered for this unit
$unitRegistered = false;
if ($isEnrolledInProgram) {
    foreach ($enrollments as $enrollment) {
        if ($enrollment['program_id'] == $program['id']) {
            $registeredUnits = getRegisteredUnits($enrollment['id']);
            foreach ($registeredUnits as $regUnit) {
                if ($regUnit['unit_id'] == $unit_id) {
                    $unitRegistered = true;
                    break 2;
                }
            }
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
    <title><?php echo htmlspecialchars($unit['name']); ?> - <?php echo APP_NAME; ?></title>
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
                            <h2><?php echo htmlspecialchars($unit['name']); ?></h2>
                            <p><?php echo htmlspecialchars($unit['code']); ?> - Year <?php echo $unit['year']; ?>, Semester <?php echo $unit['semester']; ?></p>
                        </div>
                        <div>
                            <a href="program.php?id=<?php echo $program['id']; ?>" class="btn">Back to <?php echo htmlspecialchars($program['name']); ?></a>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Unit Overview</h3>
                            </div>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                        <?php echo htmlspecialchars($unit['code']); ?>
                                    </div>
                                </div>
                                
                                <div style="flex: 2; min-width: 250px;">
                                    <h3><?php echo htmlspecialchars($unit['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($unit['description'] ?? 'No description available'); ?></p>
                                    
                                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                                        <div>
                                            <strong>Program:</strong>
                                            <p><?php echo htmlspecialchars($program['name']); ?></p>
                                        </div>
                                        <div>
                                            <strong>Department:</strong>
                                            <p><?php echo htmlspecialchars($department['name']); ?></p>
                                        </div>
                                        <div>
                                            <strong>Credits:</strong>
                                            <p><?php echo $unit['credits']; ?></p>
                                        </div>
                                        <div>
                                            <strong>Year/Semester:</strong>
                                            <p>Y<?php echo $unit['year']; ?>/S<?php echo $unit['semester']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!$isEnrolledInProgram): ?>
                                <div class="alert alert-warning">
                                    You are not enrolled in the <?php echo htmlspecialchars($program['name']); ?> program. 
                                    <a href="enroll.php?program_id=<?php echo $program['id']; ?>" class="btn btn-block" style="margin-top: 1rem;">Enroll in Program</a>
                                </div>
                            <?php elseif (!$unitRegistered): ?>
                                <div class="alert alert-warning">
                                    You are not registered for this unit. 
                                    <a href="register_unit.php?unit_id=<?php echo $unit['id']; ?>" class="btn btn-block" style="margin-top: 1rem;">Register for Unit</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($isEnrolledInProgram && $unitRegistered): ?>
                            <?php foreach ($materialGroups as $type => $typeMaterials): ?>
                                <?php if (!empty($typeMaterials)): ?>
                                <div class="card">
                                    <div class="card-header">
                                        <h3><?php echo $materialTypeNames[$type] ?? ucfirst($type); ?></h3>
                                        <p><?php echo count($typeMaterials); ?> <?php echo strtolower($materialTypeNames[$type] ?? ucfirst($type)); ?> available</p>
                                    </div>
                                    
                                    <div class="grid">
                                        <?php foreach ($typeMaterials as $material): ?>
                                        <div class="material-card">
                                            <div style="position: relative;">
                                                <?php if ($material['cover_image']): ?>
                                                    <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px 4px 0 0;">
                                                <?php else: ?>
                                                    <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                                        <?php echo $materialIcons[$material['type']] ?? '📁'; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($material['price'] > 0): ?>
                                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                        KES <?php echo number_format($material['price'], 2); ?>
                                                    </div>
                                                <?php elseif ($material['access_level'] === 'public'): ?>
                                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(16, 185, 129, 0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                                        FREE
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="material-card-content">
                                                <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                                                <p class="material-type">
                                                    <?php echo $materialTypeNames[$material['type']] ?? ucfirst($material['type']); ?>
                                                </p>
                                                <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                                    <?php if ($material['type'] === 'ebook' || $material['type'] === 'lecture_note'): ?>
                                                        <span><?php echo $material['preview_pages'] ?? 0; ?>/<?php echo $material['pages'] ?? 'N/A'; ?> pages</span>
                                                    <?php elseif ($material['type'] === 'video'): ?>
                                                        <span><?php echo $material['duration'] ?? 'N/A'; ?></span>
                                                    <?php elseif ($material['type'] === 'assignment'): ?>
                                                        <span>Due: <?php echo date('M j', strtotime($material['due_date'] ?? 'N/A')); ?></span>
                                                    <?php else: ?>
                                                        <span>N/A</span>
                                                    <?php endif; ?>
                                                    <div>
                                                        <a href="material.php?id=<?php echo $material['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                                            <?php echo ($material['access_level'] === 'public' || $material['price'] <= 0) ? 'View' : 'Purchase'; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Unit Information</h3>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Code:</strong></span>
                                    <span><?php echo htmlspecialchars($unit['code']); ?></span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Year:</strong></span>
                                    <span><?php echo $unit['year']; ?></span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Semester:</strong></span>
                                    <span><?php echo $unit['semester']; ?></span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Credits:</strong></span>
                                    <span><?php echo $unit['credits']; ?></span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span><strong>Status:</strong></span>
                                    <span>
                                        <?php if ($unit['status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactive</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Information</h3>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <div style="text-align: center; margin-bottom: 1rem;">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                        <?php echo strtoupper(substr($program['name'] ?? 'P', 0, 1)); ?>
                                    </div>
                                    <h4><?php echo htmlspecialchars($program['name']); ?></h4>
                                    <p style="margin: 0.25rem 0 0; color: #666;"><?php echo htmlspecialchars($program['code']); ?></p>
                                </div>
                                
                                <p><?php echo htmlspecialchars($program['description'] ?? 'No description available'); ?></p>
                                
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span><strong>Duration:</strong></span>
                                    <span><?php echo $program['duration']; ?> years</span>
                                </div>
                                
                                <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                                    <span><strong>Level:</strong></span>
                                    <span><?php echo ucfirst($program['level']); ?></span>
                                </div>
                                
                                <div style="margin-top: 1rem;">
                                    <a href="program.php?id=<?php echo $program['id']; ?>" class="btn btn-block">View Program Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Department Information</h3>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <div style="text-align: center; margin-bottom: 1rem;">
                                    <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                        <?php echo strtoupper(substr($department['name'] ?? 'D', 0, 1)); ?>
                                    </div>
                                    <h4><?php echo htmlspecialchars($department['name']); ?></h4>
                                    <p style="margin: 0.25rem 0 0; color: #666;"><?php echo htmlspecialchars($department['code']); ?></p>
                                </div>
                                
                                <p><?php echo htmlspecialchars($department['description'] ?? 'No description available'); ?></p>
                                
                                <div style="margin-top: 1rem;">
                                    <a href="department.php?id=<?php echo $department['id']; ?>" class="btn btn-block">View Department Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($isEnrolledInProgram && $unitRegistered): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <div style="display: grid; grid-template-columns: 1fr; gap: 0.5rem;">
                                    <a href="live_classes.php?unit=<?php echo $unit['id']; ?>" class="btn" style="text-align: center; padding: 1rem;">
                                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">🎬</div>
                                        <div>Live Classes</div>
                                    </a>
                                    <a href="assignments.php?unit=<?php echo $unit['id']; ?>" class="btn" style="text-align: center; padding: 1rem;">
                                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📋</div>
                                        <div>Assignments</div>
                                    </a>
                                    <a href="forum.php?unit=<?php echo $unit['id']; ?>" class="btn" style="text-align: center; padding: 1rem;">
                                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">💬</div>
                                        <div>Discussion Forum</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
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
        
        .material-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .material-card:hover {
            transform: translateY(-5px);
        }
        
        .material-card-content {
            padding: 1rem;
        }
        
        .material-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        
        .material-type {
            color: var(--accent-color);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>