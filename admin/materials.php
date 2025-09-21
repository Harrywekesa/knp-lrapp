<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle material actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_material'])) {
        $unit_id = $_POST['unit_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $type = $_POST['type'];
        $access_level = $_POST['access_level'];
        $price = $_POST['price'];
        
        // Handle file uploads
        $file_path = null;
        $cover_image = null;
        
        if (isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/materials/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['material_file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $target_file = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['material_file']['tmp_name'], $target_file)) {
                $file_path = 'uploads/materials/' . $filename;
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
        
        if (createUnitMaterial($unit_id, $title, $description, $type, $file_path, $access_level, $price, $cover_image)) {
            $success = "Material created successfully";
        } else {
            $error = "Failed to create material";
        }
    } elseif (isset($_POST['update_material'])) {
        $id = $_POST['material_id'];
        $unit_id = $_POST['unit_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $type = $_POST['type'];
        $access_level = $_POST['access_level'];
        $price = $_POST['price'];
        
        // Handle cover image upload
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
        
        if (updateUnitMaterial($id, $title, $description, $type, $access_level, $price, $cover_image)) {
            $success = "Material updated successfully";
        } else {
            $error = "Failed to update material";
        }
    } elseif (isset($_POST['delete_material'])) {
        $id = $_POST['material_id'];
        
        if (deleteUnitMaterial($id)) {
            $success = "Material deleted successfully";
        } else {
            $error = "Failed to delete material";
        }
    }
}

// Get all programs and units for dropdown
$programs = getAllPrograms();

// Get materials - either all or filtered by unit
$selected_unit_id = isset($_GET['unit_id']) ? (int)$_GET['unit_id'] : 0;
$materials = [];

if ($selected_unit_id > 0) {
    $materials = getMaterialsByUnit($selected_unit_id);
} else {
    // Get all materials (you might want to limit this for performance)
    global $pdo;
    $stmt = $pdo->query("SELECT um.*, u.name as unit_name, p.name as program_name, d.name as department_name 
                         FROM unit_materials um 
                         JOIN units u ON um.unit_id = u.id 
                         JOIN programs p ON u.program_id = p.id 
                         JOIN departments d ON p.department_id = d.id 
                         WHERE um.status = 'published' 
                         ORDER BY um.created_at DESC 
                         LIMIT 100");
    $materials = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials - <?php echo APP_NAME; ?></title>
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
                    <li><a href="departments.php">Departments</a></li>
                    <li><a href="programs.php">Programs</a></li>
                    <li><a href="units.php">Units</a></li>
                    <li><a href="materials.php" class="active">Materials</a></li>
                    <li><a href="users.php">Manage Users</a></li>
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
                    <h2>Manage Unit Materials</h2>
                    <p>Upload and manage learning materials for units</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Upload New Material</button>
                
                <div id="create-material-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Upload New Material</h3>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="unit_id">Unit *</label>
                            <select id="unit_id" name="unit_id" class="form-control" required>
                                <option value="">Select a unit</option>
                                <?php foreach ($programs as $program): ?>
                                    <?php 
                                    $programUnits = getUnitsByProgram($program['id']);
                                    if (!empty($programUnits)): ?>
                                        <optgroup label="<?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['department_name']); ?>)">
                                            <?php foreach ($programUnits as $unit): ?>
                                            <option value="<?php echo $unit['id']; ?>">
                                                <?php echo htmlspecialchars($unit['name']); ?> (Y<?php echo $unit['year']; ?>S<?php echo $unit['semester']; ?>)
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
                            <button type="submit" name="create_material" class="btn">Upload Material</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Unit Filter -->
                <div style="margin-bottom: 1.5rem;">
                    <form method="GET" style="display: inline-block; margin-right: 1rem;">
                        <label for="filter_unit">Filter by Unit:</label>
                        <select id="filter_unit" name="unit_id" class="form-control" style="width: auto; display: inline-block; margin: 0 0.5rem;">
                            <option value="0">All Units</option>
                            <?php foreach ($programs as $program): ?>
                                <?php 
                                $programUnits = getUnitsByProgram($program['id']);
                                if (!empty($programUnits)): ?>
                                    <optgroup label="<?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['department_name']); ?>)">
                                        <?php foreach ($programUnits as $unit): ?>
                                        <option value="<?php echo $unit['id']; ?>" <?php echo ($unit['id'] == $selected_unit_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($unit['name']); ?> (Y<?php echo $unit['year']; ?>S<?php echo $unit['semester']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="padding: 0.5rem 1rem;">Filter</button>
                    </form>
                    <input type="text" class="form-control" placeholder="Search materials..." style="width: 250px; display: inline-block;">
                </div>
                
                <div class="grid">
                    <?php if (empty($materials)): ?>
                        <div class="alert" style="grid-column: 1 / -1; text-align: center;">
                            No materials found. 
                            <?php if ($selected_unit_id > 0): ?>
                                There are no materials for the selected unit.
                            <?php else: ?>
                                There are no materials in the system.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($materials as $material): ?>
                        <div class="material-card">
                            <div style="position: relative;">
                                <?php if (!empty($material['cover_image'])): ?>
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
                                <p>
                                    <strong>Unit:</strong> <?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?><br>
                                    <strong>Program:</strong> <?php echo htmlspecialchars($material['program_name'] ?? 'N/A'); ?>
                                </p>
                                <p><?php echo htmlspecialchars(substr($material['description'] ?? 'No description', 0, 100)) . '...'; ?></p>
                                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                    <button class="btn edit-material" 
                                            data-id="<?php echo $material['id']; ?>" 
                                            data-unit="<?php echo $material['unit_id']; ?>" 
                                            data-title="<?php echo htmlspecialchars($material['title']); ?>" 
                                            data-description="<?php echo htmlspecialchars($material['description'] ?? ''); ?>" 
                                            data-type="<?php echo $material['type']; ?>" 
                                            data-access="<?php echo $material['access_level']; ?>" 
                                            data-price="<?php echo $material['price']; ?>" 
                                            style="flex: 1;">Edit</button>
                                    <form method="POST" style="flex: 1; margin: 0;">
                                        <input type="hidden" name="material_id" value="<?php echo $material['id']; ?>">
                                        <button type="submit" name="delete_material" class="btn btn-secondary" onclick="return confirm('Are you sure you want to delete this material?')">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Material Modal -->
    <div id="edit-material-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Material</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit-material-id" name="material_id">
                <div class="form-group">
                    <label for="edit-unit-id">Unit *</label>
                    <select id="edit-unit-id" name="unit_id" class="form-control" required>
                        <option value="">Select a unit</option>
                        <?php foreach ($programs as $program): ?>
                            <?php 
                            $programUnits = getUnitsByProgram($program['id']);
                            if (!empty($programUnits)): ?>
                                <optgroup label="<?php echo htmlspecialchars($program['name']); ?> (<?php echo htmlspecialchars($program['department_name']); ?>)">
                                    <?php foreach ($programUnits as $unit): ?>
                                    <option value="<?php echo $unit['id']; ?>">
                                        <?php echo htmlspecialchars($unit['name']); ?> (Y<?php echo $unit['year']; ?>S<?php echo $unit['semester']; ?>)
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
                            <label for="edit-title">Material Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-type">Material Type *</label>
                            <select id="edit-type" name="type" class="form-control" required>
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
                            <label for="edit-access-level">Access Level *</label>
                            <select id="edit-access-level" name="access_level" class="form-control" required>
                                <option value="public">Public (Free)</option>
                                <option value="registered">Registered Users</option>
                                <option value="premium">Premium (Paid)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-price">Price (KES)</label>
                            <input type="number" id="edit-price" name="price" class="form-control" step="0.01">
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
                    <button type="submit" name="update_material" class="btn">Update Material</button>
                    <button type="button" id="close-modal-edit" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
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
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-material-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-material-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit material functionality
            const editButtons = document.querySelectorAll('.edit-material');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const unit = this.getAttribute('data-unit');
                    const title = this.getAttribute('data-title');
                    const description = this.getAttribute('data-description');
                    const type = this.getAttribute('data-type');
                    const access = this.getAttribute('data-access');
                    const price = this.getAttribute('data-price');
                    
                    document.getElementById('edit-material-id').value = id;
                    document.getElementById('edit-unit-id').value = unit;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-type').value = type;
                    document.getElementById('edit-access-level').value = access;
                    document.getElementById('edit-price').value = price;
                    
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