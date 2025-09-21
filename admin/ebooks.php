<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle ebook actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_ebook'])) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $pages = $_POST['pages'];
        $preview_pages = $_POST['preview_pages'];
        
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
        
        if (createEbook($title, $author, $description, $price, $pages, $preview_pages, $file_path, $cover_image)) {
            $success = "E-book created successfully";
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
        
        if (updateEbook($id, $title, $author, $description, $price, $pages, $preview_pages, $cover_image)) {
            $success = "E-book updated successfully";
        } else {
            $error = "Failed to update e-book";
        }
    } elseif (isset($_POST['delete_ebook'])) {
        $id = $_POST['ebook_id'];
        
        if (deleteEbook($id)) {
            $success = "E-book deleted successfully";
        } else {
            $error = "Failed to delete e-book";
        }
    }
}

// Get all ebooks
$ebooks = getAllEbooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage E-books - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="programs.php">Manage Programs</a></li>
                    <li><a href="ebooks.php" class="active">Manage E-books</a></li>
                    <li><a href="trainers.php">Approve Trainers</a></li>
                    <li><a href="sales.php">Commerce</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Manage E-books</h2>
                    <p>Upload and manage e-books</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Upload New E-book</button>
                
                <div id="create-ebook-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Upload New E-book</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="author">Author</label>
                                    <input type="text" id="author" name="author" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price">Price (KES)</label>
                                    <input type="number" id="price" name="price" class="form-control" step="0.01" value="0.00">
                                </div>
                            </div>
                            
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="pages">Total Pages</label>
                                    <input type="number" id="pages" name="pages" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="preview_pages">Preview Pages</label>
                                    <input type="number" id="preview_pages" name="preview_pages" class="form-control" value="10">
                                </div>
                                
                                <div class="form-group">
                                    <label for="category">Category</label>
                                    <select id="category" name="category" class="form-control">
                                        <option value="Programming">Programming</option>
                                        <option value="Design">Design</option>
                                        <option value="Business">Business</option>
                                        <option value="Science">Science</option>
                                        <option value="Other">Other</option>
                                    </select>
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
                                    <label for="ebook_file">E-book File (PDF)</label>
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
                            <button type="submit" name="create_ebook" class="btn">Upload E-book</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search e-books...">
                </div>
                
                <div class="grid">
                    <?php foreach ($ebooks as $ebook): ?>
                    <div class="ebook-card">
                        <div style="position: relative;">
                            <?php if ($ebook['cover_image']): ?>
                                <img src="../<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="Cover" style="width: 100%; height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div style="background: #ddd; height: 200px; display: flex; align-items: center; justify-content: center;">
                                    No Cover Image
                                </div>
                            <?php endif; ?>
                            <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                                KES <?php echo number_format($ebook['price'], 2); ?>
                            </div>
                        </div>
                        <div class="ebook-card-content">
                            <h3><?php echo htmlspecialchars($ebook['title']); ?></h3>
                            <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author']); ?></p>
                            <p><?php echo htmlspecialchars(substr($ebook['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                            <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                <span><?php echo $ebook['pages']; ?> pages</span>
                                <span>Preview: <?php echo $ebook['preview_pages']; ?> pages</span>
                            </div>
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                <button class="btn edit-ebook" 
                                        data-id="<?php echo $ebook['id']; ?>" 
                                        data-title="<?php echo htmlspecialchars($ebook['title']); ?>" 
                                        data-author="<?php echo htmlspecialchars($ebook['author']); ?>" 
                                        data-description="<?php echo htmlspecialchars($ebook['description'] ?? ''); ?>" 
                                        data-price="<?php echo $ebook['price']; ?>" 
                                        data-pages="<?php echo $ebook['pages']; ?>" 
                                        data-preview="<?php echo $ebook['preview_pages']; ?>" 
                                        style="flex: 1;">Edit</button>
                                <form method="POST" style="flex: 1; margin: 0;">
                                    <input type="hidden" name="ebook_id" value="<?php echo $ebook['id']; ?>">
                                    <button type="submit" name="delete_ebook" class="btn btn-secondary" onclick="return confirm('Are you sure you want to delete this e-book?')">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit E-book Modal -->
    <div id="edit-ebook-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit E-book</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit-ebook-id" name="ebook_id">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-title">Title</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-author">Author</label>
                            <input type="text" id="edit-author" name="author" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-price">Price (KES)</label>
                            <input type="number" id="edit-price" name="price" class="form-control" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-pages">Total Pages</label>
                            <input type="number" id="edit-pages" name="pages" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit-preview-pages">Preview Pages</label>
                            <input type="number" id="edit-preview-pages" name="preview_pages" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-cover-image">Cover Image (Leave blank to keep current)</label>
                    <input type="file" id="edit-cover-image" name="cover_image" class="form-control" accept="image/*">
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_ebook" class="btn">Update E-book</button>
                    <button type="button" id="close-modal" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <style>
        header nav ul li a.active {
            border-bottom: 2px solid white;
        }
        
        .modal {
            display: none;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-ebook-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-ebook-modal');
            const closeModalBtn = document.getElementById('close-modal');
            
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
                    const title = this.getAttribute('data-title');
                    const author = this.getAttribute('data-author');
                    const description = this.getAttribute('data-description');
                    const price = this.getAttribute('data-price');
                    const pages = this.getAttribute('data-pages');
                    const preview = this.getAttribute('data-preview');
                    
                    document.getElementById('edit-ebook-id').value = id;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-author').value = author;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-price').value = price;
                    document.getElementById('edit-pages').value = pages;
                    document.getElementById('edit-preview-pages').value = preview;
                    
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