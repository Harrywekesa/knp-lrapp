<?php
require_once(__DIR__ . '/../includes/functions.php');
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get user's enrolled courses for context
$enrollments = getUserEnrollments($user['id']);
$enrolledCourses = [];
foreach ($enrollments as $enrollment) {
    $course = getProgramById($enrollment['program_id']);
    if ($course) {
        $enrolledCourses[] = $course;
    }
}

// Handle AI requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ask_ai'])) {
    $question = $_POST['question'];
    $context = $_POST['context'] ?? '';
    
    // Process with AI
    if (!function_exists('processAIRequest')) {
        /**
         * Dummy AI processing function.
         * Replace this with actual AI integration.
         */
        function processAIRequest($question, $context, $user) {
            // Example: return a simple echo for demonstration
            return "You asked: " . htmlspecialchars($question) . 
                   ($context ? " (Context: " . htmlspecialchars($context) . ")" : "") .
                   ". [This is a placeholder AI response.]";
        }
    }
    $response = processAIRequest($question, $context, $user);
    
    if ($response) {
        $aiResponse = $response;
    } else {
        $aiResponse = "Sorry, I couldn't process your request at the moment. Please try again later.";
    }
}

// Sample quick actions for AI
$quickActions = [
    [
        'icon' => '📝',
        'title' => 'Generate Quiz',
        'description' => 'Create a quiz based on course material',
        'action' => 'generate_quiz'
    ],
    [
        'icon' => '📚',
        'title' => 'Summarize Notes',
        'description' => 'Summarize lengthy lecture notes',
        'action' => 'summarize_notes'
    ],
    [
        'icon' => '❓',
        'title' => 'Explain Concept',
        'description' => 'Get detailed explanation of difficult concepts',
        'action' => 'explain_concept'
    ],
    [
        'icon' => '✍️',
        'title' => 'Write Essay',
        'description' => 'Get help writing academic essays',
        'action' => 'write_essay'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - <?php echo APP_NAME; ?></title>
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
                    <?php if (getUserRole() === 'trainee'): ?>
                        <li><a href="courses.php">My Courses</a></li>
                        <li><a href="ebooks.php">E-Books</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                        <li><a href="assignments.php">Assignments</a></li>
                    <?php elseif (getUserRole() === 'trainer'): ?>
                        <li><a href="courses.php">My Courses</a></li>
                        <li><a href="ebooks.php">E-Books</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                        <li><a href="assignments.php">Assignments</a></li>
                    <?php elseif (getUserRole() === 'exam_officer'): ?>
                        <li><a href="attendance.php">Attendance</a></li>
                    <?php elseif (getUserRole() === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="ai_assistant.php" class="active">AI Assistant</a></li>
                    <li><a href="forum.php">Discussion Forum</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>AI Assistant</h2>
                    <p>Get help with your studies using artificial intelligence</p>
                </div>
                
                <div class="ai-assistant-container">
                    <div class="ai-sidebar">
                        <div class="card">
                            <div class="card-header">
                                <h3>Your Courses</h3>
                            </div>
                            <div class="course-list">
                                <?php if (empty($enrolledCourses)): ?>
                                    <div class="alert">No enrolled courses</div>
                                <?php else: ?>
                                    <?php foreach ($enrolledCourses as $course): ?>
                                    <div class="course-item" data-course="<?php echo htmlspecialchars($course['name']); ?>">
                                        <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($course['code']); ?></p>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="quick-actions">
                                <?php foreach ($quickActions as $action): ?>
                                <div class="quick-action" data-action="<?php echo $action['action']; ?>">
                                    <div class="action-icon"><?php echo $action['icon']; ?></div>
                                    <div class="action-content">
                                        <h4><?php echo htmlspecialchars($action['title']); ?></h4>
                                        <p><?php echo htmlspecialchars($action['description']); ?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ai-main">
                        <div class="card">
                            <div class="card-header">
                                <h3>Chat with AI Assistant</h3>
                                <p>Ask questions and get intelligent responses</p>
                            </div>
                            
                            <div id="chat-container">
                                <div class="message ai-message">
                                    <div class="message-avatar">🤖</div>
                                    <div class="message-content">
                                        <p>Hello <?php echo htmlspecialchars($user['name']); ?>! I'm your AI assistant. How can I help you with your studies today?</p>
                                        <p>You can ask me anything about your courses, get explanations, generate quizzes, or summarize notes.</p>
                                    </div>
                                </div>
                                
                                <?php if (isset($aiResponse)): ?>
                                <div class="message user-message">
                                    <div class="message-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                                    <div class="message-content">
                                        <p><?php echo htmlspecialchars($_POST['question']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="message ai-message">
                                    <div class="message-avatar">🤖</div>
                                    <div class="message-content">
                                        <p><?php echo nl2br(htmlspecialchars($aiResponse)); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" id="ai-form">
                                <div class="form-group">
                                    <label for="question">Ask a Question</label>
                                    <textarea id="question" name="question" class="form-control" rows="3" placeholder="Type your question here... What would you like help with?" required></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="context">Context (Optional)</label>
                                    <select id="context" name="context" class="form-control">
                                        <option value="">No specific context</option>
                                        <?php foreach ($enrolledCourses as $course): ?>
                                        <option value="<?php echo htmlspecialchars($course['name']); ?>">
                                            <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" name="ask_ai" class="btn btn-block">Ask AI Assistant</button>
                                </div>
                            </form>
                        </div>
                        <!-- Add these features to the AI Assistant page -->

<!-- Study Recommendations Section -->
<div class="card">
    <div class="card-header">
        <h3>Personalized Study Recommendations</h3>
        <p>AI-generated suggestions based on your enrolled courses</p>
    </div>
    
    <?php 
    $recommendations = getAIRecommendations($user['id']);
    if (empty($recommendations)): ?>
        <div class="alert">No recommendations available at this time.</div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($recommendations as $rec): ?>
            <div class="recommendation-card">
                <div style="background: var(--primary-color); color: white; padding: 1rem; border-radius: 4px 4px 0 0;">
                    <h4 style="margin: 0;"><?php echo htmlspecialchars($rec['title']); ?></h4>
                </div>
                <div class="recommendation-card-content">
                    <p><?php echo htmlspecialchars($rec['content']); ?></p>
                    <div style="margin-top: 1rem;">
                        <a href="course.php?id=<?php echo $rec['course_id']; ?>" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View Course</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Difficulty Assessment Section -->
<div class="card">
    <div class="card-header">
        <h3>Course Difficulty Assessment</h3>
        <p>Get AI analysis of your performance in enrolled courses</p>
    </div>
    
    <form method="POST" style="margin-bottom: 1.5rem;">
        <div class="form-group">
            <label for="assessment_course">Select Course *</label>
            <select id="assessment_course" name="assessment_course" class="form-control" required>
                <option value="">Select a course</option>
                <?php foreach ($enrolledCourses as $course): ?>
                <option value="<?php echo $course['id']; ?>">
                    <?php echo htmlspecialchars($course['name']); ?> (<?php echo htmlspecialchars($course['code']); ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <button type="submit" name="assess_difficulty" class="btn">Analyze My Performance</button>
        </div>
    </form>
    
    <?php if (isset($_POST['assess_difficulty'])): ?>
        <?php 
        $assessment = getAIDifficultyAssessment($user['id'], $_POST['assessment_course']);
        if (is_array($assessment)): ?>
        <div class="card" style="margin-top: 1.5rem;">
            <div class="card-header">
                <h4>Performance Analysis Results</h4>
            </div>
            <div style="padding: 1rem;">
                <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0; color: var(--primary-color);">
                            <?php echo htmlspecialchars($assessment['difficulty']); ?> Difficulty
                        </h3>
                        <p style="margin: 0.5rem 0 0;"><?php echo htmlspecialchars($assessment['tip']); ?></p>
                    </div>
                    <div style="font-size: 3rem; margin-left: 1rem;">
                        <?php 
                        switch($assessment['difficulty']) {
                            case 'Easy': echo '😊'; break;
                            case 'Moderate': echo '🙂'; break;
                            case 'Challenging': echo '🤔'; break;
                            case 'Difficult': echo '😰'; break;
                            default: echo '😐'; break;
                        }
                        ?>
                    </div>
                </div>
                
                <div>
                    <h4>Recommended Actions:</h4>
                                <ul style="list-style: none; padding: 0;">
                                    <?php foreach ($assessment['recommended_actions'] as $action): ?>
                                    <li style="margin-bottom: 0.5rem; display: flex; align-items: flex-start;">
                                        <div style="margin-right: 0.75rem; color: var(--primary-color);">✓</div>
                                        <div><?php echo htmlspecialchars($action); ?></div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert"><?php echo htmlspecialchars($assessment); ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
                        <div class="card">
                            <div class="card-header">
                                <h3>AI Capabilities</h3>
                                <p>What the AI assistant can help you with</p>
                            </div>
                            
                            <div class="capabilities-grid">
                                <div class="capability-card">
                                    <div class="capability-icon">❓</div>
                                    <h4>Answer Questions</h4>
                                    <p>Get detailed answers to academic questions across all subjects</p>
                                </div>
                                
                                <div class="capability-card">
                                    <div class="capability-icon">📝</div>
                                    <h4>Generate Quizzes</h4>
                                    <p>Create practice quizzes based on your course materials</p>
                                </div>
                                
                                <div class="capability-card">
                                    <div class="capability-icon">📚</div>
                                    <h4>Summarize Content</h4>
                                    <p>Condense lengthy texts and notes into key points</p>
                                </div>
                                
                                <div class="capability-card">
                                    <div class="capability-icon">✍️</div>
                                    <h4>Writing Assistance</h4>
                                    <p>Get help with essays, reports, and academic writing</p>
                                </div>
                                
                                <div class="capability-card">
                                    <div class="capability-icon">🔍</div>
                                    <h4>Research Help</h4>
                                    <p>Find relevant information and sources for your research</p>
                                </div>
                                
                                <div class="capability-card">
                                    <div class="capability-icon">🎓</div>
                                    <h4>Study Tips</h4>
                                    <p>Receive personalized study advice and techniques</p>
                                </div>
                            </div>
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

    <script src="assets/js/main.js"></script>
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
        
        .ai-assistant-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .ai-sidebar {
            flex: 1;
            min-width: 250px;
        }
        
        .ai-main {
            flex: 3;
            min-width: 300px;
        }
        
        #chat-container {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background: #f8fafc;
        }
        
        .message {
            display: flex;
            margin-bottom: 1rem;
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .user-message .message-avatar {
            margin-right: 0;
            margin-left: 1rem;
            background: var(--primary-color);
            color: white;
        }
        
        .message-content {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 80%;
        }
        
        .user-message .message-content {
            background: var(--primary-color);
            color: white;
        }
        
        .course-item {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .course-item:hover {
            background: #f0f9ff;
        }
        
        .course-item:last-child {
            border-bottom: none;
        }
        
        .quick-action {
            display: flex;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .quick-action:hover {
            background: #f0f9ff;
        }
        
        .quick-action:last-child {
            border-bottom: none;
        }
        
        .action-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .action-content h4 {
            margin: 0 0 0.25rem 0;
            color: var(--primary-color);
        }
        
        .action-content p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .capabilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
       