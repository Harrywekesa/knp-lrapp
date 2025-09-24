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
                    <p>Manage your scheduled and past live sessions</p>
                </div>
                
                <button id="show-schedule-form" class="btn" style="margin-bottom: 1.5rem;">Schedule New Class</button>
                
                <div id="schedule-class-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Schedule New Class</h3>
                    </div>
                    <form method="POST" action="schedule_class.php">
                        <div class="form-group">
                            <label for="course_id">Course *</label>
                            <select id="course_id" name="course_id" class="form-control" required>
                                <option value="">Select a course</option>
                                <?php foreach ($courses as $course): ?>
                                <option value="<?php echo $course['id']; ?>">
                                    <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Class Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
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
                            <label for="meeting_link">Meeting Link (Optional)</label>
                            <input type="url" id="meeting_link" name="meeting_link" class="form-control" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="schedule_class" class="btn">Schedule Class</button>
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
                        <button class="tab-button" data-tab="upcoming">Up