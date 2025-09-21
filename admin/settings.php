<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        resetTheme();
        $success = 'Theme reset to default colors';
    } else {
        $primary = $_POST['primary_color'];
        $secondary = $_POST['secondary_color'];
        $accent = $_POST['accent_color'];
        updateTheme($primary, $secondary, $accent);
        $success = 'Theme updated successfully';
    }
    $theme = getThemeSettings();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Settings - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="programs.php">Manage Programs</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Theme Settings</h2>
                    <p>Customize the application theme</p>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="primary_color">Primary Color</label>
                        <input type="color" id="primary_color" name="primary_color" class="form-control color-picker" data-color="primary" value="<?php echo $theme['primary_color']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="secondary_color">Secondary Color</label>
                        <input type="color" id="secondary_color" name="secondary_color" class="form-control color-picker" data-color="secondary" value="<?php echo $theme['secondary_color']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="accent_color">Accent Color</label>
                        <input type="color" id="accent_color" name="accent_color" class="form-control color-picker" data-color="accent" value="<?php echo $theme['accent_color']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Save Changes</button>
                        <button type="submit" name="reset" class="btn btn-secondary" style="margin-left: 1rem;">Reset to Default</button>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Theme Preview</h2>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <div style="flex: 1; min-width: 200px;">
                        <h3>Primary Color</h3>
                        <div style="height: 100px; background: var(--primary-color); border-radius: 4px; margin: 1rem 0;"></div>
                        <p><?php echo $theme['primary_color']; ?></p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <h3>Secondary Color</h3>
                        <div style="height: 100px; background: var(--secondary-color); border-radius: 4px; margin: 1rem 0;"></div>
                        <p><?php echo $theme['secondary_color']; ?></p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <h3>Accent Color</h3>
                        <div style="height: 100px; background: var(--accent-color); border-radius: 4px; margin: 1rem 0;"></div>
                        <p><?php echo $theme['accent_color']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2023 <?php echo APP_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>