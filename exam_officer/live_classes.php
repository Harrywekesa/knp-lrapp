<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all classes for trainer's courses
$allClasses = [];
foreach ($courses as $course) {
    $courseClasses = getClassesByCourse($course['id']);
    foreach ($courseClasses as $class) {
        $class['course_name'] = $course['name'];
        $allClasses[] = $class;
    }
}

// Separate upcoming and past classes
$upcomingClasses = [];
$pastClasses = [];

foreach ($allClasses as $class) {
    if (strtotime($class['start_time']) > time()) {
        $upcomingClasses[] = $class;
    } else {
        $pastClasses[] = $class;
    }
}

// Sort classes by date
usort($upcomingClasses, function($a, $b) {
    return strtotime($a['start_time']) - strtotime($b['start_time']);
});

usort($pastClasses, function($a, $b) {
    return strtotime($b['start_time']) - strtotime($a['start_time']);
});

// Handle class actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_class'])) {
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $meeting_link = $_POST['meeting_link'];
        
        if (createClass($course_id, $title, $description, $start_time, $end_time, $meeting_link)) {
            $success = "Class scheduled successfully";
            // Refresh classes
            $allClasses = [];
            foreach ($courses as $course) {
                $courseClasses = getClassesByCourse($course['id']);
                foreach ($courseClasses as $class) {
                    $class['course_name'] = $course['name'];
                    $allClasses[] = $class;
                }
            }
            
            // Separate upcoming and past classes
            $upcomingClasses = [];
            $pastClasses = [];
            
            foreach ($allClasses as $class) {
                if (strtotime($class['start_time']) > time()) {
                    $upcomingClasses[] = $class;
                } else {
                    $pastClasses[] = $class;
                }
            }
            
            // Sort classes by date
            usort($upcomingClasses, function($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
            
            usort($pastClasses, function($a, $b) {
                return strtotime($b['start_time']) - strtotime($a['start_time']);
            });
        } else {
            $error = "Failed to schedule class";
        }
    } elseif (isset($_POST['update_class'])) {
        $id = $_POST['class_id'];
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $meeting_link = $_POST['meeting_link'];
        
        if (updateClass($id, $title, $description, $start_time, $end_time, $meeting_link)) {
            $success = "Class updated successfully";
            // Refresh classes
            $allClasses = [];
            foreach ($courses as $course) {
                $courseClasses = getClassesByCourse($course['id']);
                foreach ($courseClasses as $class) {
                    $class['course_name'] = $course['name'];
                    $allClasses[] = $class;
                }
            }
            
            // Separate upcoming and past classes
            $upcomingClasses = [];
            $pastClasses = [];
            
            foreach ($allClasses as $class) {
                if (strtotime($class['start_time']) > time()) {
                    $upcomingClasses[] = $class;
                } else {
                    $pastClasses[] = $class;
                }
            }
            
            // Sort classes by date
            usort($upcomingClasses, function($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
            
            usort($pastClasses, function($a, $b) {
                return strtotime($b['start_time']) - strtotime($a['start_time']);
            });
        } else {
            $error = "Failed to update class";
        }
    } elseif (isset($_POST['delete_class'])) {
        $id = $_POST['class_id'];
        
        if (deleteClass($id)) {
            $success = "Class deleted successfully";
            // Refresh classes
            $allClasses = [];
            foreach ($courses as $course) {
                $courseClasses = getClassesByCourse($course['id']);
                foreach ($courseClasses as $class) {
                    $class['course_name'] = $course['name'];
                    $allClasses[] = $class;
                }
            }
            
            // Separate upcoming and past classes
            $upcomingClasses = [];
            $pastClasses = [];
            
            foreach ($allClasses as $class) {
                if (strtotime($class['start_time']) > time()) {
                    $upcomingClasses[] = $class;
                } else {
                    $pastClasses[] = $class;
                }
            }
            
            // Sort classes by date
            usort($upcomingClasses, function($a, $b) {
                return strtotime($a['start_time']) - strtotime($b['start_time']);
            });
            
            usort($pastClasses, function($a, $b) {
                return strtotime($b['start_time']) - strtotime($a['start_time']);
            });
        } else {
            $error = "Failed to delete class";
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
    <title>Live Classes - <?php echo APP_NAME; ?></title>
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
                    <li><a href="live_classes.php" class="active">Live Classes</a></li>
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
                    <h2>Live Classes</h2>
                    <p>Schedule and manage your live sessions</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-schedule-form" class="btn" style="margin-bottom: 1.5rem;">Schedule New Class</button>
                
                <div id="schedule-class-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Schedule New Class</h3>
                    </div>
                    <form method="POST">
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
                                    <label for="title">Class Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="meeting_link">Meeting Link *</label>
                                    <input type="url" id="meeting_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="start_time">Start Time *</label>
                                    <input type="datetime-local" id="start_time" name="start_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="end_time">End Time *</label>
                                    <input type="datetime-local" id="end_time" name="end_time" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_class" class="btn">Schedule Class</button>
                            <button type="button" id="cancel-schedule" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search classes...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Classes (<?php echo count($allClasses); ?>)</button>
                        <button class="tab-button" data-tab="upcoming">Upcoming (<?php echo count($upcomingClasses); ?>)</button>
                        <button class="tab-button" data-tab="past">Past Classes (<?php echo count($pastClasses); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($allClasses)): ?>
                                <div class="alert">No classes found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Date & Time</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allClasses as $class): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($class['title']); ?></td>
                                                <td><?php echo htmlspecialchars($class['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $start = strtotime($class['start_time']);
                                                    $end = strtotime($class['end_time']);
                                                    $duration = ($end - $start) / 60; // in minutes
                                                    echo $duration . ' mins';
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (strtotime($class['start_time']) > time()): ?>
                                                        <span class="badge badge-success">Scheduled</span>
                                                    <?php elseif (strtotime($class['end_time']) > time()): ?>
                                                        <span class="badge badge-warning">In Progress</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (strtotime($class['start_time']) > time()): ?>
                                                        <button class="btn edit-class" 
                                                                data-id="<?php echo $class['id']; ?>" 
                                                                data-course="<?php echo $class['course_id']; ?>" 
                                                                data-title="<?php echo htmlspecialchars($class['title']); ?>" 
                                                                data-description="<?php echo htmlspecialchars($class['description'] ?? ''); ?>" 
                                                                data-start="<?php echo date('Y-m-d\TH:i', strtotime($class['start_time'])); ?>" 
                                                                data-end="<?php echo date('Y-m-d\TH:i', strtotime($class['end_time'])); ?>" 
                                                                data-link="<?php echo htmlspecialchars($class['meeting_link']); ?>" 
                                                                style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                            <button type="submit" name="delete_class" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this class?')">Delete</button>
                                                        </form>
                                                    <?php else: ?>
                                                        <a href="class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="upcoming" class="tab-pane">
                            <?php if (empty($upcomingClasses)): ?>
                                <div class="alert">No upcoming classes scheduled.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Date & Time</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($upcomingClasses as $class): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($class['title']); ?></td>
                                                <td><?php echo htmlspecialchars($class['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $start = strtotime($class['start_time']);
                                                    $end = strtotime($class['end_time']);
                                                    $duration = ($end - $start) / 60; // in minutes
                                                    echo $duration . ' mins';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">Scheduled</span>
                                                </td>
                                                <td>
                                                    <button class="btn edit-class" 
                                                            data-id="<?php echo $class['id']; ?>" 
                                                            data-course="<?php echo $class['course_id']; ?>" 
                                                            data-title="<?php echo htmlspecialchars($class['title']); ?>" 
                                                            data-description="<?php echo htmlspecialchars($class['description'] ?? ''); ?>" 
                                                            data-start="<?php echo date('Y-m-d\TH:i', strtotime($class['start_time'])); ?>" 
                                                            data-end="<?php echo date('Y-m-d\TH:i', strtotime($class['end_time'])); ?>" 
                                                            data-link="<?php echo htmlspecialchars($class['meeting_link']); ?>" 
                                                            style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                        <button type="submit" name="delete_class" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this class?')">Delete</button>
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
                            <?php if (empty($pastClasses)): ?>
                                <div class="alert">No past classes found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Course</th>
                                                <th>Date & Time</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pastClasses as $class): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($class['title']); ?></td>
                                                <td><?php echo htmlspecialchars($class['course_name']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($class['start_time'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $start = strtotime($class['start_time']);
                                                    $end = strtotime($class['end_time']);
                                                    $duration = ($end - $start) / 60; // in minutes
                                                    echo $duration . ' mins';
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">Completed</span>
                                                </td>
                                                <td>
                                                    <a href="class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
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

    <!-- Edit Class Modal -->
    <div id="edit-class-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Class</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-class-id" name="class_id">
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
                            <label for="edit-title">Class Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-meeting-link">Meeting Link *</label>
                            <input type="url" id="edit-meeting-link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-start-time">Start Time *</label>
                            <input type="datetime-local" id="edit-start-time" name="start_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-end-time">End Time *</label>
                            <input type="datetime-local" id="edit-end-time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_class" class="btn">Update Class</button>
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
            const showFormBtn = document.getElementById('show-schedule-form');
            const scheduleForm = document.getElementById('schedule-class-form');
            const cancelBtn = document.getElementById('cancel-schedule');
            const editModal = document.getElementById('edit-class-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                scheduleForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                scheduleForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit class functionality
            const editButtons = document.querySelectorAll('.edit-class');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const course = this.getAttribute('data-course');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const start = this.getAttribute('data-start');
                    const end = this.getAttribute('data-end');
                    const link = this.getAttribute('data-link');
                    
                    document.getElementById('edit-class-id').value = id;
                    document.getElementById('edit-course-id').value = course;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-start-time').value = start;
                    document.getElementById('edit-end-time').value = end;
                    document.getElementById('edit-meeting-link').value = link;
                    
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