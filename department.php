<?php
require_once 'includes/functions.php';
$theme = getThemeSettings();

// Get department ID from URL
$department_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$department_id) {
    header('Location: index.php');
    exit();
}

// Get department details
$department = getDepartmentById($department_id);
if (!$department) {
    header('Location: index.php');
    exit();
}

// Get programs for this department
$programs = getProgramsByDepartment($department_id);

// Group programs by level
$levelPrograms = [];
$levelNames = [
    'certificate' => 'Certificate Programs',
    'diploma' => 'Diploma Programs',
    'degree' => 'Degree Programs',
    'masters' => 'Masters Programs',
    'phd' => 'PhD Programs'
];

foreach ($programs as $program) {
    $level = $program['level'];
    if (!isset($levelPrograms[$level])) {
        $levelPrograms[$level] = [];
    }
    $levelPrograms[$level][] = $program;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="index.php#departments">Departments</a></li>
                    <li><a href="#programs">Programs</a></li>
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
                        <h1 style="margin: 0;"><?php echo htmlspecialchars($department['name']); ?></h1>
                        <p style="margin: 0.5rem 0 0; color: #666;"><?php echo htmlspecialchars($department['code']); ?></p>
                    </div>
                    <div>
                        <a href="index.php#departments" class="btn">Back to Departments</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Department Overview</h2>
                    </div>
                    <p><?php echo htmlspecialchars($department['description'] ?? 'No description available for this department.'); ?></p>
                </div>
            </div>
        </section>

        <section id="programs" style="padding: 2rem 0;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Academic Programs</h2>
                        <p>Programs offered by <?php echo htmlspecialchars($department['name']); ?></p>
                    </div>
                    
                    <?php if (empty($programs)): ?>
                        <div class="alert">No programs available for this department yet.</div>
                    <?php else: ?>
                        <?php foreach ($levelPrograms as $level => $levelProgramsList): ?>
                        <div class="card" style="margin-bottom: 2rem; border-left: 3px solid var(--accent-color);">
                            <h3 style="margin: 0 0 1rem 0; color: var(--accent-color);"><?php echo $levelNames[$level] ?? ucfirst($level); ?></h3>
                            
                            <div class="grid">
                                <?php foreach ($levelProgramsList as $program): ?>
                                <div class="program-card">
                                    <div style="background: #ddd; height: 120px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                        <?php echo htmlspecialchars($program['code']); ?>
                                    </div>
                                    <div class="program-card-content">
                                        <h3><?php echo htmlspecialchars($program['name']); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($program['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
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
        
        .program-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</body>
</html>