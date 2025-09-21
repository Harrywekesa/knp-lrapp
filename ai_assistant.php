<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if ($role === 'trainee'): ?>
                        <li><a href="ebooks.php">E-Books</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                    <?php elseif ($role === 'trainer'): ?>
                        <li><a href="courses.php">My Courses</a></li>
                        <li><a href="live_classes.php">Live Classes</a></li>
                    <?php endif; ?>
                    <li><a href="ai_assistant.php">AI Assistant</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>AI Assistant</h2>
                    <p>Ask questions and get help with your studies</p>
                </div>
                
                <div id="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 1rem; margin-bottom: 1rem; background: #f9fafb;">
                    <div class="message" style="margin-bottom: 1rem;">
                        <div style="font-weight: bold; color: var(--primary-color);">AI Assistant</div>
                        <div style="margin-top: 0.25rem;">Hello! I'm your AI assistant. How can I help you with your studies today?</div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" id="user-input" class="form-control" placeholder="Type your question here..." style="flex: 1;">
                    <button id="send-btn" class="btn">Send</button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Quick Actions</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <button class="btn" style="text-align: center; padding: 1rem;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📝</div>
                        <div>Generate Quiz</div>
                    </button>
                    <button class="btn" style="text-align: center; padding: 1rem;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📚</div>
                        <div>Summarize Notes</div>
                    </button>
                    <button class="btn" style="text-align: center; padding: 1rem;">
                        <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">❓</div>
                        <div>Explain Concept</div>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chat-container');
            const userInput = document.getElementById('user-input');
            const sendBtn = document.getElementById('send-btn');
            
            function addMessage(sender, text) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message';
                messageDiv.style.marginBottom = '1rem';
                
                const senderDiv = document.createElement('div');
                senderDiv.style.fontWeight = 'bold';
                senderDiv.style.color = sender === 'You' ? 'var(--accent-color)' : 'var(--primary-color)';
                senderDiv.textContent = sender;
                
                const textDiv = document.createElement('div');
                textDiv.style.marginTop = '0.25rem';
                textDiv.textContent = text;
                
                messageDiv.appendChild(senderDiv);
                messageDiv.appendChild(textDiv);
                chatContainer.appendChild(messageDiv);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
            
            function simulateAIResponse(userMessage) {
                // Simulate AI processing delay
                setTimeout(() => {
                    let response = "I understand your question about '" + userMessage + "'. ";
                    response += "In a real implementation, I would connect to an AI service to provide a detailed answer. ";
                    response += "For now, I'm just a simulation. How else can I assist you?";
                    addMessage('AI Assistant', response);
                }, 1000);
            }
            
            sendBtn.addEventListener('click', function() {
                const message = userInput.value.trim();
                if (message) {
                    addMessage('You', message);
                    userInput.value = '';
                    simulateAIResponse(message);
                }
            });
            
            userInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendBtn.click();
                }
            });
        });
    </script>
</body>
</html>