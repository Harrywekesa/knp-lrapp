<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrollments
$enrollments = getUserEnrollments($user['id']);

// Get registered units
$registeredUnits = [];
foreach ($enrollments as $enrollment) {
    $units = getRegisteredUnits($enrollment['id']);
    $registeredUnits = array_merge($registeredUnits, $units);
}

// Get forum topics for registered units
$unitTopics = [];
foreach ($registeredUnits as $unit) {
    $unitTopicsData = getForumTopics($unit['unit_id']);
    foreach ($unitTopicsData as $topic) {
        $topic['unit_name'] = $unit['unit_name'];
        $unitTopics[] = $topic;
    }
}

// Get general forum topics
// Define getGeneralForumTopics if not already defined
if (!function_exists('getGeneralForumTopics')) {
    function getGeneralForumTopics() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM forum_topics WHERE category = 'general' OR category = 'feedback'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
$generalTopics = getGeneralForumTopics();

// Combine all topics
$allTopics = array_merge($unitTopics, $generalTopics);

// Sort topics by date
usort($allTopics, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Separate by category
$courseTopics = [];
$generalTopicsFiltered = [];
$technicalTopics = [];
$assignmentTopics = [];
$examTopics = [];

foreach ($allTopics as $topic) {
    switch ($topic['category']) {
        case 'general':
            $generalTopicsFiltered[] = $topic;
            break;
        case 'technical':
            $technicalTopics[] = $topic;
            break;
        case 'assignment':
            $assignmentTopics[] = $topic;
            break;
        case 'exam':
            $examTopics[] = $topic;
            break;
        default:
            $courseTopics[] = $topic;
            break;
    }
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
            --secondary-color: <?php echo $theme['secondary-color']; ?>;
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
                    <li><a href="courses.php">My Programs</a></li>
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
                    <h2>Discussion Forum</h2>
                    <p>Connect with other learners and instructors</p>
                </div>
                
                <button id="show-create-form" class="btn" style="margin-bottom: 1.5rem;">Create New Topic</button>
                
                <div id="create-topic-form" class="card" style="display: none; margin-bottom: 1.5rem;">
                    <div class="card-header">
                        <h3>Create New Topic</h3>
                    </div>
                    <form method="POST" action="create_forum_topic.php">
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
                    <input type="text" class="form-control" placeholder="Search forum topics by title or content...">
                </div>
                
                <div class="tabs">
                    <div class="tab-buttons">
                        <button class="tab-button active" data-tab="all">All Topics (<?php echo count($allTopics); ?>)</button>
                        <button class="tab-button" data-tab="course">Course Topics (<?php echo count($courseTopics); ?>)</button>
                        <button class="tab-button" data-tab="general">General (<?php echo count($generalTopicsFiltered); ?>)</button>
                        <button class="tab-button" data-tab="technical">Technical (<?php echo count($technicalTopics); ?>)</button>
                        <button class="tab-button" data-tab="assignment">Assignment Help (<?php echo count($assignmentTopics); ?>)</button>
                        <button class="tab-button" data-tab="exam">Exam Prep (<?php echo count($examTopics); ?>)</button>
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
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Category</th>
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
                                                    <?php 
                                                    switch($topic['category']) {
                                                        case 'general': echo 'General'; break;
                                                        case 'technical': echo 'Technical'; break;
                                                        case 'assignment': echo 'Assignment'; break;
                                                        case 'exam': echo 'Exam Prep'; break;
                                                        case 'feedback': echo 'Feedback'; break;
                                                        default: echo ucfirst($topic['category']); break;
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="course" class="tab-pane">
                            <?php if (empty($courseTopics)): ?>
                                <div class="alert">No course topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Unit</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courseTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                <td><?php echo htmlspecialchars($topic['unit_name'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="general" class="tab-pane">
                            <?php if (empty($generalTopicsFiltered)): ?>
                                <div class="alert">No general topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($generalTopicsFiltered as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="technical" class="tab-pane">
                            <?php if (empty($technicalTopics)): ?>
                                <div class="alert">No technical topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($technicalTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="assignment" class="tab-pane">
                            <?php if (empty($assignmentTopics)): ?>
                                <div class="alert">No assignment help topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($assignmentTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="exam" class="tab-pane">
                            <?php if (empty($examTopics)): ?>
                                <div class="alert">No exam preparation topics found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Topic</th>
                                                <th>Author</th>
                                                <th>Replies</th>
                                                <th>Views</th>
                                                <th>Last Post</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($examTopics as $topic): ?>
                                            <tr>
                                                <td>
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                        <?php echo htmlspecialchars($topic['title']); ?>
                                                    </a>
                                                    <?php if ($topic['status'] === 'pinned'): ?>
                                                        <span class="badge badge-warning" style="margin-left: 0.5rem;">Pinned</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a href="forum_topic.php?id=<?php echo $topic['id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
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
        
        .tabs {
            margin-top: 1.5rem;
        }
        
        .tab-buttons {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #ddd;
        }
        
        .tab-button {
            padding: 0.75rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #666;
        }
        
        .tab-button.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .tab-pane {
            display: none;
            padding: 1.5rem 0;
        }
        
        .tab-pane.active {
            display: block;
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
            
            showFormBtn.addEventListener('click', function() {
                createForm.style.display = 'block';
                showFormBtn.style.display = 'none';
            });
            
            cancelBtn.addEventListener('click', function() {
                createForm.style.display = 'none';
                showFormBtn.style.display = 'inline-block';
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