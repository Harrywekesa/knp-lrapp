<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all assignments for trainer's courses
$allAssignments = [];
foreach ($courses as $course) {
    $courseAssignments = getAssignmentsByCourse($course['id']);
    foreach ($courseAssignments as $assignment) {
        $assignment['course_name'] = $course['name'];
        $allAssignments[] = $assignment;
    }
}

// Separate active and past assignments
$activeAssignments = [];
$pastAssignments = [];

foreach ($allAssignments as $assignment) {
    if (strtotime($assignment['due_date']) > time()) {
        $activeAssignments[] = $assignment;
    } else {
        $pastAssignments[] = $assignment;
    }
}

// Sort assignments by due date
usort($activeAssignments, function($a, $b) {
    return strtotime($a['due_date']) - strtotime($b['due_date']);
});

usort($pastAssignments, function($a, $b) {
    return strtotime($b['due_date']) - strtotime($a['due_date']);
});

// Handle assignment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_assignment'])) {
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        $max_points = $_POST['max_points'];
        
        // Handle file upload
        $file_path = null;
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/assignments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
                $file_path = 'uploads/assignments/' . $filename;
            }
        }
        
        if (createAssignment($course_id, $title, $description, $due_date, $max_points, $file_path)) {
            $success = "Assignment created successfully";
            // Refresh assignments
            $allAssignments = [];
            foreach ($courses as $course) {
                $courseAssignments = getAssignmentsByCourse($course['id']);
                foreach ($courseAssignments as $assignment) {
                    $assignment['course_name'] = $course['name'];
                    $allAssignments[] = $assignment;
                }
            }
            
            // Separate active and past assignments
            $activeAssignments = [];
            $pastAssignments = [];
            
            foreach ($allAssignments as $assignment) {
                if (strtotime($assignment['due_date']) > time()) {
                    $activeAssignments[] = $assignment;
                } else {
                    $pastAssignments[] = $assignment;
                }
            }
            
            // Sort assignments by due date
            usort($activeAssignments, function($a, $b) {
                return strtotime($a['due_date']) - strtotime($b['due_date']);
            });
            
            usort($pastAssignments, function($a, $b) {
                return strtotime($b['due_date']) - strtotime($a['due_date']);
            });
        } else {
            $error = "Failed to create assignment";
        }
    } elseif (isset($_POST['update_assignment'])) {
        $id = $_POST['assignment_id'];
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        $max_points = $_POST['max_points'];
        
        // Handle file upload
        $file_path = null;
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/assignments/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $target_file)) {
                $file_path = 'uploads/assignments/' . $filename;
            }
        }
        
        if (updateAssignment($id, $title, $description, $due_date, $max_points, $file_path)) {
            $success = "Assignment updated successfully";
            // Refresh assignments
            $allAssignments = [];
            foreach ($courses as $course) {
                $courseAssignments = getAssignmentsByCourse($course['id']);
                foreach ($courseAssignments as $assignment) {
                    $assignment['course_name'] = $course['name'];
                    $allAssignments[] = $assignment;
                }
            }
            
            // Separate active and past assignments
            $activeAssignments = [];
            $pastAssignments = [];
            
            foreach ($allAssignments as $assignment) {
                if (strtotime($assignment['due_date']) > time()) {
                    $activeAssignments[] = $assignment;
                } else {
                    $pastAssignments[] = $assignment;
                }
            }
            
            // Sort assignments by due date
            usort($activeAssignments, function($a, $b) {
                return strtotime($a['due_date']) - strtotime($b['due_date']);
            });
            
            usort($pastAssignments, function($a, $b) {
                return strtotime($b['due_date']) - strtotime($a['due_date']);
            });
        } else {
            $error = "Failed to update assignment";
        }
    } elseif (isset($_POST['delete_assignment'])) {
        $id = $_POST['assignment_id'];
        
        if (deleteAssignment($id)) {
            $success = "Assignment deleted successfully";
            // Refresh assignments
            $allAssignments = [];
            foreach ($courses as $course) {
                $courseAssignments = getAssignmentsByCourse($course['id']);
                foreach ($courseAssignments as $assignment) {
                    $assignment['course_name'] = $course['name'];
                    $allAssignments[] = $assignment;
                }
            }
            
            // Separate active and past assignments
            $activeAssignments = [];
            $pastAssignments = [];
            
            foreach ($allAssignments as $assignment) {
                if (strtotime($assignment['due_date']) > time()) {
                    $activeAssignments[] = $assignment;
                } else {
                    $pastAssignments[] = $assignment;
                }
            }
            
            // Sort assignments by due date
            usort($activeAssignments, function($a, $b) {
                return strtotime($a['due_date']) - strtotime($b['due_date']);
            });
            
            usort($pastAssignments, function($a, $b) {
                return strtotime($b['due_date']) - strtotime($a['due_date']);
            });
        } else {
            $error = "Failed to delete assignment";
        }
    }
}

