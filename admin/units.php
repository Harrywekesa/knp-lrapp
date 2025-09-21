<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle unit actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_unit'])) {
        $program_id = $_POST['program_id'];
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $credits = $_POST['credits'];
        
        if (createUnit($program_id, $name, $code, $description, $semester, $year, $credits)) {
            $success = "Unit created successfully";
        } else {
            $error = "Failed to create unit";
        }
    } elseif (isset($_POST['update_unit'])) {
        $id = $_POST['unit_id'];
        $program_id = $_POST['program_id'];
        $name = $_POST['name'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $credits = $_POST['credits'];
        
        if (updateUnit($id, $name, $code, $description, $semester, $year, $credits)) {
            $success = "Unit updated successfully";
        } else {
            $error = "Failed to update unit";
        }
    } elseif (isset($_POST['delete_unit'])) {
        $id = $_POST['unit_id'];
        
        if (deleteUnit($id)) {
            $success = "Unit deleted successfully";
        } else {
            $error = "Failed to delete unit";
        }
    }
}

// Get all programs and units
$programs = getAllPrograms();

// Get units - either all units or filtered by program
$selected_program_id = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;

if ($selected_program_id > 0) {
    $units = getUnitsByProgram($selected_program_id);
    $selected_program = getProgramById($selected_program_id);
} else {
    // Get all units
    global $pdo;
    $stmt = $pdo->query("SELECT u.*, p.name as program_name, d.name as department_name 
                         FROM units u 
                         JOIN programs p ON u.program_id = p.id 
                         JOIN departments d ON p.department_id = d.id 
                         ORDER BY u.created_at DESC");
    $units = $stmt->fetchAll();
    $selected_program = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Units - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --primary-color: <?php echo $theme['primary_color']; ?>;
            --secondary-color: <?php echo $theme['secondary_color']; ?>;
            --accent-color: <?php echo $theme['accent_color']; ?>;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header>
        <div class="container header-content">
            <div class="logo"><?php echo APP_NAME; ?></div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="departments.php">Departments</a></li>
                    <li><a href="programs.php">Programs</a></li>
                    <li><a href="units.php" class="active">Units</a></li>
                    <li><a href="materials.php">Materials</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="sales.php">Commerce</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Manage Units</h2>
                    <p>Create and manage academic units (Programs/modules)</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Unit</button>
                
                <div id="create-unit-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Unit</h3>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="program_id">Program *</label>
                            <select id="program_id" name="program_id" class="form-control" required>
                                <option value="">Select a program</option>
                                <?php foreach ($programs as $program): ?>
                                <option value="<?php echo $program['id']; ?>">
                                    <?php echo htmlspecialchars($program['name']); ?> 
                                    (<?php echo htmlspecialchars($program['department_name']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Unit Name *</label>
                                    <input type="text" id="name" name="name" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="code">Unit Code *</label>
                                    <input type="text" id="code" name="code" class="form-control" required>
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
                            <button type="submit" name="create_unit" class="btn">Create Unit</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Program Filter with "View All" option -->
                <div style="margin-bottom: 1.5rem;">
                    <form method="GET" style="display: inline-block; margin-right: 1rem;">
                        <label for="filter_program">Filter by Program:</label>
                        <select id="filter_program" name="program_id" class="form-control" style="width: auto; display: inline-block; margin: 0 0.5rem;">
                            <option value="0">View All Units</option>
                            <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['id']; ?>" <?php echo ($program['id'] == $selected_program_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['name']); ?> 
                                (<?php echo htmlspecialchars($program['department_name']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="padding: 0.5rem 1rem;">Filter</button>
                    </form>
                    <input type="text" class="form-control" placeholder="Search units..." style="width: 250px; display: inline-block;">
                </div>
                
                <?php if ($selected_program): ?>
                <h3 style="margin: 1.5rem 0 1rem;">
                    Units in <?php echo htmlspecialchars($selected_program['name']); ?>
                </h3>
                <?php elseif ($selected_program_id == 0): ?>
                <h3 style="margin: 1.5rem 0 1rem;">All Units</h3>
                <?php endif; ?>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Program</th>
                            <th>Year/Sem</th>
                            <th>Credits</th>
                            <th>Materials</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($units)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">No units found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($units as $unit): ?>
                        <tr>
                            <td><?php echo $unit['id']; ?></td>
                            <td><?php echo htmlspecialchars($unit['name']); ?></td>
                            <td><?php echo htmlspecialchars($unit['code']); ?></td>
                            <td>
                                <?php 
                                if (isset($unit['program_name'])) {
                                    echo htmlspecialchars($unit['program_name']);
                                } else {
                                    $program = getProgramById($unit['program_id']);
                                    echo htmlspecialchars($program['name'] ?? 'N/A');
                                }
                                ?>
                                <?php if (isset($unit['department_name'])): ?>
                                    <br><small><?php echo htmlspecialchars($unit['department_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>Y<?php echo $unit['year']; ?>/S<?php echo $unit['semester']; ?></td>
                            <td><?php echo $unit['credits']; ?></td>
                            <td>
                                <?php 
                                $materials = getMaterialsByUnit($unit['id']);
                                echo count($materials);
                                ?>
                            </td>
                            <td>
                                <?php if ($unit['status'] === 'active'): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn edit-unit" 
                                        data-id="<?php echo $unit['id']; ?>" 
                                        data-program="<?php echo $unit['program_id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($unit['name']); ?>" 
                                        data-code="<?php echo htmlspecialchars($unit['code']); ?>" 
                                        data-description="<?php echo htmlspecialchars($unit['description'] ?? ''); ?>" 
                                        data-year="<?php echo $unit['year']; ?>" 
                                        data-semester="<?php echo $unit['semester']; ?>" 
                                        data-credits="<?php echo $unit['credits']; ?>" 
                                        style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                                    <button type="submit" name="delete_unit" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" onclick="return confirm('Are you sure you want to delete this unit? This will delete all associated materials.')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Edit Unit Modal -->
    <div id="edit-unit-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Unit</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-unit-id" name="unit_id">
                <div class="form-group">
                    <label for="edit-program-id">Program *</label>
                    <select id="edit-program-id" name="program_id" class="form-control" required>
                        <option value="">Select a program</option>
                        <?php foreach ($programs as $program): ?>
                        <option value="<?php echo $program['id']; ?>">
                            <?php echo htmlspecialchars($program['name']); ?> 
                            (<?php echo htmlspecialchars($program['department_name']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-name">Unit Name *</label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-code">Unit Code *</label>
                            <input type="text" id="edit-code" name="code" class="form-control" required>
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
                            <input type="number" id="edit-credits" name="credits" class="form-control" min="1" max="20" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_unit" class="btn">Update Unit</button>
                    <button type="button" id="close-modal-edit" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-dark text-light">
        <div class="container">
            <p class="text-center mb-0">&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
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
            const showFormBtn = document.getElementById('show-create-form');
            const createForm = document.getElementById('create-unit-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-unit-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit unit functionality
            const editButtons = document.querySelectorAll('.edit-unit');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const program = this.getAttribute('data-program');
                    const name = this.getAttribute('data-name');
                    const code = this.getAttribute('data-code');
                    const description = this.getAttribute('data-description');
                    const year = this.getAttribute('data-year');
                    const semester = this.getAttribute('data-semester');
                    const credits = this.getAttribute('data-credits');
                    
                    document.getElementById('edit-unit-id').value = id;
                    document.getElementById('edit-program-id').value = program;
                    document.getElementById('edit-name').value = name;
                    document.getElementById('edit-code').value = code;
                    document.getElementById('edit-description').value = description;
                    document.getElementById('edit-year').value = year;
                    document.getElementById('edit-semester').value = semester;
                    document.getElementById('edit-credits').value = credits;
                    
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