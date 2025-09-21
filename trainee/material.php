<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get material ID from URL
$material_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$material_id) {
    header('Location: dashboard.php');
    exit();
}

// Get material details
$material = getMaterialById($material_id);
if (!$material) {
    header('Location: dashboard.php');
    exit();
}

// Get unit and course details
$unit = getUnitById($material['unit_id']);
if (!$unit) {
    header('Location: dashboard.php');
    exit();
}

$course = getProgramById($unit['program_id']);
if (!$course) {
    header('Location: dashboard.php');
    exit();
}

// Check if user has access to this material
$hasAccess = false;
$accessMessage = '';

// Free materials are always accessible
if ($material['access_level'] === 'public' && $material['price'] <= 0) {
    $hasAccess = true;
    $accessMessage = 'This material is free and publicly accessible.';
} 
// Registered users can access registered materials
elseif ($material['access_level'] === 'registered' && isLoggedIn()) {
    $hasAccess = true;
    $accessMessage = 'You have access to this material as a registered user.';
} 
// Premium materials require purchase
elseif ($material['access_level'] === 'premium') {
    // Check if user has purchased this material
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM purchases WHERE user_id = ? AND material_id = ?");
    $stmt->execute([$user['id'], $material['id']]);
    $purchase = $stmt->fetch();
    
    if ($purchase) {
        $hasAccess = true;
        $accessMessage = 'You have purchased access to this premium material.';
    } else {
        $accessMessage = 'This is a premium material. You need to purchase it to access the full content.';
    }
} else {
    $accessMessage = 'You do not have access to this material.';
}

// Handle material purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_material'])) {
    if ($material['price'] > 0) {
        // Create payment record
        $payment_id = createPayment($user['id'], $material['price'], 'mpesa', uniqid());
        
        if ($payment_id) {
            // Create purchase record
            if (createPurchase($user['id'], $material['id'], $payment_id)) {
                $hasAccess = true;
                $success = "Purchase successful! You now have access to this material.";
                $accessMessage = 'You have purchased access to this premium material.';
            } else {
                $error = "Failed to record purchase. Please contact support.";
            }
        } else {
            $error = "Payment processing failed. Please try again.";
        }
    }
}

// Get related materials
$relatedMaterials = getMaterialsByUnit($material['unit_id']);
// Remove current material from related list
$relatedMaterials = array_filter($relatedMaterials, function($m) use ($material_id) {
    return $m['id'] != $material_id;
});
$relatedMaterials = array_slice($relatedMaterials, 0, 3);

// Get user's enrolled courses to verify access
$enrollments = getUserEnrollments($user['id']);
$enrolledCourseIds = array_column($enrollments, 'program_id');
$isEnrolledInCourse = in_array($course['id'], $enrolledCourseIds);

// Material type icons
$materialIcons = [
    'lecture_note' => '📝',
    'assignment' => '📋',
    'video' => '🎬',
    'ebook' => '📖',
    'other' => '📁'
];

