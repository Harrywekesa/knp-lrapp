<?php
require_once '../includes/functions.php';
redirectIfNotLoggedIn();
redirectIfNotAllowed('admin');

$user = getUserById($_SESSION['user_id']);
$theme = getThemeSettings();

// Get sales data
$sales = getAllSales();
$monthlySales = getMonthlySales();
$topSellingMaterials = getTopSellingMaterials();

// Calculate total revenue
$totalRevenue = 0;
foreach ($sales as $sale) {
    $totalRevenue += $sale['amount'];
}

// Calculate monthly revenue
$currentMonth = date('Y-m');
$monthlyRevenue = 0;
foreach ($monthlySales as $sale) {
    if ($sale['month'] === $currentMonth) {
        $monthlyRevenue = $sale['amount'];
        break;
    }
}

// Get recent sales
$recentSales = array_slice($sales, 0, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracking - <?php echo APP_NAME; ?></title>
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
                    <li><a href="dashboard.php">Admin Dashboard</a></li>
                    <li><a href="users.php">Manage Users</a></li>
                    <li><a href="courses.php">Manage Courses</a></li>
                    <li><a href="ebooks.php">Manage E-Books</a></li>
                    <li><a href="trainers.php">Approve Trainers</a></li>
                    <li><a href="sales.php" class="active">Sales Tracking</a></li>
                    <li><a href="settings.php">Theme Settings</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="flex-shrink-0">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Sales Tracking</h2>
                    <p>Monitor and track all sales transactions</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-number">KES <?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-number">KES <?php echo number_format($monthlyRevenue, 2); ?></div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">🛒</div>
                        <div class="stat-number"><?php echo count($sales); ?></div>
                        <div class="stat-label">Total Sales</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-number"><?php echo count($topSellingMaterials); ?></div>
                        <div class="stat-label">Top Selling Items</div>
                    </div>
                </div>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card">
                    <div class="card-header">
                        <h3>Recent Sales</h3>
                        <p>Most recent transactions</p>
                    </div>
                    
                    <?php if (empty($recentSales)): ?>
                        <div class="alert">No sales recorded yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td><?php echo $sale['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sale['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($sale['material_title'] ?? 'N/A'); ?></td>
                                        <td>KES <?php echo number_format($sale['amount'], 2); ?></td>
                                        <td>
                                            <?php 
                                            switch($sale['method']) {
                                                case 'mpesa': echo 'M-Pesa'; break;
                                                case 'paypal': echo 'PayPal'; break;
                                                case 'credit_card': echo 'Credit Card'; break;
                                                default: echo ucfirst($sale['method']); break;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($sale['created_at'])); ?></td>
                                        <td>
                                            <?php if ($sale['payment_status'] === 'completed'): ?>
                                                <span class="badge badge-success">Completed</span>
                                            <?php elseif ($sale['payment_status'] === 'pending'): ?>
                                                <span class="badge badge-warning">Pending</span>
                                            <?php elseif ($sale['payment_status'] === 'failed'): ?>
                                                <span class="badge badge-danger">Failed</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?php echo ucfirst($sale['payment_status']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Monthly Sales Report</h3>
                        <p>Revenue by month</p>
                    </div>
                    
                    <?php if (empty($monthlySales)): ?>
                        <div class="alert">No sales recorded yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Sales</th>
                                        <th>Revenue</th>
                                        <th>Avg. Sale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthlySales as $sale): ?>
                                    <tr>
                                        <td><?php echo date('M Y', strtotime($sale['month'] . '-01')); ?></td>
                                        <td><?php echo $sale['count']; ?></td>
                                        <td>KES <?php echo number_format($sale['amount'], 2); ?></td>
                                        <td>KES <?php echo number_format($sale['amount'] / $sale['count'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Top Selling Materials</h3>
                        <p>Best performing items</p>
                    </div>
                    
                    <?php if (empty($topSellingMaterials)): ?>
                        <div class="alert">No sales recorded yet.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Material</th>
                                        <th>Sales</th>
                                        <th>Revenue</th>
                                        <th>Avg. Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topSellingMaterials as $material): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($material['title']); ?></td>
                                        <td><?php echo $material['sales_count']; ?></td>
                                        <td>KES <?php echo number_format($material['total_revenue'], 2); ?></td>
                                        <td>KES <?php echo number_format($material['avg_price'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Detailed Sales Report</h3>
                    <p>Filter and export sales data</p>
                </div>
                
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                    <div style="flex: 1; min-width: 250px;">
                        <label for="date_from">From Date</label>
                        <input type="date" id="date_from" class="form-control">
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <label for="date_to">To Date</label>
                        <input type="date" id="date_to" class="form-control">
                    </div>
                    <div style="flex: 1; min-width: 250px;">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="paypal">PayPal</option>
                            <option value="credit_card">Credit Card</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 250px; display: flex; align-items: flex-end;">
                        <button class="btn" style="flex: 1;">Filter</button>
                        <button class="btn btn-secondary" style="flex: 1; margin-left: 0.5rem;">Export CSV</button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Material</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo $sale['id']; ?></td>
                                <td><?php echo htmlspecialchars($sale['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($sale['material_title'] ?? 'N/A'); ?></td>
                                <td>KES <?php echo number_format($sale['amount'], 2); ?></td>
                                <td>
                                    <?php 
                                    switch($sale['method']) {
                                        case 'mpesa': echo 'M-Pesa'; break;
                                        case 'paypal': echo 'PayPal'; break;
                                        case 'credit_card': echo 'Credit Card'; break;
                                        default: echo ucfirst($sale['method']); break;
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($sale['reference'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($sale['created_at'])); ?></td>
                                <td>
                                    <?php if ($sale['payment_status'] === 'completed'): ?>
                                        <span class="badge badge-success">Completed</span>
                                    <?php elseif ($sale['payment_status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php elseif ($sale['payment_status'] === 'failed'): ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><?php echo ucfirst($sale['payment_status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
            // Add any necessary JavaScript for filtering/exporting
        });
    </script>
</body>
</html>