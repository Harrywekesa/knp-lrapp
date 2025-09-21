<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle department actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_department'])) {
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        
        if (createDepartment($name, $code, $description)) {
            $success = "Department created successfully";
        } else {
            $error = "Failed to create department";
        }
    } elseif (isset($_POST['update_department'])) {
        $id = $_POST['department_id'];
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        
        if (updateDepartment($id, $name, $code, $description)) {
            $success = "Department updated successfully";
        } else {
            $error = "Failed to update department";
        }
    } elseif (isset($_POST['delete_department'])) {
        $id = $_POST['department_id'];
        
        if (deleteDepartment($id)) {
            $success = "Department deleted successfully";
        } else {
            $error = "Failed to delete department";
        }
    }
}

// Get all departments
$departments = getAllDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - <?php echo APP_NAME; ?></title>
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
                    <li><a href="departments.php" class="active">Departments</a></li>
                    <li><a href="programs.php">Programs</a></li>
                    <li><a href="units.php">Units</a></li>
                    <li><a href="materials.php">Materials</a></li>
                    <li><a href="users.php">Manage Users</a></li>
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
                    <h2>Manage Departments</h2>
                    <p>Create and manage academic departments</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Department</button>
                
                <div id="create-department-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Department</h3>
                    </div>
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Department Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="code">Department Code *</label>
                                    <input type="text" id="code" name="code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_department" class="btn">Create Department</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search departments...">
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Programs</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $dept): ?>
                        <tr>
                            <td><?php echo $dept['id']; ?></td>
                            <td><?php echo htmlspecialchars($dept['name']); ?></td>
                            <td><?php echo htmlspecialchars($dept['code']); ?></td>
                            <td>
                                <?php 
                                $deptPrograms = getProgramsByDepartment($dept['id']);
                                echo count($deptPrograms);
                                ?>
                            </td>
                            <td>
                                <?php if ($dept['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn edit-department" 
                                        data-id="<?php echo $dept['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($dept['name']); ?>" 
                                        data-code="<?php echo htmlspecialchars($dept['code']); ?>" 
                                        data-description="<?php echo htmlspecialchars($dept['description'] ?? ''); ?>" 
                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="department_id" value="<?php echo $dept['id']; ?>">
                                    <button type="submit" name="delete_department" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this department? This will delete all associated programs and units.')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Department Modal -->
    <div id="edit-department-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Department</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-department-id" name="department_id">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-name">Department Name *</label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-code">Department Code *</label>
                            <input type="text" id="edit-code" name="code" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_department" class="btn">Update Department</button>
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
            const createForm = document.getElementById('create-department-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-department-modal');
            const closeModalBtn = document.getElementById('close-modal');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit department functionality
            const editButtons = document.querySelectorAll('.edit-department');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const description = this.getAttribute('data-description');
                    
                    document.getElementById('edit-department-id').value = id;
                    document.getElementById('edit-name').value = name;
                    document.getElementById('edit-code').value = code;
                    document.getElementById('edit-description').value = description;
                    
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