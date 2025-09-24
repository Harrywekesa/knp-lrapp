<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get all programs for reference
$programs = getAllPrograms();

// Get ebooks created by this trainer
$ebooks = getEbooksByTrainer($user['id']);

// Handle ebook actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_ebook'])) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $pages = $_POST['pages'];
        $preview_pages = $_POST['preview_pages'];
        $unit_id = $_POST['unit_id'];
        $access_level = $_POST['access_level'];
        
        // Handle file uploads
        $file_path = null;
        $cover_image = null;
        
        if (isset($_FILES['ebook_file']) && $_FILES['ebook_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/ebooks/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['ebook_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['ebook_file']['tmp_name'], $target_file)) {
                $file_path = 'uploads/ebooks/' . $filename;
            }
        }
        
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/covers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $cover_image = 'uploads/covers/' . $filename;
            }
        }
        
        if (createEbook($title, $author, $description, $price, $pages, $preview_pages, $unit_id, $access_level, $file_path, $cover_image)) {
            $success = "E-book created successfully";
            // Refresh ebooks
            $ebooks = getEbooksByTrainer($user['id']);
        } else {
            $error = "Failed to create e-book";
        }
    } elseif (isset($_POST['update_ebook'])) {
        $id = $_POST['ebook_id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $pages = $_POST['pages'];
        $preview_pages = $_POST['preview_pages'];
        $unit_id = $_POST['unit_id'];
        $access_level = $_POST['access_level'];
        
        // Handle file uploads
        $cover_image = null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/covers/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $cover_image = 'uploads/covers/' . $filename;
            }
        }
        
        if (updateEbook($id, $title, $author, $description, $price, $pages, $preview_pages, $unit_id, $access_level, $cover_image)) {
            $success = "E-book updated successfully";
            // Refresh ebooks
            $ebooks = getEbooksByTrainer($user['id']);
        } else {
            $error = "Failed to update e-book";
        }
    } elseif (isset($_POST['delete_ebook'])) {
        $id = $_POST['ebook_id'];
        
        if (deleteEbook($id)) {
            $success = "E-book deleted successfully";
            // Refresh ebooks
            $ebooks = getEbooksByTrainer($user['id']);
        } else {
            $error = "Failed to delete e-book";
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
    <title>E-Books Management - <?php echo APP_NAME; ?></title>
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
                    <h2>E-Books Management</h2>
                    <p>Create and manage e-books for your courses</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Upload New E-Book</button>
                
                <div id="create-ebook-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Upload New E-Book</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="unit_id">Unit *</label>
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select a unit</option>
                                <?php foreach ($programCourses as $programData): ?>
                                    <?php if (!empty($programData['courses'])): ?>
                                        <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                            <?php foreach ($programData['courses'] as $course): ?>
                                                <?php 
                                                $courseUnits = getUnitsByCourse($course['id']);
                                                if (!empty($courseUnits)):
                                                ?>
                                                    <optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)">
                                                        <?php foreach ($courseUnits as $unit): ?>
                                                        <option value="<?php echo $unit['id']; ?>">
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>)
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="title">E-Book Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="author">Author *</label>
                                    <input type="text" id="author" name="author" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="price">Price (KES) - Set to 0 for free e-books</label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="0.00">
                                </div>
                            </div>
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
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="pages">Total Pages *</label>
                                    <input type="number" id="pages" name="pages" class="form-control" min="1" max="10000" value="100" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="preview_pages">Preview Pages *</label>
                                    <input type="number" id="preview_pages" name="preview_pages" class="form-control" min="1" max="1000" value="10" required>
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
                                    <label for="ebook_file">E-Book File (PDF) *</label>
                                    <input type="file" id="ebook_file" name="ebook_file" class="form-control" accept=".pdf" required>
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
                            <button type="submit" name="create_ebook" class="btn">Upload E-Book</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search e-books...">
                </div>
                
                <?php if (empty($ebooks)): ?>
                    <div class="alert">You haven't uploaded any e-books yet.</div>
                    <div style="text-align: center; margin: 2rem 0;">
                        <button id="show-create-form" class="btn">Upload Your First E-Book</button>
                    </div>
                <?php else: ?>
                    <div class="grid">
                        <?php foreach ($ebooks as $ebook): ?>
                        <div class="ebook-card">
                            <div style="position: relative;">
                                <?php if ($ebook['cover_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="Cover" style="width: 100%; height: 150px; object-fit: cover; border-radius: 4px 4px 0 0;">
                                <?php else: ?>
                                    <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center; border-radius: 4px 4px 0 0;">
                                        <?php 
                                        switch($ebook['type']) {
                                            case 'lecture_note': echo '📝'; break;
                                            case 'assignment': echo '📋'; break;
                                            case 'video': echo '🎬'; break;
                                            case 'ebook': echo '📖'; break;
                                            default: echo '📁'; break;
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($ebook['price'] > 0): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        KES <?php echo number_format($ebook['price'], 2); ?>
                                    </div>
                                <?php elseif ($ebook['access_level'] === 'public'): ?>
                                    <div style="position: absolute; top: 10px; right: 10px; background: rgba(16, 185, 129, 0.8); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                        FREE
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ebook-card-content">
                                <h3><?php echo htmlspecialchars($ebook['title']); ?></h3>
                                <p class="ebook-type">
                                    <?php 
                                    switch($ebook['type']) {
                                        case 'lecture_note': echo 'Lecture Note'; break;
                                        case 'assignment': echo 'Assignment'; break;
                                        case 'video': echo 'Video'; break;
                                        case 'ebook': echo 'E-book'; break;
                                        default: echo 'Resource'; break;
                                    }
                                    ?>
                                </p>
                                <p><strong>Unit:</strong> <?php echo htmlspecialchars($ebook['unit_name'] ?? 'N/A'); ?></p>
                                <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author'] ?? 'Unknown'); ?></p>
                                <p><?php echo htmlspecialchars(substr($ebook['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <span><?php echo $ebook['pages']; ?> pages</span>
                                    <span>Preview: <?php echo $ebook['preview_pages']; ?> pages</span>
                                </div>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <button class="btn edit-ebook" 
                                            data-id="<?php echo $ebook['id']; ?>" 
                                            data-unit="<?php echo $ebook['unit_id']; ?>" 
                                            data-title="<?php echo htmlspecialchars($ebook['title']); ?>" 
                                            data-author="<?php echo htmlspecialchars($ebook['author'] ?? ''); ?>" 
                                            data-description="<?php echo htmlspecialchars($ebook['description'] ?? ''); ?>" 
                                            data-price="<?php echo $ebook['price']; ?>" 
                                            data-pages="<?php echo $ebook['pages']; ?>" 
                                            data-preview="<?php echo $ebook['preview_pages']; ?>" 
                                            data-access="<?php echo $ebook['access_level']; ?>" 
                                            style="flex: 1; padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                    <form method="POST" style="flex: 1; margin: 0;">
                                        <input type="hidden" name="ebook_id" value="<?php echo $ebook['id']; ?>">
                                        <button type="submit" name="delete_ebook" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width: 100%;" onclick="return confirm('Are you sure you want to delete this e-book?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Edit E-Book Modal -->
    <div id="edit-ebook-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit E-Book</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit-ebook-id" name="ebook_id">
                <div class="form-group">
                    <label for="edit-unit-id">Unit *</label>
                    <select id="edit-unit-id" name="unit_id" class="form-control" required>
                        <option value="">Select a unit</option>
                        <?php foreach ($programCourses as $programData): ?>
                            <?php if (!empty($programData['courses'])): ?>
                                <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                    <?php foreach ($programData['courses'] as $course): ?>
                                        <?php 
                                        $courseUnits = getUnitsByCourse($course['id']);
                                        if (!empty($courseUnits)):
                                        ?>
                                            <optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)">
                                                <?php foreach ($courseUnits as $unit): ?>
                                                <option value="<?php echo $unit['id']; ?>">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($unit['name']); ?> (<?php echo htmlspecialchars($unit['code']); ?>)
                                                </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-title">E-Book Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-author">Author *</label>
                            <input type="text" id="edit-author" name="author" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-price">Price (KES) - Set to 0 for free e-books</label>
                            <input type="number" id="edit-price" name="price" class="form-control" step="0.01" value="0.00">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-access-level">Access Level *</label>
                            <select id="edit-access-level" name="access_level" class="form-control" required>
                                <option value="public">Public (Free)</option>
                                <option value="registered">Registered Users</option>
                                <option value="premium">Premium (Paid)</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-pages">Total Pages *</label>
                            <input type="number" id="edit-pages" name="pages" class="form-control" min="1" max="10000" value="100" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-preview-pages">Preview Pages *</label>
                            <input type="number" id="edit-preview-pages" name="preview_pages" class="form-control" min="1" max="1000" value="10" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-ebook-file">E-Book File (PDF) - Leave blank to keep current</label>
                            <input type="file" id="edit-ebook-file" name="ebook_file" class="form-control" accept=".pdf">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-cover-image">Cover Image - Leave blank to keep current</label>
                            <input type="file" id="edit-cover-image" name="cover_image" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_ebook" class="btn">Update E-Book</button>
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
            const createForm = document.getElementById('create-ebook-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-ebook-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit ebook functionality
            const editButtons = document.querySelectorAll('.edit-ebook');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const unit = this.getAttribute('data-unit');
                    const title = this.getAttribute('data-title');
                    const author = this.getAttribute('data-author');
                    const description = this.getAttribute('data-description');
                    const price = this.getAttribute('data-price');
                    const pages = this.getAttribute('data-pages');
                    const preview = this.getAttribute('data-preview');
                    const access = this.getAttribute('data-access');
                    
                    document.getElementById('edit-ebook-id').value = id;
                    document.getElementById('edit-unit-id').value = unit;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-author').value = author;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-price').value = price;
                    document.getElementById('edit-pages').value = pages;
                    document.getElementById('edit-preview-pages').value = preview;
                    document.getElementById('edit-access-level').value = access;
                    
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
        });
    </script>
</body>
</html>