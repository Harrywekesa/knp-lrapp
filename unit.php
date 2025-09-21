<?php
require_once 'includes/functions.php';
$theme = getThemeSettings();

// Get unit ID from URL
$unit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$unit_id) {
    header('Location: index.php');
    exit();
}

// Get unit details
$unit = getUnitById($unit_id);
if (!$unit) {
    header('Location: index.php');
    exit();
}

// Get program and department details
$program = getProgramById($unit['program_id']);
$department = getDepartmentById($program['department_id']);

// Get materials for this unit
$materials = getMaterialsByUnit($unit_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($unit['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="program.php?id=<?php echo $program['id']; ?>"><?php echo htmlspecialchars($program['name']); ?></a></li>
                    <li><a href="#materials">Materials</a></li>
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
                        <h1 style="margin: 0;"><?php echo htmlspecialchars($unit['name']); ?></h1>
                        <p style="margin: 0.5rem 0 0; color: #666;">
                            <?php echo htmlspecialchars($unit['code']); ?> - 
                            Year <?php echo $unit['year']; ?>, Semester <?php echo $unit['semester']; ?>
                        </p>
                        <p style="margin: 0.5rem 0 0; color: #666;">
                            <?php echo htmlspecialchars($program['name']); ?> - 
                            <?php echo htmlspecialchars($department['name']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="program.php?id=<?php echo $program['id']; ?>" class="btn">Back to <?php echo htmlspecialchars($program['name']); ?></a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Unit Overview</h2>
                    </div>
                    <p><?php echo htmlspecialchars($unit['description'] ?? 'No description available for this unit.'); ?></p>
                    <p><strong>Credits:</strong> <?php echo $unit['credits']; ?></p>
                </div>
            </div>
        </section>

        <section id="materials" style="padding: 2rem 0;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Learning Materials</h2>
                        <p>Resources for <?php echo htmlspecialchars($unit['name']); ?></p>
                    </div>
                    
                    <?php if (empty($materials)): ?>
                        <div class="alert">No materials available for this unit yet.</div>
                    <?php else: ?>
                        <div class="grid">
                            <?php foreach ($materials as $material): ?>
                            <div class="material-card">
                                <div style="position: relative;">
                                    <?php if ($material['cover_image']): ?>
                                        <img src="<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                                            <?php 
                                            switch($material['type']) {
                                                case 'lecture_note': echo '📝'; break;
                                                case 'assignment': echo '📋'; break;
                                                case 'video': echo '🎬'; break;
                                                case 'ebook': echo '📖'; break;
                                                default: echo '📁'; break;
                                            }
                                            ?>
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
                                        <?php 
                                        switch($material['type']) {
                                            case 'lecture_note': echo 'Lecture Note'; break;
                                            case 'assignment': echo 'Assignment'; break;
                                            case 'video': echo 'Video'; break;
                                            case 'ebook': echo 'E-book'; break;
                                            default: echo 'Resource'; break;
                                        }
                                        ?>
                                    </p>
                                    <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                    <div style="margin-top: 1rem;">
                                        <?php if ($material['access_level'] === 'public' || isLoggedIn()): ?>
                                            <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">View Material</a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-block">Login to Access</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
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
    </style>
</body>
</html>