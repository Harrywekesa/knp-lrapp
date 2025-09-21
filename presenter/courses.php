<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all programs for reference
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
    <title>My Courses - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="courses.php" class="active">My Courses</a></li>
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
                    <h2>My Courses</h2>
                    <p>Courses you are teaching</p>
                </div>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Course</button>
                
                <div id="create-course-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Course</h3>
                    </div>
                    <form method="POST" action="create_course.php">
                        <div class="form-group">
                            <label for="program_id">Program *</label>
                            <select id="program_id" name="program_id" class="form-control" required>
                                <option value="">Select a program</option>
                                <?php foreach ($allPrograms as $program): ?>
                                <option value="<?php echo $program['id']; ?>">
                                    <?php echo htmlspecialchars($program['name']); ?> 
                                    (<?php echo htmlspecialchars($program['department_name']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Course Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="code">Course Code *</label>
                                    <input type="text" id="code" name="code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="year">Year *</label>
                                    <select id="year" name="year" class="form-control" required>
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                        <option value="4">Year 4</option>
                                        <option value="5">Year 5</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="semester">Semester *</label>
                                    <select id="semester" name="semester" class="form-control" required>
                                        <option value="1">Semester 1</option>
                                        <option value="2">Semester 2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="credits">Credits *</label>
                                    <input type="number" id="credits" name="credits" class="form-control" min="1" max="20" value="3" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_course" class="btn">Create Course</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <?php if (empty($courses)): ?>
                    <div class="alert">You haven't created any courses yet.</div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                <?php echo htmlspecialchars($course['code'] ?? 'COURSE'); ?>
                            </div>
                            <div class="course-card-content">
                                <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($course['description'] ?? 'No description available', 0, 100)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span>Year <?php echo $course['year']; ?>, Sem <?php echo $course['semester']; ?></span>
                                    <span><?php echo $course['credits']; ?> credits</span>
                                </div>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="btn" style="flex: 1;">View Details</a>
                                    <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-secondary" style="flex: 1;">Edit</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>All Academic Programs</h2>
                    <p>Browse programs across all departments</p>
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
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }
        
        .form-col {
            flex: 1;
            padding: 0 0.5rem;
            min-width: 200px;
        }
        
        @media (max-width: 768px) {
            .form-col {
                min-width: 100%;
                margin-bottom: 1rem;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-course-form');
            const cancelBtn = document.getElementById('cancel-create');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
        });
    </script>
</body>
</html>