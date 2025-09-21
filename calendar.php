<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUserById($_SESSION['user_id']);
$role = getUserRole();
$theme = getThemeSettings();

// Simulate calendar events
$events = [
    [
        'id' => 1,
        'title' => 'PHP Basics Class',
        'start' => '2023-10-15T10:00:00',
        'end' => '2023-10-15T12:00:00',
        'type' => 'class'
    ],
    [
        'id' => 2,
        'title' => 'Submit JavaScript Assignment',
        'start' => '2023-10-16T23:59:00',
        'end' => '2023-10-16T23:59:00',
        'type' => 'deadline'
    ],
    [
        'id' => 3,
        'title' => 'Advanced JavaScript Class',
        'start' => '2023-10-17T14:00:00',
        'end' => '2023-10-17T16:00:00',
        'type' => 'class'
    ],
    [
        'id' => 4,
        'title' => 'Database Design Exam',
        'start' => '2023-10-20T09:00:00',
        'end' => '2023-10-20T11:00:00',
        'type' => 'exam'
    ]
];

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Get first day of month and number of days
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$dayOfWeek = date('w', $firstDay);
$daysInMonth = cal_days_in_month(0, $month, $year);
$monthName = date('F', $firstDay);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - <?php echo APP_NAME; ?></title>
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
                    <?php elseif ($role === 'exam_officer'): ?>
                        <li><a href="attendance.php">Attendance</a></li>
                    <?php elseif ($role === 'admin'): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="calendar.php">Calendar</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2>Calendar</h2>
                    <p>View your schedule and important dates</p>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><?php echo $monthName . ' ' . $year; ?></h3>
                    <div>
                        <a href="?month=<?php echo $month == 1 ? 12 : $month - 1; ?>&year=<?php echo $month == 1 ? $year - 1 : $year; ?>" class="btn" style="padding: 0.5rem 1rem;">Previous</a>
                        <a href="?month=<?php echo date('n'); ?>&year=<?php echo date('Y'); ?>" class="btn" style="padding: 0.5rem 1rem; margin: 0 0.5rem;">Today</a>
                        <a href="?month=<?php echo $month == 12 ? 1 : $month + 1; ?>&year=<?php echo $month == 12 ? $year + 1 : $year; ?>" class="btn" style="padding: 0.5rem 1rem;">Next</a>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background: #ddd;">
                    <div class="calendar-header">Sun</div>
                    <div class="calendar-header">Mon</div>
                    <div class="calendar-header">Tue</div>
                    <div class="calendar-header">Wed</div>
                    <div class="calendar-header">Thu</div>
                    <div class="calendar-header">Fri</div>
                    <div class="calendar-header">Sat</div>
                    
                    <?php for ($i = 0; $i < $dayOfWeek; $i++): ?>
                        <div class="calendar-day empty"></div>
                    <?php endfor; ?>
                    
                    <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                        <?php 
                        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $dayEvents = array_filter($events, function($event) use ($currentDate) {
                            return date('Y-m-d', strtotime($event['start'])) === $currentDate;
                        });
                        ?>
                        <div class="calendar-day <?php echo date('Y-m-d') === $currentDate ? 'today' : ''; ?>">
                            <div class="day-number"><?php echo $day; ?></div>
                            <div class="events">
                                <?php foreach (array_slice($dayEvents, 0, 2) as $event): ?>
                                    <div class="event <?php echo $event['type']; ?>" title="<?php echo htmlspecialchars($event['title']); ?>">
                                        <?php echo htmlspecialchars(substr($event['title'], 0, 15)) . (strlen($event['title']) > 15 ? '...' : ''); ?>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($dayEvents) > 2): ?>
                                    <div class="more-events">+<?php echo count($dayEvents) - 2; ?> more</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                
                <div style="margin-top: 2rem;">
                    <h3>Upcoming Events</h3>
                    <div style="margin-top: 1rem;">
                        <?php foreach ($events as $event): ?>
                        <div class="card" style="margin-bottom: 1rem;">
                            <div style="display: flex; align-items: center;">
                                <div style="margin-right: 1rem; width: 40px; height: 40px; border-radius: 50%; background: <?php 
                                    echo $event['type'] === 'class' ? '#3b82f6' : 
                                        ($event['type'] === 'deadline' ? '#f59e0b' : 
                                        ($event['type'] === 'exam' ? '#ef4444' : '#8b5cf6')); 
                                ?>; display: flex; align-items: center; justify-content: center; color: white;">
                                    <?php 
                                    echo $event['type'] === 'class' ? '📚' : 
                                        ($event['type'] === 'deadline' ? '⏰' : 
                                        ($event['type'] === 'exam' ? '📝' : '⭐')); 
                                    ?>
                                </div>
                                <div>
                                    <h4 style="margin: 0;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p style="margin: 0.25rem 0 0; color: #666; font-size: 0.9rem;">
                                        <?php echo date('M j, Y g:i A', strtotime($event['start'])); ?>
                                    </p>
                                </div>
                                <div style="margin-left: auto;">
                                    <button class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
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
    <style>
        .calendar-header {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem;
            text-align: center;
            font-weight: bold;
        }
        
        .calendar-day {
            background: white;
            min-height: 100px;
            padding: 0.5rem;
            border: 1px solid #eee;
        }
        
        .calendar-day.empty {
            background: #f8fafc;
        }
        
        .calendar-day.today {
            background: #eff6ff;
            border: 2px solid var(--primary-color);
        }
        
        .day-number {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .event {
            font-size: 0.75rem;
            padding: 0.1rem 0.25rem;
            margin-bottom: 0.1rem;
            border-radius: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .event.class {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .event.deadline {
            background: #fef3c7;
            color: #92400e;
        }
        
        .event.exam {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .more-events {
            font-size: 0.7rem;
            color: #666;
            text-align: center;
        }
    </style>
</body>
</html>