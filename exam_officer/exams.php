<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle exam actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_exam'])) {
        $unit_id = $_POST['unit_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $exam_date = $_POST['exam_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $max_points = $_POST['max_points'];
        $exam_type = $_POST['exam_type'];
        
        if (createExam($unit_id, $title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type)) {
            $success = "Exam created successfully";
        } else {
            $error = "Failed to create exam";
        }
    } elseif (isset($_POST['update_exam'])) {
        $id = $_POST['exam_id'];
        $unit_id = $_POST['unit_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $exam_date = $_POST['exam_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $max_points = $_POST['max_points'];
        $exam_type = $_POST['exam_type'];
        
        if (updateExam($id, $title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type)) {
            $success = "Exam updated successfully";
        } else {
            $error = "Failed to update exam";
        }
    } elseif (isset($_POST['delete_exam'])) {
        $id = $_POST['exam_id'];
        
        if (deleteExam($id)) {
            $success = "Exam deleted successfully";
        } else {
            $error = "Failed to delete exam";
        }
    }
}

// Get exams
$exams = getAllExams();
$units = getAllUnits();

// Get upcoming exams
$upcomingExams = array_filter($exams, function($exam) {
    return strtotime($exam['exam_date'] . ' ' . $exam['start_time']) > time();
});

// Get past exams
$pastExams = array_filter($exams, function($exam) {
    return strtotime($exam['exam_date'] . ' ' . $exam['start_time']) <= time();
});

// Sort exams by date
usort($upcomingExams, function($a, $b) {
    return strtotime($a['exam_date'] . ' ' . $a['start_time']) - strtotime($b['exam_date'] . ' ' . $b['start_time']);
});

