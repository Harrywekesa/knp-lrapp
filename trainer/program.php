<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get program ID from URL
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$program_id) {
    header('Location: courses.php');
    exit();
}

// Get program details
$program = getProgramById($program_id);
if (!$program) {
    header('Location: courses.php');
    exit();
}

// Get department details
$department = getDepartmentById($program['department_id']);

// Get units for this program
$units = getUnitsByProgram($program_id);

// Get courses assigned to this trainer for this program
$courses = getCoursesByTrainerAndProgram($user['id'], $program_id);

// Handle course assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_course'])) {
        $unit_id = $_POST['unit_id'];
        $course_title = $_POST['course_title'];
        $course_code = $_POST['course_code'];
        $description = $_POST['description'];
        $year = $_POST['year'];
        $semester = $_POST['semester'];
        $credits = $_POST['credits'];
        
        if (assignCourseToTrainer($user['id'], $unit_id, $course_title, $course_code, $description, $year, $semester, $credits)) {
            $success = "Course assigned successfully";
            // Refresh courses
            $courses = getCoursesByTrainerAndProgram($user['id'], $program_id);
        } else {
            $error = "Failed to assign course";
        }
    } elseif (isset($_POST['update_course'])) {
        $course_id = $_POST['course_id'];
        $course_title = $_POST['course_title'];
        $course_code = $_POST['course_code'];
        $description = $_POST['description'];
        $year = $_POST['year'];
        $semester = $_POST['semester'];
        $credits = $_POST['credits'];
        
        if (updateCourse($course_id, $course_title, $course_code, $description, $year, $semester, $credits)) {
            $success = "Course updated successfully";
            // Refresh courses
            $courses = getCoursesByTrainerAndProgram($user['id'], $program_id);
        } else {
            $error = "Failed to update course";
        }
    } elseif (isset($_POST['remove_course'])) {
        $course_id = $_POST['course_id'];
        
        if (removeTrainerFromCourse($course_id)) {
            $success = "Course removed successfully";
            // Refresh courses
            $courses = getCoursesByTrainerAndProgram($user['id'], $program_id);
        } else {
            $error = "Failed to remove course";
        }
    }
}

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
    <title><?php echo htmlspecialchars($program['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <h2><?php echo htmlspecialchars($program['name']); ?></h2>
                    <p><?php echo htmlspecialchars($program['code']); ?> - <?php echo $levelNames[$program['level']] ?? ucfirst($program['level']); ?></p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem; margin-bottom: 2rem;">
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Overview</h3>
                            </div>
                            <p><?php echo htmlspecialchars($program['description'] ?? 'No description available for this program.'); ?></p>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                                <div>
                                    <strong>Department:</strong>
                                    <p><?php echo htmlspecialchars($department['name'] ?? 'N/A'); ?></p>
                                </div>
                                <div>
                                    <strong>Level:</strong>
                                    <p><?php echo $levelNames[$program['level']] ?? ucfirst($program['level']); ?></p>
                                </div>
                                <div>
                                    <strong>Duration:</strong>
                                    <p><?php echo $program['duration']; ?> years</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>My Assigned Courses</h3>
                                <p>Courses you are teaching in this program</p>
                            </div>
                            
                            <?php if (empty($courses)): ?>
                                <div class="alert">You haven't been assigned any courses in this program yet.</div>
                                <div style="text-align: center; margin: 2rem 0;">
                                    <button id="show-assign-form" class="btn">Assign Yourself to a Course</button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Course</th>
                                                <th>Code</th>
                                                <th>Unit</th>
                                                <th>Year/Sem</th>
                                                <th>Credits</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courses as $course): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course['name']); ?></td>
                                                <td><?php echo htmlspecialchars($course['code']); ?></td>
                                                <td><?php echo htmlspecialchars($course['unit_name'] ?? 'N/A'); ?></td>
                                                <td>Y<?php echo $course['year']; ?>/S<?php echo $course['semester']; ?></td>
                                                <td><?php echo $course['credits']; ?></td>
                                                <td>
                                                    <?php if ($course['status'] === 'active'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn edit-course" 
                                                            data-id="<?php echo $course['id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($course['name']); ?>" 
                                                            data-code="<?php echo htmlspecialchars($course['code']); ?>" 
                                                            data-description="<?php echo htmlspecialchars($course['description'] ?? ''); ?>" 
                                                            data-year="<?php echo $course['year']; ?>" 
                                                            data-semester="<?php echo $course['semester']; ?>" 
                                                            data-credits="<?php echo $course['credits']; ?>" 
                                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                        <button type="submit" name="remove_course" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to remove yourself from this course?')">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Program Coordinator</h3>
                            </div>
                            <div style="text-align: center; padding: 1rem;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    <?php echo strtoupper(substr($program['trainer_name'] ?? 'T', 0, 1)); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($program['trainer_name'] ?? 'Not Assigned'); ?></h4>
                                <p style="margin: 0.25rem 0 0; color: #666;">Program Coordinator</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <a href="courses.php" class="btn btn-block">Back to Programs</a>
                                <a href="ebooks.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View E-Books</a>
                                <a href="live_classes.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View Live Classes</a>
                                <a href="assignments.php?program=<?php echo $program['id']; ?>" class="btn btn-block">View Assignments</a>
                                <a href="forum.php?program=<?php echo $program['id']; ?>" class="btn btn-block">Discussion Forum</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="assign-course-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Assign Yourself to a Course</h3>
                        <p>Select a unit to teach</p>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="unit_id">Select Unit *</label>
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select a unit</option>
                                <?php foreach ($units as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>">
                                    <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>) - 
                                    Year <?php echo $unit['year']; ?>, Semester <?php echo $unit['semester']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="course_title">Course Title *</label>
                                    <input type="text" id="course_title" name="course_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="course_code">Course Code *</label>
                                    <input type="text" id="course_code" name="course_code" class="form-control" required>
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
                            <button type="submit" name="assign_course" class="btn">Assign Course</button>
                            <button type="button" id="cancel-assign" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Program Units</h2>
                    <p>All units available in this program</p>
                </div>
                
                <?php if (empty($units)): ?>
                    <div class="alert">No units available for this program.</div>
                <?php else: ?>
                    <?php foreach ($unitStructure as $year => $semesters): ?>
                    <div class="card" style="margin-bottom: 2rem;">
                        <h3 style="color: var(--primary-color); margin-bottom: 1rem;">Year <?php echo $year; ?></h3>
                        
                        <?php foreach ($semesters as $semester => $semesterUnits): ?>
                        <div class="card" style="margin-bottom: 1.5rem; border-left: 3px solid var(--accent-color);">
                            <h4 style="margin: 0 0 1rem 0; color: var(--accent-color);">Semester <?php echo $semester; ?></h4>
                            
                            <?php if (empty($semesterUnits)): ?>
                                <div class="alert">No units available for this semester.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit Code</th>
                                                <th>Unit Name</th>
                                                <th>Credits</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($semesterUnits as $unit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($unit['code']); ?></td>
                                                <td><?php echo htmlspecialchars($unit['name']); ?></td>
                                                <td><?php echo $unit['credits']; ?></td>
                                                <td><?php echo htmlspecialchars(substr($unit['description'] ?? 'No description', 0, 80)) . '...'; ?></td>
                                                <td>
                                                    <?php if ($unit['status'] === 'active'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $isAssigned = false;
                                                    foreach ($courses as $course) {
                                                        if ($course['unit_id'] == $unit['id']) {
                                                            $isAssigned = true;
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                    <?php if ($isAssigned): ?>
                                                        <span class="badge badge-success">Assigned</span>
                                                    <?php else: ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                                                            <button type="submit" name="assign_unit" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Assign</button>
                                                        </form>
                                                    <?php endif; ?>
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
        </div>
    </main>

    <!-- Edit Course Modal -->
    <div id="edit-course-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Course</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-course-id" name="course_id">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-course-title">Course Title *</label>
                            <input type="text" id="edit-course-title" name="course_title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-course-code">Course Code *</label>
                            <input type="text" id="edit-course-code" name="course_code" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-year">Year *</label>
                            <select id="edit-year" name="year" class="form-control" required>
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
                            <label for="edit-semester">Semester *</label>
                            <select id="edit-semester" name="semester" class="form-control" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-credits">Credits *</label>
                            <input type="number" id="edit-credits" name="credits" class="form-control" min="1" max="20" value="3" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_course" class="btn">Update Course</button>
                    <button type="button" id="close-modal-edit" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

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
        
        .modal {
            display: none;
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
            const showFormBtn = document.getElementById('show-assign-form');
            const assignForm = document.getElementById('assign-course-form');
            const cancelBtn = document.getElementById('cancel-assign');
            const editModal = document.getElementById('edit-course-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            if (showFormBtn) {
                showFormBtn.addEventListener('click', function() {
                    assignForm.style.display = 'block';
                    showFormBtn.style.display = 'none';
                });
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    assignForm.style.display = 'none';
                    if (showFormBtn) {
                        showFormBtn.style.display = 'inline-block';
                    }
                });
            }
            
            // Edit course functionality
            const editButtons = document.querySelectorAll('.edit-course');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const code = this.getAttribute('data-code');
                    const description = this.getAttribute('data-description');
                    const year = this.getAttribute('data-year');
                    const semester = this.getAttribute('data-semester');
                    const credits = this.getAttribute('data-credits');
                    
                    document.getElementById('edit-course-id').value = id;
                    document.getElementById('edit-course-title').value = title;
                    document.getElementById('edit-course-code').value = code;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-year').value = year;
                    document.getElementById('edit-semester').value = semester;
                    document.getElementById('edit-credits').value = credits;
                    
                    editModal.style.display = 'flex';
                });
            });
            
            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    editModal.style.display = 'none';
                });
            }
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === editModal) {
                    editModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>