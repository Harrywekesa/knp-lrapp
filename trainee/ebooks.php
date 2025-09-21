<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get registered units
$registeredUnits = [];
foreach ($enrollments as $enrollment) {
    $units = getRegisteredUnits($enrollment['id']);
    $registeredUnits = array_merge($registeredUnits, $units);
}

// Get materials for registered units
$materials = [];
foreach ($registeredUnits as $unit) {
    $unitMaterials = getMaterialsByUnit($unit['unit_id']);
    foreach ($unitMaterials as $material) {
        $material['unit_name'] = $unit['unit_name'];
        $materials[] = $material;
    }
}

// Separate by material type
$lectureNotes = [];
$ebooks = [];
$videos = [];
$assignments = [];
$others = [];

foreach ($materials as $material) {
    switch ($material['type']) {
        case 'lecture_note':
            $lectureNotes[] = $material;
            break;
        case 'ebook':
            $ebooks[] = $material;
            break;
        case 'video':
            $videos[] = $material;
            break;
        case 'assignment':
            $assignments[] = $material;
            break;
        default:
            $others[] = $material;
            break;
    }
}

// Get all public materials (for browsing)
$allPublicMaterials = [];
global $pdo;
$stmt = $pdo->query("SELECT um.*, u.name as unit_name, p.name as program_name, d.name as department_name 
                     FROM unit_materials um 
                     JOIN units u ON um.unit_id = u.id 
                     JOIN programs p ON u.program_id = p.id 
                     JOIN departments d ON p.department_id = d.id 
                     WHERE um.access_level = 'public' AND um.status = 'published' 
                     ORDER BY um.created_at DESC 
                     LIMIT 20");
$allPublicMaterials = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Books & Materials - <?php echo APP_NAME; ?></title>
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
                    <li><a href="ebooks.php" class="active">E-Books</a></li>
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
                    <h2>E-Books & Learning Materials</h2>
                    <p>Access educational resources for your registered units</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search materials by title, unit, or program...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="my-materials">My Materials (<?php echo count($materials); ?>)</button>
                        <button class="tab-button" data-tab="public">Public Materials (<?php echo count($allPublicMaterials); ?>)</button>
                        <button class="tab-button" data-tab="lecture-notes">Lecture Notes (<?php echo count($lectureNotes); ?>)</button>
                        <button class="tab-button" data-tab="ebooks">E-Books (<?php echo count($ebooks); ?>)</button>
                        <button class="tab-button" data-tab="videos">Videos (<?php echo count($videos); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="my-materials" class="tab-pane active">
                            <?php if (empty($materials)): ?>
                                <div class="alert">No materials available for your registered units yet.</div>
                                <div style="text-align: center; margin: 2rem 0;">
                                    <a href="courses.php" class="btn">Browse and Register for Units</a>
                                </div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($materials as $material): ?>
                                    <div class="material-card">
                                        <div style="position: relative;">
                                            <?php if ($material['cover_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
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
                                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?></p>
                                            <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="margin-top: 1rem;">
                                                <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">View Material</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="public" class="tab-pane">
                            <?php if (empty($allPublicMaterials)): ?>
                                <div class="alert">No public materials available.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($allPublicMaterials as $material): ?>
                                    <div class="material-card">
                                        <div style="position: relative;">
                                            <?php if ($material['cover_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
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
                                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?></p>
                                            <p><strong>Program:</strong> <?php echo htmlspecialchars($material['program_name'] ?? 'N/A'); ?></p>
                                            <p><strong>Department:</strong> <?php echo htmlspecialchars($material['department_name'] ?? 'N/A'); ?></p>
                                            <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="margin-top: 1rem;">
                                                <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">View Material</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="lecture-notes" class="tab-pane">
                            <?php if (empty($lectureNotes)): ?>
                                <div class="alert">No lecture notes available for your registered units.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($lectureNotes as $material): ?>
                                    <div class="material-card">
                                        <div style="position: relative;">
                                            <?php if ($material['cover_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                                                    📝
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
                                            <p class="material-type">Lecture Note</p>
                                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?></p>
                                            <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="margin-top: 1rem;">
                                                <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">View Lecture Note</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="ebooks" class="tab-pane">
                            <?php if (empty($ebooks)): ?>
                                <div class="alert">No e-books available for your registered units.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($ebooks as $material): ?>
                                    <div class="material-card">
                                        <div style="position: relative;">
                                            <?php if ($material['cover_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                                                    📖
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
                                            <p class="material-type">E-book</p>
                                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?></p>
                                            <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="margin-top: 1rem;">
                                                <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">Read E-book</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="videos" class="tab-pane">
                            <?php if (empty($videos)): ?>
                                <div class="alert">No videos available for your registered units.</div>
                            <?php else: ?>
                                <div class="grid">
                                    <?php foreach ($videos as $material): ?>
                                    <div class="material-card">
                                        <div style="position: relative;">
                                            <?php if ($material['cover_image']): ?>
                                                <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                                                    🎬
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
                                            <p class="material-type">Video</p>
                                            <p><strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?></p>
                                            <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                            <div style="margin-top: 1rem;">
                                                <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">Watch Video</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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