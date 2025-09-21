<?php
require_once 'includes/functions.php';
$theme = getThemeSettings();

// Get program ID from URL
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$program_id) {
    header('Location: index.php');
    exit();
}

// Get program details
$program = getProgramById($program_id);
if (!$program) {
    header('Location: index.php');
    exit();
}

// Get department details
$department = getDepartmentById($program['department_id']);

// Get units for this program
$units = getUnitsByProgram($program_id);

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
    'certificate' => 'Certificate',
    'diploma' => 'Diploma',
    'degree' => 'Degree',
    'masters' => 'Masters',
    'phd' => 'PhD'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($program['name']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="department.php?id=<?php echo $department['id']; ?>"><?php echo htmlspecialchars($department['name']); ?></a></li>
                    <li><a href="#units">Units</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn" style="padding: 0.5rem 1rem; margin-left: 1rem;">Login</a></li>
                        <li><a href="register.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-left: 0.5rem;">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section style="padding: 2rem 0; background: #f8fafc;">
            <div class="container">
                <div style="display: flex; align-items: center; margin-bottom: 2rem;">
                    <div style="flex: 1;">
                        <h1 style="margin: 0;"><?php echo htmlspecialchars($program['name']); ?></h1>
                        <p style="margin: 0.5rem 0 0; color: #666;">
                            <?php echo htmlspecialchars($program['code']); ?> - 
                            <?php echo $levelNames[$program['level']] ?? ucfirst($program['level']); ?> - 
                            <?php echo $program['duration']; ?> years
                        </p>
                        <p style="margin: 0.5rem 0 0; color: #666;">
                            Department: <?php echo htmlspecialchars($department['name']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="department.php?id=<?php echo $department['id']; ?>" class="btn">Back to <?php echo htmlspecialchars($department['name']); ?></a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Program Overview</h2>
                    </div>
                    <p><?php echo htmlspecialchars($program['description'] ?? 'No description available for this program.'); ?></p>
                </div>
            </div>
        </section>

        <section id="units" style="padding: 2rem 0;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Academic Units</h2>
                        <p>Units organized by year and semester</p>
                    </div>
                    
                    <?php if (empty($units)): ?>
                        <div class="alert">No units available for this program yet.</div>
                    <?php else: ?>
                        <?php foreach ($unitStructure as $year => $semesters): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <h3 style="color: var(--primary-color);">Year <?php echo $year; ?></h3>
                            
                            <?php foreach ($semesters as $semester => $semesterUnits): ?>
                            <div class="card" style="margin: 1rem 0; border-left: 3px solid var(--primary-color);">
                                <h4 style="margin: 0 0 1rem 0;">Semester <?php echo $semester; ?></h4>
                                
                                <div class="grid">
                                    <?php foreach ($semesterUnits as $unit): ?>
                                    <div class="unit-card">
                                        <div style="background: #ddd; height: 100px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                            <?php echo htmlspecialchars($unit['code']); ?>
                                        </div>
                                        <div class="unit-card-content">
                                            <h4><?php echo htmlspecialchars($unit['name']); ?></h4>
                                            <p><?php echo $unit['credits']; ?> credits</p>
                                            <div style="margin-top: 1rem;">
                                                <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn btn-block">View Materials</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div style="border-top: 1px solid #444; padding: 1rem 0; text-align: center; color: #aaa;">
                <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <style>
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
</body>
</html>