// Group courses by program
$programCourses = [];
foreach ($courses as $course) {
    $programId = $course['program_id'];
    if (!isset($programCourses[$programId])) {
        $programCourses[$programId] = [
            'program' => getProgramById($programId),
            'courses' => []
        ];
    }
    $programCourses[$programId]['courses'][] = $course;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - <?php echo APP_NAME; ?></title>
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
                    <li><a href="assignments.php" class="active">Assignments</a></li>
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
                    <h2>Assignments</h2>
                    <p>Create and manage course assignments</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Assignment</button>
                
                <div id="create-assignment-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Assignment</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="course_id">Course *</label>
                            <select id="course_id" name="course_id" class="form-control" required>
                                <option value="">Select a course</option>
                                <?php foreach ($programCourses as $programData): ?>
                                    <?php if (!empty($programData['courses'])): ?>
                                        <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                            <?php foreach ($programData['courses'] as $course): ?>
                                            <option value="<?php echo $course['id']; ?>">
                                                <?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="title">Assignment Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="max_points">Maximum Points *</label>
                                    <input type="number" id="max_points" name="max_points" class="form-control" min="1" max="1000" value="100" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="due_date">Due Date *</label>
                                    <input type="datetime-local" id="due_date" name="due_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="assignment_file">Assignment File (Optional)</label>
                                    <input type="file" id="assignment_file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx,.txt">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_assignment" class="btn">Create Assignment</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search assignments...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Assignments (<?php echo count($allAssignments); ?>)</button>
                        <button class="tab-button" data-tab="active">Active (<?php echo count($activeAssignments); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Assignments (<?php echo count($pastAssignments); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($allAssignments)): ?>
                                <div class="alert">No assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php if (strtotime($assignment['due_date']) > time()): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Past</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (strtotime($assignment['due_date']) > time()): ?>
                                                        <button class="btn edit-assignment" 
                                                                data-id="<?php echo $assignment['id']; ?>" 
                                                                data-course="<?php echo $assignment['course_id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($assignment['title']); ?>" 
                                                                data-description="<?php echo htmlspecialchars($assignment['description'] ?? ''); ?>" 
                                                                data-due="<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>" 
                                                                data-points="<?php echo $assignment['max_points']; ?>" 
                                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                                            <button type="submit" name="delete_assignment" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="grade_assignments.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Grade</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="active" class="tab-pane">
                            <?php if (empty($activeAssignments)): ?>
                                <div class="alert">No active assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Left</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($activeAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php 
                                                    $daysLeft = ceil((strtotime($assignment['due_date']) - time()) / (60 * 60 * 24));
                                                    echo $daysLeft . ' days';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <button class="btn edit-assignment" 
                                                            data-id="<?php echo $assignment['id']; ?>" 
                                                            data-course="<?php echo $assignment['course_id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($assignment['title']); ?>" 
                                                            data-description="<?php echo htmlspecialchars($assignment['description'] ?? ''); ?>" 
                                                            data-due="<?php echo date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>" 
                                                            data-points="<?php echo $assignment['max_points']; ?>" 
                                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="assignment_id" value="<?php echo $assignment['id']; ?>">
                                                        <button type="submit" name="delete_assignment" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="past" class="tab-pane">
                            <?php if (empty($pastAssignments)): ?>
                                <div class="alert">No past assignments found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Due Date</th>
                                                <th>Max Points</th>
                                                <th>Days Overdue</th>
                                                <th>Submissions</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pastAssignments as $assignment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                                <td><?php echo $assignment['max_points']; ?></td>
                                                <td>
                                                    <?php 
                                                    $daysOverdue = floor((time() - strtotime($assignment['due_date'])) / (60 * 60 * 24));
                                                    echo $daysOverdue . ' days';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM assignment_submissions WHERE assignment_id = ?");
                                                    $stmt->execute([$assignment['id']]);
                                                    $submissionCount = $stmt->fetch()['count'];
                                                    echo $submissionCount;
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="grade_assignments.php?assignment_id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Grade</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Assignment Modal -->
    <div id="edit-assignment-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Assignment</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit-assignment-id" name="assignment_id">
                <div class="form-group">
                    <label for="edit-course-id">Course *</label>
                    <select id="edit-course-id" name="course_id" class="form-control" required>
                        <option value="">Select a course</option>
                        <?php foreach ($programCourses as $programData): ?>
                            <?php if (!empty($programData['courses'])): ?>
                                <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                    <?php foreach ($programData['courses'] as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-title">Assignment Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-max-points">Maximum Points *</label>
                            <input type="number" id="edit-max-points" name="max_points" class="form-control" min="1" max="1000" value="100" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-due-date">Due Date *</label>
                            <input type="datetime-local" id="edit-due-date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-assignment-file">Assignment File (Leave blank to keep current)</label>
                            <input type="file" id="edit-assignment-file" name="assignment_file" class="form-control" accept=".pdf,.doc,.docx,.txt">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_assignment" class="btn">Update Assignment</button>
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
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-assignment-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-assignment-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit assignment functionality
            const editButtons = document.querySelectorAll('.edit-assignment');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const course = this.getAttribute('data-course');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const due = this.getAttribute('data-due');
                    const points = this.getAttribute('data-points');
                    
                    document.getElementById('edit-assignment-id').value = id;
                    document.getElementById('edit-course-id').value = course;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-due-date').value = due;
                    document.getElementById('edit-max-points').value = points;
                    
                    editModal.style.display = 'flex';
                });
            });
            
            closeModalBtn.addEventListener('click', function() {
                editModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === editModal) {
                    editModal.style.display = 'none';
                }
            });
            
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