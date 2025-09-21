<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle program actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_program'])) {
        $department_id = $_POST['department_id'];
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $level = $_POST['level'];
        $duration = $_POST['duration'];
        
        if (createProgram($department_id, $name, $code, $description, $level, $duration)) {
            $success = "Program created successfully";
        } else {
            $error = "Failed to create program";
        }
    } elseif (isset($_POST['update_program'])) {
        $id = $_POST['program_id'];
        $department_id = $_POST['department_id'];
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $level = $_POST['level'];
        $duration = $_POST['duration'];
        
        if (updateProgram($id, $name, $code, $description, $level, $duration)) {
            $success = "Program updated successfully";
        } else {
            $error = "Failed to update program";
        }
    } elseif (isset($_POST['delete_program'])) {
        $id = $_POST['program_id'];
        
        if (deleteProgram($id)) {
            $success = "Program deleted successfully";
        } else {
            $error = "Failed to delete program";
        }
    }
}

// Get all programs, departments, and units
$programs = getAllPrograms();
$departments = getAllDepartments();
$units = []; // We'll populate this when needed

// Level options
$levelOptions = [
    '3' => '3',
    '4' => '4',
    '5' => '5',
    '6' => '6',
    '7' => '7'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Programs - <?php echo APP_NAME; ?></title>
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
                    <li><a href="programs.php" class="active">Programs</a></li>
                    <li><a href="units.php">Units</a></li>
                    <li><a href="materials.php">Materials</a></li>
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
                    <h2>Manage Programs</h2>
                    <p>Create and manage academic programs with departments and levels</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Program</button>
                
                <div id="create-program-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Program</h3>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="department_id">Department *</label>
                            <select id="department_id" name="department_id" class="form-control" required>
                                <option value="">Select a department</option>
                                <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?> (<?php echo htmlspecialchars($dept['code']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Program Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="code">Program Code *</label>
                                    <input type="text" id="code" name="code" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="level">Program Level *</label>
                                    <select id="level" name="level" class="form-control" required>
                                        <option value="">Select a level</option>
                                        <?php foreach ($levelOptions as $value => $label): ?>
                                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="duration">Duration (years) *</label>
                                    <input type="number" id="duration" name="duration" class="form-control" min="1" max="10" value="4" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_program" class="btn">Create Program</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search programs...">
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Level</th>
                            <th>Duration</th>
                            <th>Units</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $program): ?>
                        <tr>
                            <td><?php echo $program['id']; ?></td>
                            <td><?php echo htmlspecialchars($program['name']); ?></td>
                            <td><?php echo htmlspecialchars($program['code']); ?></td>
                            <td><?php echo htmlspecialchars($program['department_name']); ?></td>
                            <td><?php echo $levelOptions[$program['level']] ?? ucfirst($program['level']); ?></td>
                            <td><?php echo $program['duration']; ?> years</td>
                            <td>
                                <?php 
                                $programUnits = getUnitsByProgram($program['id']);
                                echo count($programUnits);
                                ?>
                            </td>
                            <td>
                                <?php if ($program['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn edit-program" 
                                        data-id="<?php echo $program['id']; ?>" 
                                        data-department="<?php echo $program['department_id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($program['name']); ?>" 
                                        data-code="<?php echo htmlspecialchars($program['code']); ?>" 
                                        data-description="<?php echo htmlspecialchars($program['description'] ?? ''); ?>" 
                                        data-level="<?php echo $program['level']; ?>" 
                                        data-duration="<?php echo $program['duration']; ?>" 
                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                                    <button type="submit" name="delete_program" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this program?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Program Modal -->
    <div id="edit-program-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Program</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-program-id" name="program_id">
                <div class="form-group">
                    <label for="edit-department-id">Department *</label>
                    <select id="edit-department-id" name="department_id" class="form-control" required>
                        <option value="">Select a department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?> (<?php echo htmlspecialchars($dept['code']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-name">Program Name *</label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-code">Program Code *</label>
                            <input type="text" id="edit-code" name="code" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-level">Program Level *</label>
                            <select id="edit-level" name="level" class="form-control" required>
                                <option value="">Select a level</option>
                                <?php foreach ($levelOptions as $value => $label): ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-duration">Duration (years) *</label>
                            <input type="number" id="edit-duration" name="duration" class="form-control" min="1" max="10" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_program" class="btn">Update Program</button>
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
            const createForm = document.getElementById('create-program-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-program-modal');
            const closeModalBtn = document.getElementById('close-modal');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit program functionality
            const editButtons = document.querySelectorAll('.edit-program');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const department = this.getAttribute('data-department');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const description = this.getAttribute('data-description');
                    const level = this.getAttribute('data-level');
                    const duration = this.getAttribute('data-duration');
                    
                    document.getElementById('edit-program-id').value = id;
                    document.getElementById('edit-department-id').value = department;
                    document.getElementById('edit-name').value = name;
                    document.getElementById('edit-code').value = code;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-level').value = level;
                    document.getElementById('edit-duration').value = duration;
                    
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