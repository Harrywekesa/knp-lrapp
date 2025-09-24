<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('exam_officer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Handle result actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['import_results'])) {
        $exam_id = $_POST['exam_id'];
        $file = $_FILES['results_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            // Process CSV file
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle) {
                $header = fgetcsv($handle); // Skip header row
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 3) {
                        $student_id = $data[0];
                        $student_name = $data[1];
                        $points = $data[2];
                        
                        // Record result
                        recordExamResult($exam_id, $student_id, $points);
                    }
                }
                fclose($handle);
                $success = "Results imported successfully";
            } else {
                $error = "Failed to process results file";
            }
        } else {
            $error = "Failed to upload results file";
        }
    } elseif (isset($_POST['export_results'])) {
        $exam_id = $_POST['exam_id'];
        exportResultsToCSV($exam_id);
        exit();
    }
}

// Get exams with results
$exams = getExamsWithResults();

// Get selected exam
$selected_exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$results = [];
$examDetails = null;

if ($selected_exam_id > 0) {
    $results = getExamResults($selected_exam_id);
    $examDetails = getExamById($selected_exam_id);
}

// Calculate statistics
$totalStudents = count($results);
$averageScore = 0;
$highestScore = 0;
$lowestScore = 0;

if ($totalStudents > 0) {
    $totalPoints = 0;
    $scores = array_column($results, 'points_awarded');
    $totalPoints = array_sum($scores);
    $averageScore = round($totalPoints / $totalStudents, 2);
    $highestScore = max($scores);
    $lowestScore = min($scores);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results Management - <?php echo APP_NAME; ?></title>
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
                    <li><a href="exams.php">Exams</a></li>
                    <li><a href="results.php" class="active">Results</a></li>
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
                    <h2>Results Management</h2>
                    <p>Manage and analyze exam results</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="margin-bottom: 1.5rem;">
                    <form method="GET" style="display: inline-block; margin-right: 1rem;">
                        <label for="exam_id">Select Exam:</label>
                        <select id="exam_id" name="exam_id" class="form-control" style="width: auto; display: inline-block; margin: 0 0.5rem;">
                            <option value="0">Select an exam</option>
                            <?php foreach ($exams as $exam): ?>
                            <option value="<?php echo $exam['id']; ?>" <?php echo ($exam['id'] == $selected_exam_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($exam['title']); ?> - 
                                <?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?> - 
                                <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="padding: 0.5rem 1rem;">Load Results</button>
                    </form>
                    
                    <?php if ($selected_exam_id > 0): ?>
                    <div style="display: inline-block;">
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="exam_id" value="<?php echo $selected_exam_id; ?>">
                            <button type="submit" name="export_results" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Export to CSV</button>
                        </form>
                        
                        <form method="POST" enctype="multipart/form-data" style="display: inline-block; margin-left: 0.5rem;">
                            <input type="hidden" name="exam_id" value="<?php echo $selected_exam_id; ?>">
                            <input type="file" name="results_file" class="form-control" accept=".csv" style="display: inline-block; width: auto;">
                            <button type="submit" name="import_results" class="btn" style="padding: 0.5rem 1rem; margin-left: 0.5rem;">Import Results</button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($selected_exam_id > 0 && $examDetails): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($examDetails['title']); ?></h3>
                        <p><?php echo date('M j, Y', strtotime($examDetails['exam_date'])); ?></p>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <strong>Unit:</strong>
                            <p><?php echo htmlspecialchars($examDetails['unit_name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <strong>Type:</strong>
                            <p>
                                <?php 
                                switch($examDetails['exam_type']) {
                                    case 'midterm': echo 'Midterm Exam'; break;
                                    case 'final': echo 'Final Exam'; break;
                                    case 'practical': echo 'Practical Exam'; break;
                                    case 'quiz': echo 'Quiz'; break;
                                    case 'assignment': echo 'Assignment'; break;
                                    default: echo ucfirst($examDetails['exam_type']); break;
                                }
                                ?>
                            </p>
                        </div>
                        <div>
                            <strong>Max Points:</strong>
                            <p><?php echo $examDetails['max_points']; ?></p>
                        </div>
                        <div>
                            <strong>Status:</strong>
                            <p>
                                <?php if (strtotime($examDetails['exam_date'] . ' ' . $examDetails['end_time']) > time()): ?>
                                    <span class="badge badge-success">Scheduled</span>
                                <?php elseif (strtotime($examDetails['exam_date'] . ' ' . $examDetails['end_time']) > time()): ?>
                                    <span class="badge badge-warning">In Progress</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Completed</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($selected_exam_id > 0): ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Exam Results</h3>
                        <p>Student performance for this exam</p>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-number"><?php echo $totalStudents; ?></div>
                            <div class="stat-label">Students</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📈</div>
                            <div class="stat-number"><?php echo $averageScore; ?></div>
                            <div class="stat-label">Average Score</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🏆</div>
                            <div class="stat-number"><?php echo $highestScore; ?></div>
                            <div class="stat-label">Highest Score</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📉</div>
                            <div class="stat-number"><?php echo $lowestScore; ?></div>
                            <div class="stat-label">Lowest Score</div>
                        </div>
                    </div>
                    
                    <?php if (empty($results)): ?>
                        <div class="alert">No results available for this exam.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Points Awarded</th>
                                        <th>Percentage</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                    <tr>
                                        <td><?php echo $result['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                        <td><?php echo $result['points_awarded']; ?>/<?php echo $examDetails['max_points']; ?></td>
                                        <td><?php echo round(($result['points_awarded'] / $examDetails['max_points']) * 100, 2); ?>%</td>
                                        <td>
                                            <?php 
                                            $percentage = ($result['points_awarded'] / $examDetails['max_points']) * 100;
                                            if ($percentage >= 85) {
                                                echo '<span class="badge badge-success">A</span>';
                                            } elseif ($percentage >= 75) {
                                                echo '<span class="badge badge-success">B+</span>';
                                            } elseif ($percentage >= 65) {
                                                echo '<span class="badge badge-success">B</span>';
                                            } elseif ($percentage >= 55) {
                                                echo '<span class="badge badge-warning">C+</span>';
                                            } elseif ($percentage >= 45) {
                                                echo '<span class="badge badge-warning">C</span>';
                                            } elseif ($percentage >= 35) {
                                                echo '<span class="badge badge-danger">D</span>';
                                            } else {
                                                echo '<span class="badge badge-danger">F</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($result['status'] === 'graded'): ?>
                                                <span class="badge badge-success">Graded</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="view_result.php?id=<?php echo $result['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Details</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h3>Select an Exam</h3>
                        <p>Choose an exam to view results</p>
                    </div>
                    
                    <div class="alert">Please select an exam from the dropdown above to view results.</div>
                    
                    <div class="grid">
                        <?php foreach (array_slice($exams, 0, 6) as $exam): ?>
                        <div class="exam-card">
                            <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                                <h3 style="margin: 0;"><?php echo htmlspecialchars($exam['title']); ?></h3>
                                <p style="margin: 0.5rem 0 0; opacity: 0.9;">
                                    <?php echo date('M j, Y', strtotime($exam['exam_date'])); ?>
                                </p>
                            </div>
                            <div class="exam-card-content">
                                <p><strong>Unit:</strong> <?php echo htmlspecialchars($exam['unit_name'] ?? 'N/A'); ?></p>
                                <p><strong>Type:</strong> 
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
                                </p>
                                <p><strong>Max Points:</strong> <?php echo $exam['max_points']; ?></p>
                                <div style="margin-top: 1rem;">
                                    <a href="?exam_id=<?php echo $exam['id']; ?>" class="btn btn-block">View Results</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
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
        
        .exam-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .exam-card:hover {
            transform: translateY(-5px);
        }
        
        .exam-card-content {
            padding: 1rem;
        }
        
        .exam-card-content h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
    </style>
</body>
</html>