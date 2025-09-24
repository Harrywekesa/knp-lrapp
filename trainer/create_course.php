<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get all departments and programs
$departments = getAllDepartments();

// Group programs by department
$departmentPrograms = [];
foreach ($departments as $department) {
    $deptId = $department['id'];
    $programs = getProgramsByDepartment($deptId);
    if (!empty($programs)) {
        $departmentPrograms[$deptId] = [
            'department' => $department,
            'programs' => $programs
        ];
    }
}

// Handle course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_course'])) {
       $unit_id = $_POST['unit_id'] ?? null;
if (!$unit_id) {
    $error = "Please select a unit before creating a course.";
}

        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $year = $_POST['year'];
        $semester = $_POST['semester'];
        $credits = $_POST['credits'];
        
        if (createCourse($unit_id, $user['id'], $name, $code, $description, $year, $semester, $credits)) {
            $success = "Course created successfully";
            // Redirect to courses page
            header('Location: courses.php');
            exit();
        } else {
            $error = "Failed to create course";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - <?php echo APP_NAME; ?></title>
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
                    <h2>Create New Course</h2>
                    <p>Select a unit to create a course for</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Select Academic Unit</h3>
                        <p>Browse through the academic structure to select a unit</p>
                    </div>
                    
                    <?php if (empty($departmentPrograms)): ?>
                        <div class="alert">No departments or programs available. Please contact administrator.</div>
                    <?php else: ?>
                        <?php foreach ($departmentPrograms as $deptId => $deptData): ?>
                        <div class="card" style="margin-bottom: 2rem;">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                                <?php echo htmlspecialchars($deptData['department']['name']); ?>
                            </h3>
                            
                            <?php foreach ($deptData['programs'] as $program): ?>
                            <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                                <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);">
                                    <?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['code']); ?>)
                                </h4>
                                
                                <?php 
                                $programUnits = getUnitsByProgram($program['id']);
                                if (empty($programUnits)):
                                ?>
                                    <div class="alert">No units available for this program yet.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit Code</th>
                                                    <th>Unit Name</th>
                                                    <th>Year/Semester</th>
                                                    <th>Credits</th>
                                                    <th>Description</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($programUnits as $unit): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($unit['code']); ?></td>
                                                    <td><?php echo htmlspecialchars($unit['name']); ?></td>
                                                    <td>Y<?php echo $unit['year']; ?>/S<?php echo $unit['semester']; ?></td>
                                                    <td><?php echo $unit['credits']; ?></td>
                                                    <td><?php echo htmlspecialchars(substr($unit['description'] ?? 'No description', 0, 80)) . '...'; ?></td>
                                                    <td>
                                                        <button class="btn select-unit" 
                                                                data-id="<?php echo $unit['id']; ?>" 
                                                                data-name="<?php echo htmlspecialchars($unit['name']); ?>" 
                                                                data-code="<?php echo htmlspecialchars($unit['code']); ?>" 
                                                                data-description="<?php echo htmlspecialchars($unit['description'] ?? ''); ?>" 
                                                                data-year="<?php echo $unit['year']; ?>" 
                                                                data-semester="<?php echo $unit['semester']; ?>" 
                                                                data-credits="<?php echo $unit['credits']; ?>" 
                                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Select Unit</button>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div id="create-course-form" class="card" style="display: none; margin-top: 1.5rem;">
                    <div class="card-header">
                        <h3>Create Course for Selected Unit</h3>
                    </div>
                    <form method="POST">
                        <input type="hidden" id="unit_id" name="unit_id">
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="unit_name">Selected Unit *</label>
                                    <input type="text" id="unit_name" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="unit_code">Unit Code *</label>
                                    <input type="text" id="unit_code" class="form-control" readonly>
                                </div>
                            </div>
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
            min-width: 250px;
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
            const selectUnitButtons = document.querySelectorAll('.select-unit');
            const createForm = document.getElementById('create-course-form');
            const cancelBtn = document.getElementById('cancel-create');
            
            selectUnitButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const description = this.getAttribute('data-description');
                    const year = this.getAttribute('data-year');
                    const semester = this.getAttribute('data-semester');
                    const credits = this.getAttribute('data-credits');
                    
                    document.getElementById('unit_id').value = id;
                    document.getElementById('unit_name').value = name;
                    document.getElementById('unit_code').value = code;
                    document.getElementById('name').value = name;
                    document.getElementById('code').value = code;
                    document.getElementById('description').value = description;
                    document.getElementById('year').value = year;
                    document.getElementById('semester').value = semester;
                    document.getElementById('credits').value = credits;
                    
                    createForm.style.display = 'block';
                    window.scrollTo({ top: createForm.offsetTop, behavior: 'smooth' });
                });
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
            });
        });
    </script>
</body>
</html>