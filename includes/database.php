<?php
require_once 'config.php';

// Create tables if they don't exist
function createTables($pdo) {
    $queries = [
        // Users table with enhanced fields
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('trainee', 'trainer', 'exam_officer', 'admin') DEFAULT 'trainee',
            status ENUM('active', 'pending', 'suspended') DEFAULT 'pending',
            bio TEXT,
            avatar VARCHAR(255),
            phone VARCHAR(20),
            department_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
        )",
        
        // Academic departments
        "CREATE TABLE IF NOT EXISTS departments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            code VARCHAR(20) UNIQUE,
            hod_id INT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (hod_id) REFERENCES users(id) ON DELETE SET NULL
        )",
        
        // Academic programs/courses with level field
        "CREATE TABLE IF NOT EXISTS programs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department_id INT,
            name VARCHAR(200) NOT NULL,
            code VARCHAR(20) UNIQUE,
            description TEXT,
            level ENUM('certificate', 'diploma', 'degree', 'masters', 'phd') DEFAULT 'degree',
            duration INT, -- in years
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
        )",
        
        // Academic units
        "CREATE TABLE IF NOT EXISTS units (
            id INT AUTO_INCREMENT PRIMARY KEY,
            program_id INT,
            name VARCHAR(200) NOT NULL,
            code VARCHAR(20) UNIQUE,
            description TEXT,
            semester INT,
            year INT,
            credits INT DEFAULT 3,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
        )",
        
        // Unit materials
        "CREATE TABLE IF NOT EXISTS unit_materials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            unit_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            type ENUM('lecture_note', 'assignment', 'video', 'ebook', 'other') DEFAULT 'lecture_note',
            file_path VARCHAR(255),
            cover_image VARCHAR(255),
            access_level ENUM('public', 'registered', 'premium') DEFAULT 'public',
            price DECIMAL(10, 2) DEFAULT 0.00,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
        )",
        
        // Student enrollments
        "CREATE TABLE IF NOT EXISTS enrollments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            program_id INT,
            enrollment_date DATE,
            status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
        )",
        
        // Student unit registrations
        "CREATE TABLE IF NOT EXISTS unit_registrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            enrollment_id INT,
            unit_id INT,
            registration_date DATE,
            status ENUM('registered', 'completed', 'dropped') DEFAULT 'registered',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
        )",
        
        // Classes table
        "CREATE TABLE IF NOT EXISTS classes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            unit_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            start_time DATETIME,
            end_time DATETIME,
            meeting_link VARCHAR(255),
            status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
        )",
        
        // Sessions table
        "CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            class_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            start_time DATETIME,
            end_time DATETIME,
            recording_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
        )",
        
        // Attendance records
        "CREATE TABLE IF NOT EXISTS attendance_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id INT,
            user_id INT,
            joined_at TIMESTAMP NULL,
            left_at TIMESTAMP NULL,
            qr_token VARCHAR(255),
            status ENUM('present', 'absent', 'late') DEFAULT 'absent',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Purchases table
        "CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            material_id INT,
            payment_id INT,
            purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (material_id) REFERENCES unit_materials(id) ON DELETE CASCADE
        )",
        
        // Payments table
        "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            amount DECIMAL(10, 2),
            method ENUM('mpesa', 'paypal', 'credit_card') DEFAULT 'mpesa',
            status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
            reference VARCHAR(255),
            transaction_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Assignments table
        "CREATE TABLE IF NOT EXISTS assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            unit_id INT,
            title VARCHAR(200) NOT NULL,
            description TEXT,
            file_path VARCHAR(255),
            due_date DATETIME,
            max_points INT DEFAULT 100,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
        )",
        
        // Assignment submissions
        "CREATE TABLE IF NOT EXISTS assignment_submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            assignment_id INT,
            user_id INT,
            file_path VARCHAR(255),
            submission_text TEXT,
            points_awarded INT,
            feedback TEXT,
            status ENUM('submitted', 'graded', 'returned') DEFAULT 'submitted',
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            graded_at TIMESTAMP NULL,
            FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Forum topics
        "CREATE TABLE IF NOT EXISTS forum_topics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            unit_id INT NULL,
            title VARCHAR(200) NOT NULL,
            content TEXT,
            category VARCHAR(100),
            views INT DEFAULT 0,
            status ENUM('open', 'closed', 'pinned') DEFAULT 'open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
        )",
        
        // Forum replies
        "CREATE TABLE IF NOT EXISTS forum_replies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            topic_id INT,
            user_id INT,
            content TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Certificates
        "CREATE TABLE IF NOT EXISTS certificates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            program_id INT,
            title VARCHAR(200) NOT NULL,
            issued_date DATE,
            expiry_date DATE,
            file_path VARCHAR(255),
            status ENUM('active', 'expired', 'revoked') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
        )",
        
        // Notifications
        "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(200) NOT NULL,
            message TEXT,
            type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
            read_status BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Theme settings
        "CREATE TABLE IF NOT EXISTS theme_settings (
            id INT PRIMARY KEY,
            primary_color VARCHAR(7) DEFAULT '#3B82F6',
            secondary_color VARCHAR(7) DEFAULT '#10B981',
            accent_color VARCHAR(7) DEFAULT '#8B5CF6',
            logo_path VARCHAR(255)
        )",
        
        // Insert default theme
        "INSERT IGNORE INTO theme_settings (id, primary_color, secondary_color, accent_color) 
         VALUES (1, '#3B82F6', '#10B981', '#8B5CF6')"
    ];
    
    foreach ($queries as $query) {
        $pdo->exec($query);
    }
}

// Initialize database
createTables($pdo);
?>