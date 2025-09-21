<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get unit ID from URL
$unit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$unit_id) {
    header('Location: courses.php');
    exit();
}

// Get unit details
$unit = getUnitById($unit_id);
if (!$unit) {
    header('Location: courses.php');
    exit();
}

// Get course details
$course = getProgramById($unit['program_id']);
if (!$course) {
    header('Location: courses.php');
    exit();
}

// Check if user teaches this course
if ($course['trainer_id'] != $user['id']) {
    header('Location: courses.php');
    exit();
}

// Get materials for this unit
$materials = getMaterialsByUnit($unit_id);

// Get classes for this unit
$classes = getClassesByCourse($unit['id']);

// Get assignments for this unit
$assignments = getAssignmentsByCourse($unit['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($unit['name']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="ai_assistant.php">Your AI Assistant</a></li>
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
                    <h2><?php echo htmlspecialchars($unit['name']); ?></h2>
                    <p><?php echo htmlspecialchars($unit['code']); ?> - Year <?php echo $unit['year']; ?>, Semester <?php echo $unit['semester']; ?></p>
                </div>
                
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <p><?php echo htmlspecialchars($unit['description'] ?? 'No description available'); ?></p>
                        <p><strong>Credits:</strong> <?php echo $unit['credits']; ?></p>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                    </div>
                    <div>
                        <a href="course.php?id=<?php echo $course['id']; ?>" class="btn">Back to <?php echo htmlspecialchars($course['name']); ?></a>
                        <a href="edit_unit.php?id=<?php echo $unit['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">Edit Unit</a>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Learning Materials</h2>
                    <p>Materials for <?php echo htmlspecialchars($unit['name']); ?></p>
                </div>
                
                <button id="show-upload-form" class="btn" style="margin-bottom: 1.5rem;">Upload New Material</button>
                
                <div id="upload-material-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Upload New Material</h3>
                    </div>
                    <form method="POST" action="upload_material.php" enctype="multipart/form-data">
                        <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="title">Material Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="type">Material Type *</label>
                                    <select id="type" name="type" class="form-control" required>
                                        <option value="lecture_note">Lecture Note</option>
                                        <option value="assignment">Assignment</option>
                                        <option value="video">Video</option>
                                        <option value="ebook">E-book</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="access_level">Access Level *</label>
                                    <select id="access_level" name="access_level" class="form-control" required>
                                        <option value="public">Public (Free)</option>
                                        <option value="registered">Registered Users</option>
                                        <option value="premium">Premium (Paid)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="price">Price (KES) - Set to 0 for free materials</label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="material_file">Material File</label>
                                    <input type="file" id="material_file" name="material_file" class="form-control">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="cover_image">Cover Image</label>
                                    <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="upload_material" class="btn">Upload Material</button>
                            <button type="button" id="cancel-upload" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <?php if (empty($materials)): ?>
                    <div class="alert">No materials available for this unit yet.</div>
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
                                <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <a href="material.php?id=<?php echo $material['id']; ?>" class="btn" style="flex: 1;">View Details</a>
                                    <a href="edit_material.php?id=<?php echo $material['id']; ?>" class="btn btn-secondary" style="flex: 1;">Edit</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h2>Live Classes</h2>
                        <p>Classes for <?php echo htmlspecialchars($unit['name']); ?></p>
                    </div>
                    
                    <?php if (empty($classes)): ?>
                        <div class="alert">No classes scheduled for this unit yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($class['title']); ?></td>
                                        <td><?php echo date('M j, g:i A', strtotime($class['start_time'])); ?></td>
                                        <td>
                                            <?php if (strtotime($class['start_time']) > time()): ?>
                                                <span class="badge badge-success">Scheduled</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="class.php?id=<?php echo $class['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Manage</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 1rem;">
                        <a href="schedule_class.php?unit_id=<?php echo $unit['id']; ?>" class="btn btn-block">Schedule New Class</a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2>Assignments</h2>
                        <p>Assignments for <?php echo htmlspecialchars($unit['name']); ?></p>
                    </div>
                    
                    <?php if (empty($assignments)): ?>
                        <div class="alert">No assignments created for this unit yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                        <td>
                                            <?php if ($assignment['status'] === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="assignment.php?id=<?php echo $assignment['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Manage</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 1rem;">
                        <a href="create_assignment.php?unit_id=<?php echo $unit['id']; ?>" class="btn btn-block">Create New Assignment</a>
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
            const showFormBtn = document.getElementById('show-upload-form');
            const uploadForm = document.getElementById('upload-material-form');
            const cancelBtn = document.getElementById('cancel-upload');
            
            showFormBtn.addEventListener('click', function() {
                uploadForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                uploadForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
        });
    </script>
</body>
</html>