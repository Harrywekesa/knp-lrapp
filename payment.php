<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Simulate ebook data
$ebook = [
    'id' => 1,
    'title' => 'Complete Guide to PHP Development',
    'author' => 'Jane Developer',
    'price' => 499.00,
    'description' => 'A comprehensive guide to PHP development covering basics to advanced topics.'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo APP_NAME; ?></title>
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
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Complete Your Purchase</h2>
                    <p>Review your order and select payment method</p>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 2rem;">
                    <div style="flex: 1; min-width: 300px;">
                        <h3>Order Summary</h3>
                        <div style="border: 1px solid #ddd; border-radius: 4px; padding: 1rem; margin: 1rem 0;">
                            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                                <div style="background: #ddd; width: 80px; height: 100px; margin-right: 1rem;"></div>
                                <div>
                                    <h4><?php echo htmlspecialchars($ebook['title']); ?></h4>
                                    <p>by <?php echo htmlspecialchars($ebook['author']); ?></p>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                <span>Price:</span>
                                <span>KES <?php echo number_format($ebook['price'], 2); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid #eee; font-weight: bold;">
                                <span>Total:</span>
                                <span>KES <?php echo number_format($ebook['price'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-width: 300px;">
                        <h3>Payment Method</h3>
                        <div class="card" style="margin: 1rem 0;">
                            <h4>M-Pesa</h4>
                            <p>Pay with your M-Pesa account</p>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" class="form-control" placeholder="2547XXXXXXXX">
                            </div>
                            <button class="btn btn-block">Pay with M-Pesa</button>
                        </div>
                        
                        <div class="card">
                            <h4>PayPal</h4>
                            <p>Pay with your PayPal account</p>
                            <button class="btn btn-block btn-secondary">Pay with PayPal</button>
                        </div>
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

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mpesaBtn = document.querySelector('.btn');
            const paypalBtn = document.querySelector('.btn-secondary');
            
            mpesaBtn.addEventListener('click', function() {
                alert('M-Pesa payment simulation. In a real implementation, this would initiate an STK push.');
            });
            
            paypalBtn.addEventListener('click', function() {
                alert('PayPal payment simulation. In a real implementation, this would redirect to PayPal.');
            });
        });
    </script>
</body>
</html>