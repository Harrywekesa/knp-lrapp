<?php
require_once 'includes/functions.php';
$theme = getThemeSettings();

// Get material ID from URL
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$material_id) {
    header('Location: index.php');
    exit();
}

// Get material details
$material = getMaterialById($material_id);
if (!$material) {
    header('Location: index.php');
    exit();
}

// Get unit, program, and department details
$unit = getUnitById($material['unit_id']);
$program = getProgramById($unit['program_id']);
$department = getDepartmentById($program['department_id']);

// Check access permissions
$canAccess = ($material['access_level'] === 'public') || isLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($material['title']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="unit.php?id=<?php echo $unit['id']; ?>"><?php echo htmlspecialchars($unit['name']); ?></a></li>
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
                        <h1 style="margin: 0;"><?php echo htmlspecialchars($material['title']); ?></h1>
                        <p style="margin: 0.5rem 0 0; color: #666;">
                            <?php echo htmlspecialchars($unit['name']); ?> - <?php echo htmlspecialchars($program['name']); ?>
                        </p>
                    </div>
                    <div>
                        <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn">Back to <?php echo htmlspecialchars($unit['name']); ?></a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Material Details</h2>
                    </div>
                    
                    <?php if (!$canAccess): ?>
                        <div class="alert alert-error">
                            This material requires registration to access. 
                            <a href="login.php" style="color: #3b82f6; text-decoration: underline;">Login</a> or 
                            <a href="register.php" style="color: #3b82f6; text-decoration: underline;">register</a> to continue.
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                            <div style="flex: 1; min-width: 300px;">
                                <?php if ($material['cover_image']): ?>
                                    <img src="<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="background: #ddd; height: 300px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
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
                            </div>
                            
                            <div style="flex: 2; min-width: 300px;">
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
                                
                                <div style="margin: 1rem 0;">
                                    <p><strong>Unit:</strong> <?php echo htmlspecialchars($unit['name']); ?></p>
                                    <p><strong>Program:</strong> <?php echo htmlspecialchars($program['name']); ?></p>
                                    <p><strong>Department:</strong> <?php echo htmlspecialchars($department['name']); ?></p>
                                </div>
                                
                                <div style="margin: 1rem 0;">
                                    <p><?php echo htmlspecialchars($material['description'] ?? 'No description available.'); ?></p>
                                </div>
                                
                                <div style="margin: 1rem 0;">
                                    <?php if ($material['price'] > 0): ?>
                                        <p><strong>Price:</strong> KES <?php echo number_format($material['price'], 2); ?></p>
                                    <?php else: ?>
                                        <p><strong>Access:</strong> Free</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div style="margin-top: 1rem;">
                                    <?php if ($material['file_path']): ?>
                                        <a href="<?php echo htmlspecialchars($material['file_path']); ?>" class="btn btn-block" download>Download Material</a>
                                    <?php else: ?>
                                        <button class="btn btn-block" disabled>No file available</button>
                                    <?php endif; ?>
                                </div>
                            </div>
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
        .material-type {
            color: var(--accent-color);
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }
    </style>
</body>
</html>