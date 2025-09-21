<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainer');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get topic ID from URL
$topic_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$topic_id) {
    header('Location: forum.php');
    exit();
}

// Get topic details
$topic = getForumTopicById($topic_id);
if (!$topic) {
    header('Location: forum.php');
    exit();
}

// Get course details
$course = getProgramById($topic['program_id']);
if (!$course) {
    header('Location: forum.php');
    exit();
}

// Check if user teaches this course
if ($course['trainer_id'] != $user['id']) {
    header('Location: forum.php');
    exit();
}

// Get replies for this topic
global $pdo;
$stmt = $pdo->prepare("SELECT fr.*, u.name as author FROM forum_replies fr JOIN users u ON fr.user_id = u.id WHERE fr.topic_id = ? ORDER BY fr.created_at ASC");
$stmt->execute([$topic_id]);
$replies = $stmt->fetchAll();

// Get enrolled students for this course
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN enrollments e ON u.id = e.user_id WHERE e.program_id = ? AND e.status = 'active'");
$stmt->execute([$course['id']]);
$enrolledStudents = $stmt->fetchAll();

// Update view count
$stmt = $pdo->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
$stmt->execute([$topic_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($topic['title']); ?> - <?php echo APP_NAME; ?></title>
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
                    <li><a href="ai_assistant.php">Your AI Assistant</a></li>
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
                    <h2><?php echo htmlspecialchars($topic['title']); ?></h2>
                    <p>Forum Topic</p>
                </div>
                
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="flex: 1;">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($course['name']); ?></p>
                        <p><strong>Author:</strong> <?php echo htmlspecialchars($topic['author']); ?></p>
                        <p><strong>Posted:</strong> <?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></p>
                        <p><strong>Views:</strong> <?php echo $topic['views'] + 1; ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($topic['category']); ?></p>
                        <p><strong>Status:</strong> 
                            <?php if ($topic['status'] === 'open'): ?>
                                <span class="badge badge-success">Open</span>
                            <?php elseif ($topic['status'] === 'closed'): ?>
                                <span class="badge badge-danger">Closed</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Pinned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <a href="forum.php" class="btn">Back to Forum</a>
                        <div style="margin-top: 1rem;">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                                <?php if ($topic['status'] === 'open'): ?>
                                    <button type="submit" name="pin_topic" class="btn btn-secondary" style="margin-right: 0.5rem;">Pin Topic</button>
                                    <button type="submit" name="close_topic" class="btn btn-secondary">Close Topic</button>
                                <?php elseif ($topic['status'] === 'closed'): ?>
                                    <button type="submit" name="open_topic" class="btn btn-secondary" style="margin-right: 0.5rem;">Reopen Topic</button>
                                    <button type="submit" name="pin_topic" class="btn btn-secondary">Pin Topic</button>
                                <?php else: ?>
                                    <button type="submit" name="unpin_topic" class="btn btn-secondary" style="margin-right: 0.5rem;">Unpin Topic</button>
                                    <button type="submit" name="close_topic" class="btn btn-secondary">Close Topic</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div style="background: #f8fafc; padding: 1rem; border-radius: 4px 4px 0 0; border-bottom: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong><?php echo htmlspecialchars($topic['author']); ?></strong>
                                <span style="margin-left: 1rem; color: #666;"><?php echo date('M j, Y g:i A', strtotime($topic['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <p><?php echo nl2br(htmlspecialchars($topic['content'])); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Replies (<?php echo count($replies); ?>)</h2>
                    <p>Discussion replies</p>
                </div>
                
                <?php if (empty($replies)): ?>
                    <div class="alert">No replies yet. Be the first to reply!</div>
                <?php else: ?>
                    <?php foreach ($replies as $reply): ?>
                    <div class="card" style="margin-bottom: 1rem;">
                        <div style="background: #f8fafc; padding: 1rem; border-radius: 4px 4px 0 0; border-bottom: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($reply['author']); ?></strong>
                                    <span style="margin-left: 1rem; color: #666;"><?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <div style="padding: 1rem;">
                            <p><?php echo nl2br(htmlspecialchars($reply['content'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Add Reply</h2>
                    <p>Respond to this topic</p>
                </div>
                
                <form method="POST" action="add_reply.php">
                    <input type="hidden" name="topic_id" value="<?php echo $topic['id']; ?>">
                    <div class="form-group">
                        <textarea name="content" class="form-control" rows="4" placeholder="Write your reply..." required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="add_reply" class="btn">Post Reply</button>
                    </div>
                </form>
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
    </style>
</body>
</html>