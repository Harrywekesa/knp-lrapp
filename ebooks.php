<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('trainee');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();
$ebooks = getAllEbooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Books - <?php echo APP_NAME; ?></title>
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
                    <li><a href="ebooks.php">E-Books</a></li>
                    <li><a href="live_classes.php">Live Classes</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>E-Book Library</h2>
                    <p>Browse and purchase educational materials</p>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <input type="text" id="search-input" class="form-control" placeholder="Search e-books...">
                </div>
                
                <div class="grid">
                    <?php foreach ($ebooks as $ebook): ?>
                    <div class="ebook-card">
                        <div style="background: #ddd; height: 150px; display: flex; align-items: center; justify-content: center;">
                            E-Book Cover
                        </div>
                        <div class="ebook-card-content">
                            <h3><?php echo htmlspecialchars($ebook['title']); ?></h3>
                            <p><strong>Author:</strong> <?php echo htmlspecialchars($ebook['author'] ?? 'Unknown'); ?></p>
                            <p class="price">KES <?php echo number_format($ebook['price'], 2); ?></p>
                            <button class="btn">Purchase</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
</body>
</html>