$materialTypes = [
    'lecture_note' => 'Lecture Note',
    'assignment' => 'Assignment',
    'video' => 'Video',
    'ebook' => 'E-book',
    'other' => 'Resource'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($material['title']); ?> - <?php echo APP_NAME; ?></title>
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
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h2><?php echo htmlspecialchars($material['title']); ?></h2>
                            <p><?php echo htmlspecialchars($material['unit_name'] ?? 'N/A'); ?> - <?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></p>
                        </div>
                        <div>
                            <a href="unit.php?id=<?php echo $unit['id']; ?>" class="btn">Back to Unit</a>
                        </div>
                    </div>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                    <div style="flex: 2; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Material Details</h3>
                            </div>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                                <div style="flex: 1; min-width: 200px;">
                                    <div style="position: relative;">
                                        <?php if ($material['cover_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($material['cover_image']); ?>" alt="Cover" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div style="background: #ddd; height: 200px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                <span style="font-size: 3rem;">
                                                    <?php echo $materialIcons[$material['type']] ?? '📁'; ?>
                                                </span>
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
                                    
                                    <div style="margin-top: 1rem;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span><strong>Type:</strong></span>
                                            <span><?php echo $materialTypes[$material['type']] ?? ucfirst($material['type']); ?></span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span><strong>Access Level:</strong></span>
                                            <span>
                                                <?php 
                                                switch($material['access_level']) {
                                                    case 'public': echo 'Public'; break;
                                                    case 'registered': echo 'Registered Users'; break;
                                                    case 'premium': echo 'Premium'; break;
                                                    default: echo ucfirst($material['access_level']); break;
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        
                                        <?php if ($material['type'] === 'ebook' || $material['type'] === 'lecture_note'): ?>
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span><strong>Pages:</strong></span>
                                            <span><?php echo $material['pages'] ?? 'N/A'; ?></span>
                                        </div>
                                        
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span><strong>Preview Pages:</strong></span>
                                            <span><?php echo $material['preview_pages'] ?? 'N/A'; ?></span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span><strong>Uploaded:</strong></span>
                                            <span><?php echo date('M j, Y', strtotime($material['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div style="flex: 2; min-width: 300px;">
                                    <h3>Description</h3>
                                    <p><?php echo htmlspecialchars($material['description'] ?? 'No description available'); ?></p>
                                    
                                    <div style="margin-top: 1.5rem;">
                                        <h3>Access Status</h3>
                                        <div class="alert <?php echo $hasAccess ? 'alert-success' : 'alert-warning'; ?>">
                                            <?php echo $accessMessage; ?>
                                        </div>
                                        
                                        <?php if (!$hasAccess && $material['access_level'] === 'premium' && $material['price'] > 0): ?>
                                        <div class="card" style="margin-top: 1rem;">
                                            <div class="card-header">
                                                <h4>Purchase This Material</h4>
                                            </div>
                                            <form method="POST">
                                                <div style="padding: 1rem;">
                                                    <p>This premium material costs <strong>KES <?php echo number_format($material['price'], 2); ?></strong>.</p>
                                                    <p>After purchase, you'll have unlimited access to this material.</p>
                                                    <button type="submit" name="purchase_material" class="btn btn-block" style="margin-top: 1rem;">
                                                        Purchase for KES <?php echo number_format($material['price'], 2); ?>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($hasAccess): ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3>Material Content</h3>
                                </div>
                                
                                <div style="padding: 1rem;">
                                    <?php if ($material['type'] === 'ebook' || $material['type'] === 'lecture_note'): ?>
                                        <?php if ($material['file_path']): ?>
                                            <div style="text-align: center; margin: 1rem 0;">
                                                <iframe src="../<?php echo htmlspecialchars($material['file_path']); ?>" 
                                                        style="width: 100%; height: 500px; border: 1px solid #ddd; border-radius: 4px;"
                                                        title="<?php echo htmlspecialchars($material['title']); ?>">
                                                    Your browser does not support PDFs. 
                                                    <a href="../<?php echo htmlspecialchars($material['file_path']); ?>">Download the PDF</a>.
                                                </iframe>
                                            </div>
                                            
                                            <div style="text-align: center; margin-top: 1rem;">
                                                <a href="../<?php echo htmlspecialchars($material['file_path']); ?>" class="btn" download>
                                                    Download <?php echo $materialTypes[$material['type']] ?? 'Material'; ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert">No file available for this material.</div>
                                        <?php endif; ?>
                                    <?php elseif ($material['type'] === 'video'): ?>
                                        <?php if ($material['file_path']): ?>
                                            <div style="text-align: center; margin: 1rem 0;">
                                                <video controls style="width: 100%; max-width: 800px; border-radius: 4px;">
                                                    <source src="../<?php echo htmlspecialchars($material['file_path']); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert">No video file available for this material.</div>
                                        <?php endif; ?>
                                    <?php elseif ($material['type'] === 'assignment'): ?>
                                        <div class="card">
                                            <div class="card-header">
                                                <h4>Assignment Details</h4>
                                            </div>
                                            <div style="padding: 1rem;">
                                                <p><strong>Instructions:</strong></p>
                                                <p><?php echo htmlspecialchars($material['description'] ?? 'No instructions provided'); ?></p>
                                                
                                                <div style="margin-top: 1rem;">
                                                    <h5>Submission Guidelines</h5>
                                                    <ul>
                                                        <li>Submit your assignment before the deadline</li>
                                                        <li>Include your name and student ID</li>
                                                        <li>Follow the formatting guidelines provided</li>
                                                        <li>Upload your completed assignment below</li>
                                                    </ul>
                                                </div>
                                                
                                                <div style="margin-top: 1.5rem;">
                                                    <h5>Submit Assignment</h5>
                                                    <form method="POST" enctype="multipart/form-data">
                                                        <div class="form-group">
                                                            <label for="submission_text">Submission Text</label>
                                                            <textarea id="submission_text" name="submission_text" class="form-control" rows="5" placeholder="Type your submission here..."></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label for="submission_file">Upload File (Optional)</label>
                                                            <input type="file" id="submission_file" name="submission_file" class="form-control" accept=".pdf,.doc,.docx,.txt">
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <button type="submit" name="submit_assignment" class="btn">Submit Assignment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($material['file_path']): ?>
                                            <div style="text-align: center; margin: 1rem 0;">
                                                <a href="../<?php echo htmlspecialchars($material['file_path']); ?>" class="btn" download>
                                                    Download <?php echo $materialTypes[$material['type']] ?? 'Material'; ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert">No file available for this material.</div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <div class="card">
                            <div class="card-header">
                                <h3>Course Information</h3>
                            </div>
                            
                            <div style="text-align: center; padding: 1rem;">
                                <div style="width: 80px; height: 80px; border-radius: 50%; background: #ddd; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                    <?php echo strtoupper(substr($course['name'] ?? 'C', 0, 1)); ?>
                                </div>
                                <h4><?php echo htmlspecialchars($course['name'] ?? 'N/A'); ?></h4>
                                <p style="margin: 0.25rem 0 0; color: #666;"><?php echo htmlspecialchars($course['code'] ?? 'N/A'); ?></p>
                                <p style="margin: 0.25rem 0 0; color: #666;"><?php echo $course['duration'] ?? 'N/A'; ?> years</p>
                            </div>
                            
                            <div style="padding: 1rem;">
                                <p><?php echo htmlspecialchars($course['description'] ?? 'No description available'); ?></p>
                                
                                <div style="margin-top: 1rem;">
                                    <a href="course.php?id=<?php echo $course['id']; ?>" class="btn btn-