usort($pastExams, function($a, $b) {
    return strtotime($b['exam_date'] . ' ' . $b['start_time']) - strtotime($a['exam_date'] . ' ' . $a['start_time']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management - <?php echo APP_NAME; ?></title>
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
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="exams.php" class="active">Exams</a></li>
                    <li><a href="results.php">Results</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="students.php">Students</a></li>
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
                    <h2>Exam Management</h2>
                    <p>Create and manage examinations</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Exam</button>
                
                <div id="create-exam-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Exam</h3>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="unit_id">Unit *</label>
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select a unit</option>
                                <?php foreach ($units as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>">
                                    <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="title">Exam Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="exam_type">Exam Type *</label>
                                    <select id="exam_type" name="exam_type" class="form-control" required>
                                        <option value="midterm">Midterm Exam</option>
                                        <option value="final">Final Exam</option>
                                        <option value="practical">Practical Exam</option>
                                        <option value="quiz">Quiz</option>
                                        <option value="assignment">Assignment</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="exam_date">Exam Date *</label>
                                    <input type="date" id="exam_date" name="exam_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="start_time">Start Time *</label>
                                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="end_time">End Time *</label>
                                    <input type="time" id="end_time" name="end_time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="max_points">Maximum Points *</label>
                                    <input type="number" id="max_points" name="max_points" class="form-control" min="1" max="1000" value="100" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_exam" class="btn">Create Exam</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="upcoming">Upcoming Exams (<?php echo count($upcomingExams); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Exams (<?php echo count($pastExams); ?>)</button>
                        <button class="tab-button" data-tab="all">All Exams (<?php echo count($exams); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="upcoming" class="tab-pane active">
                            <?php if (empty($upcomingExams)): ?>
                                <div class="alert">No upcoming exams scheduled.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($upcomingExams as $exam): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                <td><?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y g:i A', strtotime($exam['exam_date'] . ' ' . $exam['start_time'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($exam['exam_date'] . ' ' . $exam['end_time'])); ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    switch($exam['exam_type']) {
                                                        case 'midterm': echo 'Midterm'; break;
                                                        case 'final': echo 'Final'; break;
                                                        case 'practical': echo 'Practical'; break;
                                                        case 'quiz': echo 'Quiz'; break;
                                                        case 'assignment': echo 'Assignment'; break;
                                                        default: echo ucfirst($exam['exam_type']); break;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $exam['max_points']; ?></td>
                                                <td>
                                                    <?php if (strtotime($exam['exam_date'] . ' ' . $exam['start_time']) > time()): ?>
                                                        <span class="badge badge-success">Scheduled</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">In Progress</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button class="btn edit-exam" 
                                                            data-id="<?php echo $exam['id']; ?>" 
                                                            data-unit="<?php echo $exam['unit_id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($exam['title']); ?>" 
                                                            data-description="<?php echo htmlspecialchars($exam['description'] ?? ''); ?>" 
                                                            data-date="<?php echo $exam['exam_date']; ?>" 
                                                            data-start="<?php echo $exam['start_time']; ?>" 
                                                            data-end="<?php echo $exam['end_time']; ?>" 
                                                            data-points="<?php echo $exam['max_points']; ?>" 
                                                            data-type="<?php echo $exam['exam_type']; ?>" 
                                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                                        <button type="submit" name="delete_exam" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this exam?')">Delete</button>
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
                            <?php if (empty($pastExams)): ?>
                                <div class="alert">No past exams found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pastExams as $exam): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                <td><?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y g:i A', strtotime($exam['exam_date'] . ' ' . $exam['start_time'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($exam['exam_date'] . ' ' . $exam['end_time'])); ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    switch($exam['exam_type']) {
                                                        case 'midterm': echo 'Midterm'; break;
                                                        case 'final': echo 'Final'; break;
                                                        case 'practical': echo 'Practical'; break;
                                                        case 'quiz': echo 'Quiz'; break;
                                                        case 'assignment': echo 'Assignment'; break;
                                                        default: echo ucfirst($exam['exam_type']); break;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $exam['max_points']; ?></td>
                                                <td>
                                                    <span class="badge badge-secondary">Completed</span>
                                                </td>
                                                <td>
                                                    <a href="exam_results.php?exam_id=<?php echo $exam['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Results</a>
                                                    <a href="grade_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Grade</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="all" class="tab-pane">
                            <?php if (empty($exams)): ?>
                                <div class="alert">No exams found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Unit</th>
                                                <th>Date & Time</th>
                                                <th>Type</th>
                                                <th>Max Points</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($exams as $exam): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($exam['title']); ?></td>
                                                <td><?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <?php echo date('M j, Y g:i A', strtotime($exam['exam_date'] . ' ' . $exam['start_time'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($exam['exam_date'] . ' ' . $exam['end_time'])); ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    switch($exam['exam_type']) {
                                                        case 'midterm': echo 'Midterm'; break;
                                                        case 'final': echo 'Final'; break;
                                                        case 'practical': echo 'Practical'; break;
                                                        case 'quiz': echo 'Quiz'; break;
                                                        case 'assignment': echo 'Assignment'; break;
                                                        default: echo ucfirst($exam['exam_type']); break;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $exam['max_points']; ?></td>
                                                <td>
                                                    <?php if (strtotime($exam['exam_date'] . ' ' . $exam['start_time']) > time()): ?>
                                                        <span class="badge badge-success">Scheduled</span>
                                                    <?php elseif (strtotime($exam['exam_date'] . ' ' . $exam['end_time']) > time()): ?>
                                                        <span class="badge badge-warning">In Progress</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (strtotime($exam['exam_date'] . ' ' . $exam['end_time']) > time()): ?>
                                                        <button class="btn edit-exam" 
                                                                data-id="<?php echo $exam['id']; ?>" 
                                                                data-unit="<?php echo $exam['unit_id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($exam['title']); ?>" 
                                                                data-description="<?php echo htmlspecialchars($exam['description'] ?? ''); ?>" 
                                                                data-date="<?php echo $exam['exam_date']; ?>" 
                                                                data-start="<?php echo $exam['start_time']; ?>" 
                                                                data-end="<?php echo $exam['end_time']; ?>" 
                                                                data-points="<?php echo $exam['max_points']; ?>" 
                                                                data-type="<?php echo $exam['exam_type']; ?>" 
                                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">
                                                            <button type="submit" name="delete_exam" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this exam?')">Delete</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="exam_results.php?exam_id=<?php echo $exam['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Results</a>
                                                        <a href="grade_exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;">Grade</a>
                                                    <?php endif; ?>
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

    <!-- Edit Exam Modal -->
    <div id="edit-exam-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Exam</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-exam-id" name="exam_id">
                <div class="form-group">
                    <label for="edit-unit-id">Unit *</label>
                    <select id="edit-unit-id" name="unit_id" class="form-control" required>
                        <option value="">Select a unit</option>
                        <?php foreach ($units as $unit): ?>
                        <option value="<?php echo $unit['id']; ?>">
                            <?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-title">Exam Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-exam-type">Exam Type *</label>
                            <select id="edit-exam-type" name="exam_type" class="form-control" required>
                                <option value="midterm">Midterm Exam</option>
                                <option value="final">Final Exam</option>
                                <option value="practical">Practical Exam</option>
                                <option value="quiz">Quiz</option>
                                <option value="assignment">Assignment</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-exam-date">Exam Date *</label>
                            <input type="date" id="edit-exam-date" name="exam_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-start-time">Start Time *</label>
                            <input type="time" id="edit-start-time" name="start_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-end-time">End Time *</label>
                            <input type="time" id="edit-end-time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-max-points">Maximum Points *</label>
                            <input type="number" id="edit-max-points" name="max_points" class="form-control" min="1" max="1000" value="100" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-description">Description</label>
                            <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_exam" class="btn">Update Exam</button>
                    <button type="button" id="close-modal" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
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
            const createForm = document.getElementById('create-exam-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-exam-modal');
            const closeModalBtn = document.getElementById('close-modal');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit exam functionality
            const editButtons = document.querySelectorAll('.edit-exam');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const unit = this.getAttribute('data-unit');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const date = this.getAttribute('data-date');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');
                    const points = this.getAttribute('data-points');
                    const type = this.getAttribute('data-type');
                    
                    document.getElementById('edit-exam-id').value = id;
                    document.getElementById('edit-unit-id').value = unit;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-exam-date').value = date;
                    document.getElementById('edit-start-time').value = start;
                    document.getElementById('edit-end-time').value = end;
                    document.getElementById('edit-max-points').value = points;
                    document.getElementById('edit-exam-type').value = type;
                    
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