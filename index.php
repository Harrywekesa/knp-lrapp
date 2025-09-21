<?php
require_once 'includes/functions.php';
$theme = getThemeSettings();

// Get pending trainers for admin dashboard display
$pendingTrainers = [];
if (isLoggedIn() && getUserRole() === 'admin') {
    $pendingTrainers = getPendingTrainers();
}

// Get departments and programs for academic structure
$departments = getAllDepartments();
$programs = getAllPrograms();

// Group programs by department and level
$departmentPrograms = [];
foreach ($programs as $program) {
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
    '3' => 'Level 3 Programs',
    '4' => 'Level 4 Programs',
    '5' => 'Level 5 Programs',
    '6' => 'Level 6 Programs',
    '7' => 'Level 7 Programs'
];

// Get actual materials from database - properly separated
$allMaterials = [];
$freeMaterials = [];
$premiumMaterials = [];

// Get all published materials
global $pdo;
$stmt = $pdo->query("SELECT um.*, u.name as unit_name, p.name as program_name, d.name as department_name 
                     FROM unit_materials um 
                     JOIN units u ON um.unit_id = u.id 
                     JOIN programs p ON u.program_id = p.id 
                     JOIN departments d ON p.department_id = d.id 
                     WHERE um.status = 'published' 
                     ORDER BY um.created_at DESC 
                     LIMIT 50");
$allMaterials = $stmt->fetchAll();

// Properly separate free and premium materials
foreach ($allMaterials as $material) {
    // A material is free if:
    // 1. Access level is 'public' AND price is 0, OR
    // 2. Access level is 'public' AND no price set
    if (($material['access_level'] === 'public' && $material['price'] <= 0) || 
        ($material['access_level'] === 'public' && is_null($material['price']))) {
        $freeMaterials[] = $material;
    } else {
        // Everything else is premium
        $premiumMaterials[] = $material;
    }
}

// Limit arrays for display
$freeMaterials = array_slice($freeMaterials, 0, 3);
$premiumMaterials = array_slice($premiumMaterials, 0, 4);

$categories = ['All', 'Web Development', 'Programming', 'Database', 'Frontend', 'AI', 'Mathematics', 'Science'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Learning Resource Application</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="#departments">Departments</a></li>
                    <li><a href="#programs">Programs</a></li>
                    <li><a href="#materials">Materials</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <?php if (getUserRole() === 'admin' && !empty($pendingTrainers)): ?>
                            <li>
                                <a href="admin/trainers.php" class="btn" style="padding: 0.5rem 1rem; margin-left: 1rem; background: #ef4444; color: white;">
                                    <?php echo count($pendingTrainers); ?> Pending Trainers
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn" style="padding: 0.5rem 1rem; margin-left: 1rem;">Login</a></li>
                        <li><a href="register.php" class="btn btn-secondary" style="padding: 0.5rem 1rem; margin-left: 0.5rem;">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">Expand Your Knowledge with KNP LRAPP</h1>
                <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto 2rem;">
                    Access thousands of learning materials organized by departments, programs, and units. 
                    Join our community of learners and take your skills to the next level.
                </p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="#departments" class="btn" style="background: white; color: var(--primary-color); font-weight: bold; padding: 0.75rem 2rem;">Browse Departments</a>
                    <a href="register.php" class="btn btn-secondary" style="padding: 0.75rem 2rem;">Create Free Account</a>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section style="padding: 3rem 0; background: #f8fafc;">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">🏢</div>
                        <div class="stat-number"><?php echo count($departments); ?></div>
                        <div class="stat-label">Departments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🎓</div>
                        <div class="stat-number"><?php echo count($programs); ?></div>
                        <div class="stat-label">Programs</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📚</div>
                        <div class="stat-number"><?php echo count($allMaterials); ?>+</div>
                        <div class="stat-label">Learning Materials</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">👨‍🏫</div>
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Expert Instructors</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Departments Section -->
        <section id="departments" style="padding: 3rem 0;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Academic Departments</h2>
                        <p>Explore our academic departments and their programs</p>
                    </div>
                    
                    <div class="grid">
                        <?php foreach ($departments as $department): ?>
                        <div class="department-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($department['name']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;"><?php echo htmlspecialchars($department['code']); ?></p>
                            </div>
                            <div class="department-card-content">
                                <p><?php echo htmlspecialchars(substr($department['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <div style="margin-top: 1rem;">
                                    <a href="department.php?id=<?php echo $department['id']; ?>" class="btn btn-block">View Programs</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Programs by Department and Level Section -->
        <section id="programs" style="padding: 3rem 0; background: #f8fafc;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Academic Programs</h2>
                        <p>Browse programs organized by department and level</p>
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
        </section>

        <!-- Materials Section -->
        <section id="materials" style="padding: 3rem 0;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>Learning Materials</h2>
                        <p>Explore our collection of educational resources</p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <select class="form-control" style="width: auto; display: inline-block; margin-right: 0.5rem;">
                                <option>Sort by: Popularity</option>
                                <option>Sort by: Newest</option>
                                <option>Sort by: Price: Low to High</option>
                                <option>Sort by: Price: High to Low</option>
                            </select>
                            <select class="form-control" style="width: auto; display: inline-block;">
                                <?php foreach ($categories as $category): ?>
                                <option><?php echo $category; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <input type="text" class="form-control" placeholder="Search materials..." style="width: 250px;">
                        </div>
                    </div>
                    
                    <?php if (!empty($freeMaterials)): ?>
                    <h3 style="margin: 1.5rem 0 1rem;">Free Materials</h3>
                    <div class="grid">
                        <?php foreach ($freeMaterials as $material): ?>
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
                                <div style="position: absolute; top: 10px; right: 10px; background: rgba(16, 185, 129, 0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                    FREE
                                </div>
                            </div>
                            <div class="material-card-content">
                                <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                                <p class="category"><?php echo htmlspecialchars($material['unit_name'] ?? 'General'); ?></p>
                                <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description available', 0, 80)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                    <span class="material-type">
                                        <?php 
                                        switch($material['type']) {
                                            case 'lecture_note': echo 'Lecture Note'; break;
                                            case 'assignment': echo 'Assignment'; break;
                                            case 'video': echo 'Video'; break;
                                            case 'ebook': echo 'E-book'; break;
                                            default: echo 'Resource'; break;
                                        }
                                        ?>
                                    </span>
                                    <span class="price free">FREE</span>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-block">View Material</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($premiumMaterials)): ?>
                    <h3 style="margin: 2rem 0 1rem;">Premium Materials</h3>
                    <div class="grid">
                        <?php foreach ($premiumMaterials as $material): ?>
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
                                <?php endif; ?>
                            </div>
                            <div class="material-card-content">
                                <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                                <p class="category"><?php echo htmlspecialchars($material['unit_name'] ?? 'General'); ?></p>
                                <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description available', 0, 80)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                                    <span class="material-type">
                                        <?php 
                                        switch($material['type']) {
                                            case 'lecture_note': echo 'Lecture Note'; break;
                                            case 'assignment': echo 'Assignment'; break;
                                            case 'video': echo 'Video'; break;
                                            case 'ebook': echo 'E-book'; break;
                                            default: echo 'Resource'; break;
                                        }
                                        ?>
                                    </span>
                                    <span class="price">
                                        <?php if ($material['price'] > 0): ?>
                                            KES <?php echo number_format($material['price'], 2); ?>
                                        <?php else: ?>
                                            FREE
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <a href="material.php?id=<?php echo $material['id']; ?>" class="btn" style="flex: 1;">Preview</a>
                                    <?php if ($material['price'] > 0): ?>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="purchase.php?material_id=<?php echo $material['id']; ?>" class="btn btn-accent" style="flex: 1;">Purchase</a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-accent" style="flex: 1;">Login to Purchase</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="material.php?id=<?php echo $material['id']; ?>" class="btn btn-accent" style="flex: 1;">Get Free</a>
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

        <!-- About Section -->
        <section id="about" style="padding: 3rem 0; background: #f8fafc;">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h2>About KNP LRAPP</h2>
                        <p>Learn about our mission and values</p>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                        <div style="flex: 1; min-width: 300px;">
                            <h3>Our Mission</h3>
                            <p>
                                KNP LRAPP is dedicated to providing high-quality educational resources to learners 
                                around the world. We believe that education should be accessible to everyone, 
                                regardless of their background or financial situation.
                            </p>
                            <p>
                                Our platform offers a wide range of materials, from free introductory resources 
                                to comprehensive premium courses, ensuring that every learner can find content 
                                that matches their needs and goals.
                            </p>
                        </div>
                        
                        <div style="flex: 1; min-width: 300px;">
                            <h3>Why Choose Us?</h3>
                            <ul style="list-style: none; padding: 0;">
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Expert Instructors</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Learn from industry professionals with real-world experience</p>
                                    </div>
                                </li>
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Flexible Learning</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Access materials anytime, anywhere on any device</p>
                                    </div>
                                </li>
                                <li style="margin-bottom: 1rem; display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Affordable Options</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Free resources and premium content at competitive prices</p>
                                    </div>
                                </li>
                                <li style="display: flex; align-items: flex-start;">
                                    <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                    <div>
                                        <strong>Community Support</strong>
                                        <p style="margin: 0.25rem 0 0; color: #666;">Connect with other learners and get help when you need it</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section style="padding: 3rem 0; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; text-align: center;">
            <div class="container">
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Ready to Start Learning?</h2>
                <p style="font-size: 1.1rem; max-width: 700px; margin: 0 auto 2rem;">
                    Join thousands of learners who have transformed their careers with our resources. 
                    Create a free account to get started today.
                </p>
                <div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
                    <a href="register.php" class="btn" style="background: white; color: var(--primary-color); font-weight: bold; padding: 0.75rem 2rem;">Create Free Account</a>
                    <a href="#departments" class="btn btn-secondary" style="padding: 0.75rem 2rem;">Browse Materials</a>
                </div>
            </div>
        </section>
    </main>

    <footer id="contact" class="footer mt-auto py-3 bg-dark text-light">
        <div class="container">
            <div style="display: flex; flex-wrap: wrap; gap: 2rem; padding: 2rem 0;">
                <div style="flex: 1; min-width: 250px;">
                    <h3><?php echo APP_NAME; ?></h3>
                    <p style="color: #ccc;">
                        Providing quality educational resources to learners worldwide since 2023.
                    </p>
                </div>
                
                <div style="flex: 1; min-width: 250px;">
                    <h4>Quick Links</h4>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;"><a href="index.php" style="color: #ccc; text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#departments" style="color: #ccc; text-decoration: none;">Departments</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#programs" style="color: #ccc; text-decoration: none;">Programs</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#materials" style="color: #ccc; text-decoration: none;">Materials</a></li>
                        <li style="margin-bottom: 0.5rem;"><a href="#about" style="color: #ccc; text-decoration: none;">About</a></li>
                    </ul>
                </div>
                
                <div style="flex: 1; min-width: 250px;">
                    <h4>Contact Us</h4>
                    <p style="color: #ccc;">
                        Email: info@knplrapp.com<br>
                        Phone: +254 700 000 000<br>
                        Address: Nairobi, Kenya
                    </p>
                </div>
            </div>
            
            <div style="border-top: 1px solid #444; padding: 1rem 0; text-align: center; color: #aaa;">
                <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
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
        
        .hero {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        
        .department-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .department-card:hover {
            transform: translateY(-5px);
        }
        
        .department-card-content {
            padding: 1rem;
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
        
        .category {
            color: var(--accent-color);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .material-type {
            color: var(--accent-color);
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .price {
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .price.free {
            color: var(--secondary-color);
        }
        
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</body>
</html>