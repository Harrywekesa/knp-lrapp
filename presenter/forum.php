<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get courses taught by this trainer
$courses = getCoursesByTrainer($user['id']);

// Get forum topics for trainer's courses
$allTopics = [];
foreach ($courses as $course) {
    $courseTopics = getForumTopics($course['id']);
    foreach ($courseTopics as $topic) {
        $topic['course_name'] = $course['name'];
        $allTopics[] = $topic;
    }
}

// Separate open and closed topics
$openTopics = [];
$closedTopics = [];
$pinnedTopics = [];

foreach ($allTopics as $topic) {
    if ($topic['status'] === 'pinned') {
        $pinnedTopics[] = $topic;
    } elseif ($topic['status'] === 'open') {
        $openTopics[] = $topic;
    } else {
        $closedTopics[] = $topic;
    }
}

// Handle topic actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_topic'])) {
        $course_id = $_POST['course_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        
        if (createForumTopic($user['id'], $title, $content, $category, $course_id)) {
            $success = "Topic created successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to create topic";
        }
    } elseif (isset($_POST['update_topic'])) {
        $id = $_POST['topic_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $category = $_POST['category'];
        $status = $_POST['status'];
        
        if (updateForumTopic($id, $title, $content, $category, $status)) {
            $success = "Topic updated successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to update topic";
        }
    } elseif (isset($_POST['delete_topic'])) {
        $id = $_POST['topic_id'];
        
        if (deleteForumTopic($id)) {
            $success = "Topic deleted successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to delete topic";
        }
    } elseif (isset($_POST['pin_topic'])) {
        $id = $_POST['topic_id'];
        
        if (pinForumTopic($id)) {
            $success = "Topic pinned successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to pin topic";
        }
    } elseif (isset($_POST['unpin_topic'])) {
        $id = $_POST['topic_id'];
        
        if (unpinForumTopic($id)) {
            $success = "Topic unpinned successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to unpin topic";
        }
    } elseif (isset($_POST['close_topic'])) {
        $id = $_POST['topic_id'];
        
        if (closeForumTopic($id)) {
            $success = "Topic closed successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to close topic";
        }
    } elseif (isset($_POST['open_topic'])) {
        $id = $_POST['topic_id'];
        
        if (openForumTopic($id)) {
            $success = "Topic reopened successfully";
            // Refresh topics
            $allTopics = [];
            foreach ($courses as $course) {
                $courseTopics = getForumTopics($course['id']);
                foreach ($courseTopics as $topic) {
                    $topic['course_name'] = $course['name'];
                    $allTopics[] = $topic;
                }
            }
            
            // Separate open and closed topics
            $openTopics = [];
            $closedTopics = [];
            $pinnedTopics = [];
            
            foreach ($allTopics as $topic) {
                if ($topic['status'] === 'pinned') {
                    $pinnedTopics[] = $topic;
                } elseif ($topic['status'] === 'open') {
                    $openTopics[] = $topic;
                } else {
                    $closedTopics[] = $topic;
                }
            }
        } else {
            $error = "Failed to reopen topic";
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
    <title>Discussion Forum - <?php echo APP_NAME; ?></title>
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
                    <li><a href="forum.php" class="active">Discussion Forum</a></li>
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
                    <h2>Discussion Forum</h2>
                    <p>Manage and participate in course discussions</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Topic</button>
                
                <div id="create-topic-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Topic</h3>
                    </div>
                    <form method="POST">
                        <div class="form-group">
                            <label for="course_id">Course *</label>
                            <select id="course_id" name="course_id" class="form-control" required>
                                <option value="">Select a course</option>
                                <?php foreach ($programCourses as $programData): ?>
                                    <?php if (!empty($programData['courses'])): ?>
                                        <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                            <?php foreach ($programData['courses'] as $course): ?>
                                            <option value="<?php echo $course['id']; ?>">
                                                <?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)
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
                                    <label for="title">Topic Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="category">Category *</label>
                                    <select id="category" name="category" class="form-control" required>
                                        <option value="general">General Discussion</option>
                                        <option value="technical">Technical Questions</option>
                                        <option value="assignment">Assignment Help</option>
                                        <option value="exam">Exam Preparation</option>
                                        <option value="feedback">Feedback & Suggestions</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" name="create_topic" class="btn">Create Topic</button>
                            <button type="button" id="cancel-create" class="btn btn-secondary" style="margin-left: 1rem;">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" class="form-control" placeholder="Search forum topics...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Topics (<?php echo count($allTopics); ?>)</button>
                        <button class="tab-button" data-tab="pinned">Pinned (<?php echo count($pinnedTopics); ?>)</button>
                        <button class="tab-button" data-tab="open">Open (<?php echo count($openTopics); ?>)</button>
                        <button class="tab-button" data-tab="closed">Closed (<?php echo count($closedTopics); ?>)</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="all" class="tab-pane active">
                            <?php if (empty($allTopics)): ?>
                                <div class="alert">No forum topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Course</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($topic['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['author']); ?></td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_replies WHERE topic_id = ?");
                                                    $stmt->execute([$topic['id']]);
                                                    $replyCount = $stmt->fetch()['count'];
                                                    echo $replyCount;
                                                    ?>
                                                </td>
                                                <td><?php echo $topic['views'] ?? 0; ?></td>
                                                <td><?php echo date('M j, g:i A', strtotime($topic['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($topic['status'] === 'open'): ?>
                                                        <span class="badge badge-success">Open</span>
                                                    <?php elseif ($topic['status'] === 'closed'): ?>
                                                        <span class="badge badge-danger">Closed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                                        <?php if ($topic['status'] === 'open'): ?>
                                                            <button type="submit" name="pin_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Pin Topic">📌</button>
                                                        <?php elseif ($topic['status'] === 'pinned'): ?>
                                                            <button type="submit" name="unpin_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Unpin Topic">📍</button>
                                                        <?php else: ?>
                                                            <button type="submit" name="open_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Reopen Topic">🔄</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="pinned" class="tab-pane">
                            <?php if (empty($pinnedTopics)): ?>
                                <div class="alert">No pinned topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Course</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pinnedTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($topic['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['author']); ?></td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_replies WHERE topic_id = ?");
                                                    $stmt->execute([$topic['id']]);
                                                    $replyCount = $stmt->fetch()['count'];
                                                    echo $replyCount;
                                                    ?>
                                                </td>
                                                <td><?php echo $topic['views'] ?? 0; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                                        <button type="submit" name="unpin_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Unpin Topic">📍</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="open" class="tab-pane">
                            <?php if (empty($openTopics)): ?>
                                <div class="alert">No open topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Course</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($openTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($topic['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['author']); ?></td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_replies WHERE topic_id = ?");
                                                    $stmt->execute([$topic['id']]);
                                                    $replyCount = $stmt->fetch()['count'];
                                                    echo $replyCount;
                                                    ?>
                                                </td>
                                                <td><?php echo $topic['views'] ?? 0; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                                        <button type="submit" name="pin_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Pin Topic">📌</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="closed" class="tab-pane">
                            <?php if (empty($closedTopics)): ?>
                                <div class="alert">No closed topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Course</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Closed</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($closedTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: #666; text-decoration: none;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <span class="badge badge-danger" style="margin-left: 0.5rem;">Closed</span>
                                                </td>
                                                <td><?php echo htmlspecialchars($topic['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($topic['author']); ?></td>
                                                <td>
                                                    <?php 
                                                    global $pdo;
                                                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM forum_replies WHERE topic_id = ?");
                                                    $stmt->execute([$topic['id']]);
                                                    $replyCount = $stmt->fetch()['count'];
                                                    echo $replyCount;
                                                    ?>
                                                </td>
                                                <td><?php echo $topic['views'] ?? 0; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($topic['created_at'])); ?></td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                                        <button type="submit" name="open_topic" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-left: 0.5rem;" title="Reopen Topic">🔄</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Topic Modal -->
    <div id="edit-topic-modal" class="modal" style="display: none;">
        <div class="card" style="width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="card-header">
                <h3>Edit Topic</h3>
            </div>
            <form method="POST">
                <input type="hidden" id="edit-topic-id" name="topic_id">
                <div class="form-group">
                    <label for="edit-course-id">Course *</label>
                    <select id="edit-course-id" name="course_id" class="form-control" required>
                        <option value="">Select a course</option>
                        <?php foreach ($programCourses as $programData): ?>
                            <?php if (!empty($programData['courses'])): ?>
                                <optgroup label="<?php echo htmlspecialchars($programData['program']['name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($programData['program']['department_name'] ?? 'N/A'); ?>)">
                                    <?php foreach ($programData['courses'] as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['name']); ?> (Y<?php echo $course['year']; ?>S<?php echo $course['semester']; ?>)
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
                            <label for="edit-title">Topic Title *</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="edit-category">Category *</label>
                            <select id="edit-category" name="category" class="form-control" required>
                                <option value="general">General Discussion</option>
                                <option value="technical">Technical Questions</option>
                                <option value="assignment">Assignment Help</option>
                                <option value="exam">Exam Preparation</option>
                                <option value="feedback">Feedback & Suggestions</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="edit-content">Content *</label>
                    <textarea id="edit-content" name="content" class="form-control" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit-status">Status *</label>
                    <select id="edit-status" name="status" class="form-control" required>
                        <option value="open">Open</option>
                        <option value="closed">Closed</option>
                        <option value="pinned">Pinned</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="update_topic" class="btn">Update Topic</button>
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
            const createForm = document.getElementById('create-topic-form');
            const cancelBtn = document.getElementById('cancel-create');
            const editModal = document.getElementById('edit-topic-modal');
            const closeModalBtn = document.getElementById('close-modal-edit');
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
            });
            
            // Edit topic functionality
            const editButtons = document.querySelectorAll('.edit-topic');
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const course = this.getAttribute('data-course');
                    const title = this.getAttribute('data-title');
                    const content = this.getAttribute('data-content');
                    const category = this.getAttribute('data-category');
                    const status = this.getAttribute('data-status');
                    
                    document.getElementById('edit-topic-id').value = id;
                    document.getElementById('edit-course-id').value = course;
                    document.getElementById('edit-title').value = title;
                    document.getElementById('edit-content').value = content;
                    document.getElementById('edit-category').value = category;
                    document.getElementById('edit-status').value = status;
                    
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
            
            // Tab switching
            const tabButtons = document.querySelectorAll('.tab-button');
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons and panes
                    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Show corresponding pane
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html>