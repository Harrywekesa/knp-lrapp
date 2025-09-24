<?php
require_once 'database.php';

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header('Location: dashboard.php');
        exit();
    }
}

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'trainee';
}

function checkRole($allowedRoles) {
    if (!is_array($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }
    return in_array(getUserRole(), $allowedRoles);
}

function redirectIfNotAllowed($allowedRoles) {
    if (!checkRole($allowedRoles)) {
        header('Location: dashboard.php');
        exit();
    }
}
// Fetch all courses from the database
function getAllCourses() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//delete courses
function deleteCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
    return $stmt->execute([$course_id]);
}
//Fetc all ebooks from the database
function getAllDraftEbooks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM ebooks WHERE status = 'draft' ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Get all published e-books
function getAllEbooks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM ebooks WHERE status = 'published' ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Get all published e-books
function getAllArchivedEbooks() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM ebooks WHERE status = 'archived' ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// User functions
function registerUser($name, $email, $password, $role = 'trainee') {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // For trainers, set status to pending for approval
    $status = ($role === 'trainer') ? 'pending' : 'active';
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword, $role, $status]);
}

function loginUser($email, $password) {
    global $pdo;
    // Fixed: Check all users but verify status after authentication
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Check if user is active
        if ($user['status'] !== 'active') {
            return false; // User not active
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['expires_at'] = time() + SESSION_LIFETIME;
        return true;
    }
    return false;
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function getPendingTrainers() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'trainer' AND status = 'pending' ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function approveTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'trainer'");
    return $stmt->execute([$trainer_id]);
}

function rejectTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'trainer' AND status = 'pending'");
    return $stmt->execute([$trainer_id]);
}

function suspendUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function activateUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function deleteUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$user_id]);
}

function updateUserRole($user_id, $role) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    return $stmt->execute([$role, $user_id]);
}

function createUser($name, $email, $password, $role) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $status = 'active'; // Admin-created users are active by default
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword, $role, $status]);
}

function updateUserProfile($user_id, $name, $email, $bio, $phone) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, bio = ?, phone = ? WHERE id = ?");
    return $stmt->execute([$name, $email, $bio, $phone, $user_id]);
}

// Department functions
function createDepartment($name, $code, $description) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO departments (name, code, description) VALUES (?, ?, ?)");
    return $stmt->execute([$name, $code, $description]);
}

function getAllDepartments() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC");
    return $stmt->fetchAll();
}

function getDepartmentById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateDepartment($id, $name, $code, $description) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE departments SET name = ?, code = ?, description = ? WHERE id = ?");
    return $stmt->execute([$name, $code, $description, $id]);
}

function deleteDepartment($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    return $stmt->execute([$id]);
}

// Program functions with level support
function createProgram($department_id, $name, $code, $description, $level, $duration) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO programs (department_id, name, code, description, level, duration) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$department_id, $name, $code, $description, $level, $duration]);
}

function getProgramsByDepartment($department_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE department_id = ? AND status = 'active' ORDER BY name ASC");
    $stmt->execute([$department_id]);
    return $stmt->fetchAll();
}

function getAllPrograms() {
    global $pdo;
    $stmt = $pdo->query("SELECT p.*, d.name as department_name FROM programs p JOIN departments d ON p.department_id = d.id WHERE p.status = 'active' ORDER BY d.name, p.name ASC");
    return $stmt->fetchAll();
}

function getProgramById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateProgram($id, $name, $code, $description, $level, $duration) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE programs SET name = ?, code = ?, description = ?, level = ?, duration = ? WHERE id = ?");
    return $stmt->execute([$name, $code, $description, $level, $duration, $id]);
}

function deleteProgram($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
    return $stmt->execute([$id]);
}

// Unit functions
function createUnit($program_id, $name, $code, $description, $semester, $year, $credits) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO units (program_id, name, code, description, semester, year, credits) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$program_id, $name, $code, $description, $semester, $year, $credits]);
}

function getUnitsByProgram($program_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM units WHERE program_id = ? AND status = 'active' ORDER BY year ASC, semester ASC, name ASC");
    $stmt->execute([$program_id]);
    return $stmt->fetchAll();
}

function getUnitsByProgramAndYear($program_id, $year) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM units WHERE program_id = ? AND year = ? AND status = 'active' ORDER BY semester ASC, name ASC");
    $stmt->execute([$program_id, $year]);
    return $stmt->fetchAll();
}

function getUnitById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM units WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateUnit($id, $name, $code, $description, $semester, $year, $credits) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE units SET name = ?, code = ?, description = ?, semester = ?, year = ?, credits = ? WHERE id = ?");
    return $stmt->execute([$name, $code, $description, $semester, $year, $credits, $id]);
}

function deleteUnit($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM units WHERE id = ?");
    return $stmt->execute([$id]);
}

// Unit material functions
function createUnitMaterial($unit_id, $title, $description, $type, $file_path, $access_level, $price, $cover_image = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO unit_materials (unit_id, title, description, type, file_path, access_level, price, cover_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published')");
    return $stmt->execute([$unit_id, $title, $description, $type, $file_path, $access_level, $price, $cover_image]);
}

function getMaterialsByUnit($unit_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM unit_materials WHERE unit_id = ? AND status = 'published' ORDER BY created_at DESC");
    $stmt->execute([$unit_id]);
    return $stmt->fetchAll();
}

function getMaterialById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM unit_materials WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateUnitMaterial($id, $title, $description, $type, $access_level, $price, $cover_image = null) {
    global $pdo;
    if ($cover_image) {
        $stmt = $pdo->prepare("UPDATE unit_materials SET title = ?, description = ?, type = ?, access_level = ?, price = ?, cover_image = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $access_level, $price, $cover_image, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE unit_materials SET title = ?, description = ?, type = ?, access_level = ?, price = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $access_level, $price, $id]);
    }
}

function deleteUnitMaterial($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM unit_materials WHERE id = ?");
    return $stmt->execute([$id]);
}

// Enrollment functions
function enrollUser($user_id, $program_id) {
    global $pdo;
    $enrollment_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, program_id, enrollment_date) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $program_id, $enrollment_date]);
}

function getUserEnrollments($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT e.*, p.name as program_name, d.name as department_name FROM enrollments e JOIN programs p ON e.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.user_id = ? AND e.status = 'active'");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Unit registration functions
function registerUnit($enrollment_id, $unit_id) {
    global $pdo;
    $registration_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO unit_registrations (enrollment_id, unit_id, registration_date) VALUES (?, ?, ?)");
    return $stmt->execute([$enrollment_id, $unit_id, $registration_date]);
}

function getRegisteredUnits($enrollment_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            ur.*, 
            u.name AS unit_name, 
            u.code AS unit_code, 
            u.year, 
            u.semester, 
            u.credits
        FROM unit_registrations ur
        JOIN units u ON ur.unit_id = u.id
        WHERE ur.enrollment_id = ? AND ur.status = 'registered'
    ");
    $stmt->execute([$enrollment_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// (Place this at the end of your functions.php file or in an appropriate section)
function updateTrainerProfile($userId, $name, $email, $bio, $phone, $specialization, $office_location) {
    // Example using PDO, adjust as needed for your DB connection
    global $pdo;
    $sql = "UPDATE users SET name = :name, email = :email, bio = :bio, phone = :phone, specialization = :specialization, office_location = :office_location WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':bio' => $bio,
        ':phone' => $phone,
        ':specialization' => $specialization,
        ':office_location' => $office_location,
        ':id' => $userId
    ]);
}
// Theme functions
function getThemeSettings() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM theme_settings WHERE id = 1");
    return $stmt->fetch();
}

function updateTheme($primary, $secondary, $accent, $logo_path = null) {
    global $pdo;
    if ($logo_path) {
        $stmt = $pdo->prepare("UPDATE theme_settings SET primary_color = ?, secondary_color = ?, accent_color = ?, logo_path = ? WHERE id = 1");
        return $stmt->execute([$primary, $secondary, $accent, $logo_path]);
    } else {
        $stmt = $pdo->prepare("UPDATE theme_settings SET primary_color = ?, secondary_color = ?, accent_color = ? WHERE id = 1");
        return $stmt->execute([$primary, $secondary, $accent]);
    }
}

function resetTheme() {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE theme_settings SET primary_color = '#3B82F6', secondary_color = '#10B981', accent_color = '#8B5CF6', logo_path = NULL WHERE id = 1");
    return $stmt->execute();
}

// Payment functions
function createPayment($user_id, $amount, $method, $reference) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, method, reference, status) VALUES (?, ?, ?, ?, 'completed')");
    return $stmt->execute([$user_id, $amount, $method, $reference]);
}

function createPurchase($user_id, $material_id, $payment_id) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO purchases (user_id, material_id, payment_id) VALUES (?, ?, ?)");
    return $stmt->execute([$user_id, $material_id, $payment_id]);
}

// Class functions
function createClass($unit_id, $title, $description, $start_time, $end_time, $meeting_link) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO classes (unit_id, title, description, start_time, end_time, meeting_link) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$unit_id, $title, $description, $start_time, $end_time, $meeting_link]);
}

function getClassesByUnit($unit_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE unit_id = ? ORDER BY start_time ASC");
    $stmt->execute([$unit_id]);
    return $stmt->fetchAll();
}

function getClassById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateClass($id, $title, $description, $start_time, $end_time, $meeting_link) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE classes SET title = ?, description = ?, start_time = ?, end_time = ?, meeting_link = ? WHERE id = ?");
    return $stmt->execute([$title, $description, $start_time, $end_time, $meeting_link, $id]);
}

function deleteClass($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
    return $stmt->execute([$id]);
}

// Session functions
function createSession($class_id, $title, $description, $start_time, $end_time) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO sessions (class_id, title, description, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$class_id, $title, $description, $start_time, $end_time]);
}

function getSessionsByClass($class_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE class_id = ? ORDER BY start_time ASC");
    $stmt->execute([$class_id]);
    return $stmt->fetchAll();
}

function getSessionById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Attendance functions
function recordAttendance($session_id, $user_id, $qr_token = null) {
    global $pdo;
    // Check if attendance record already exists
    $stmt = $pdo->prepare("SELECT id FROM attendance_records WHERE session_id = ? AND user_id = ?");
    $stmt->execute([$session_id, $user_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing record
        $stmt = $pdo->prepare("UPDATE attendance_records SET joined_at = NOW(), qr_token = ?, status = 'present' WHERE id = ?");
        return $stmt->execute([$qr_token, $existing['id']]);
    } else {
        // Create new record
        $stmt = $pdo->prepare("INSERT INTO attendance_records (session_id, user_id, joined_at, qr_token, status) VALUES (?, ?, NOW(), ?, 'present')");
        return $stmt->execute([$session_id, $user_id, $qr_token]);
    }
}

function markLeft($session_id, $user_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE attendance_records SET left_at = NOW() WHERE session_id = ? AND user_id = ?");
    return $stmt->execute([$session_id, $user_id]);
}

// Assignment functions
function createAssignment($unit_id, $title, $description, $due_date, $max_points, $file_path = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO assignments (unit_id, title, description, due_date, max_points, file_path) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$unit_id, $title, $description, $due_date, $max_points, $file_path]);
}

function getAssignmentsByUnit($unit_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE unit_id = ? AND status = 'active' ORDER BY due_date ASC");
    $stmt->execute([$unit_id]);
    return $stmt->fetchAll();
}

function submitAssignment($assignment_id, $user_id, $submission_text, $file_path = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO assignment_submissions (assignment_id, user_id, submission_text, file_path) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$assignment_id, $user_id, $submission_text, $file_path]);
}

// Forum functions
function createForumTopic($user_id, $title, $content, $category, $unit_id = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO forum_topics (user_id, title, content, category, unit_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $content, $category, $unit_id]);
}

function getForumTopics($unit_id = null) {
    global $pdo;
    if ($unit_id) {
        $stmt = $pdo->prepare("SELECT ft.*, u.name as author FROM forum_topics ft JOIN users u ON ft.user_id = u.id WHERE ft.unit_id = ? ORDER BY ft.created_at DESC");
        $stmt->execute([$unit_id]);
    } else {
        $stmt = $pdo->query("SELECT ft.*, u.name as author FROM forum_topics ft JOIN users u ON ft.user_id = u.id ORDER BY ft.created_at DESC");
    }
    return $stmt->fetchAll();
}

function createForumReply($topic_id, $user_id, $content) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO forum_replies (topic_id, user_id, content) VALUES (?, ?, ?)");
    return $stmt->execute([$topic_id, $user_id, $content]);
}

// Certificate functions
function issueCertificate($user_id, $program_id, $title) {
    global $pdo;
    $issued_date = date('Y-m-d');
    $expiry_date = date('Y-m-d', strtotime('+2 years'));
    $stmt = $pdo->prepare("INSERT INTO certificates (user_id, program_id, title, issued_date, expiry_date) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$user_id, $program_id, $title, $issued_date, $expiry_date]);
}

// Notification functions
function createNotification($user_id, $title, $message, $type = 'info') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$user_id, $title, $message, $type]);
}

function getUnreadNotifications($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND read_status = FALSE ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function markNotificationAsRead($notification_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET read_status = TRUE WHERE id = ?");
    return $stmt->execute([$notification_id]);
}

// Add these functions to your existing functions.php file

// Course functions
function createCourse($title, $description, $trainer_id, $price = 0) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO courses (title, description, trainer_id, price) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$title, $description, $trainer_id, $price]);
}

function getCourseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCoursesByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE trainer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function updateCourse($id, $title, $description, $price) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, price = ? WHERE id = ?");
    return $stmt->execute([$title, $description, $price, $id]);
}


function searchCourses($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, u.name as trainer_name FROM courses c LEFT JOIN users u ON c.trainer_id = u.id WHERE c.title LIKE ? OR c.description LIKE ? ORDER BY c.created_at DESC");
    $stmt->execute(["%$query%", "%$query%"]);
    return $stmt->fetchAll();
}
// Ebook functions
function createEbook($title, $author, $description, $price, $file_path, $cover_image = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO ebooks (title, author, description, price, file_path, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$title, $author, $description, $price, $file_path, $cover_image]);
}

function getEbookById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ebooks WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateEbook($id, $title, $author, $description, $price, $cover_image = null) {
    global $pdo;
    if ($cover_image) {
        $stmt = $pdo->prepare("UPDATE ebooks SET title = ?, author = ?, description = ?, price = ?, cover_image = ? WHERE id = ?");
        return $stmt->execute([$title, $author, $description, $price, $cover_image, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE ebooks SET title = ?, author = ?, description = ?, price = ? WHERE id = ?");
        return $stmt->execute([$title, $author, $description, $price, $id]);
    }
}

function deleteEbook($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM ebooks WHERE id = ?");
    return $stmt->execute([$id]);
}

function searchEbooks($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM ebooks WHERE title LIKE ? OR author LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$query%", "%$query%"]);
    return $stmt->fetchAll();
}

function getClassesByCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM classes WHERE course_id = ? ORDER BY start_time ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function getAssignmentsByCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? AND status = 'active' ORDER BY due_date ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}
function getEbooksByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT e.*, u.name as trainer_name FROM ebooks e LEFT JOIN users u ON e.trainer_id = u.id WHERE e.trainer_id = ? ORDER BY e.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}
// Forum functions
function getForumTopicById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, p.name as program_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id LEFT JOIN programs p ON ft.program_id = p.id WHERE ft.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getForumRepliesByTopic($topic_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT fr.*, u.name as author FROM forum_replies fr JOIN users u ON fr.user_id = u.id WHERE fr.topic_id = ? ORDER BY fr.created_at ASC");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll();
}

function updateForumTopicViews($topic_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET views = views + 1 WHERE id = ?");
    return $stmt->execute([$topic_id]);
}

// Assignment functions
function getAssignmentById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, p.name as program_name, u.name as author FROM assignments a JOIN programs p ON a.program_id = p.id JOIN users u ON a.author_id = u.id WHERE a.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAssignmentSubmissionById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM assignment_submissions WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAssignmentSubmissionsByAssignment($assignment_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT s.*, u.name as student_name FROM assignment_submissions s JOIN users u ON s.user_id = u.id WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC");
    $stmt->execute([$assignment_id]);
    return $stmt->fetchAll();
}

function gradeAssignmentSubmission($submission_id, $points_awarded, $feedback) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE assignment_submissions SET points_awarded = ?, feedback = ?, graded_at = NOW() WHERE id = ?");
    return $stmt->execute([$points_awarded, $feedback, $submission_id]);
}
// Enrollment functions
function getEnrollmentById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM enrollments WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function enrollUserInProgram($user_id, $program_id) {
    global $pdo;
    $enrollment_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, program_id, enrollment_date, status) VALUES (?, ?, ?, 'active')");
    return $stmt->execute([$user_id, $program_id, $enrollment_date]);
}

function registerUnitForEnrollment($enrollment_id, $unit_id) {
    global $pdo;
    $registration_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO unit_registrations (enrollment_id, unit_id, registration_date, status) VALUES (?, ?, ?, 'registered')");
    return $stmt->execute([$enrollment_id, $unit_id, $registration_date]);
}
// (Place this at the end of your functions.php file or after other user-related functions)
function updateUserPassword($userId, $newPassword) {
    global $conn; // Make sure $conn is your database connection variable
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("si", $hashedPassword, $userId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
//get course by trainer and program
function getCoursesByTrainerAndProgram($trainer_id, $program_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE trainer_id = ? AND program_id = ? ORDER BY created_at DESC");
    $stmt->execute([$trainer_id, $program_id]);
    return $stmt->fetchAll();
}
//assign course to trainer  
function assignCourseToTrainer($course_id, $trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE courses SET trainer_id = ? WHERE id = ?");
    return $stmt->execute([$trainer_id, $course_id]);
}
//remove trainer from course
function removeTrainerFromCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE courses SET trainer_id = NULL WHERE id = ?");
    return $stmt->execute([$course_id]);
}
//getunits by course
function getUnitsByCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM units WHERE course_id = ? AND status = 'active' ORDER BY name ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}
// AI Assistant functions
function processAIRequest($question, $context = '', $user = null) {
    // Simulate AI processing
    // In a real implementation, you would connect to an AI service like OpenAI
    
    // For demonstration, we'll return simulated responses based on keywords
    $question = strtolower($question);
    
    if (strpos($question, 'hello') !== false || strpos($question, 'hi') !== false) {
        return "Hello! How can I assist you with your studies today?";
    }
    
    if (strpos($question, 'quiz') !== false || strpos($question, 'test') !== false) {
        return "I can help you create a quiz. Please provide the subject matter or course content you'd like to create a quiz for, and I'll generate relevant questions for you.";
    }
    
    if (strpos($question, 'summarize') !== false || strpos($question, 'notes') !== false) {
        return "I can help summarize your notes. Please paste the content you'd like summarized, and I'll create a concise summary for easier studying.";
    }
    
    if (strpos($question, 'explain') !== false || strpos($question, 'concept') !== false) {
        return "I can explain complex concepts in simple terms. Please tell me what concept you'd like me to explain, and I'll break it down for you with examples.";
    }
    
    if (strpos($question, 'essay') !== false || strpos($question, 'write') !== false) {
        return "I can help you with essay writing. Please provide the topic and any specific requirements, and I'll help you structure and develop your essay.";
    }
    
    if (strpos($question, 'help') !== false) {
        return "I can help with various academic tasks including:\n\n1. Answering questions about course materials\n2. Generating quizzes and tests\n3. Summarizing lengthy texts\n4. Explaining complex concepts\n5. Assisting with essay writing\n6. Providing study tips and techniques\n\nWhat specific help do you need today?";
    }
    
    // Context-aware responses
    if ($context) {
        return "Based on your context about {$context}, here's my response to your question: {$question}\n\nI've analyzed the relevant course materials and provided an answer tailored to your specific program. Is there anything else you'd like me to elaborate on?";
    }
    
    // General response
    return "Thank you for your question: \"{$question}\". As your AI assistant, I'm here to help with your academic journey. While I can't provide specific answers without more context, I can help you explore this topic further.\n\nConsider:\n1. Reviewing your course materials\n2. Asking more specific questions\n3. Using the 'Explain Concept' feature for complex topics\n\nHow else can I assist you today?";
}

function generateQuiz($subject, $num_questions = 10) {
    // Simulate quiz generation
    $questions = [];
    
    for ($i = 1; $i <= $num_questions; $i++) {
        $questions[] = [
            'id' => $i,
            'question' => "Sample question {$i} about {$subject}",
            'options' => [
                "Option A for question {$i}",
                "Option B for question {$i}",
                "Option C for question {$i}",
                "Option D for question {$i}"
            ],
            'correct_answer' => rand(0, 3)
        ];
    }
    
    return $questions;
}

function summarizeText($text, $max_length = 200) {
    // Simulate text summarization
    $words = explode(' ', $text);
    if (count($words) <= $max_length) {
        return $text;
    }
    
    $summary_words = array_slice($words, 0, $max_length);
    return implode(' ', $summary_words) . '...';
}

function explainConcept($concept) {
    // Simulate concept explanation
    return "Here's an explanation of '{$concept}':\n\nThis is a fundamental concept in your field of study. To understand it better:\n\n1. First, consider the basic principles...\n2. Then examine how it applies in practice...\n3. Finally, review examples and case studies...\n\nFor more detailed information, I recommend reviewing your course materials or asking your instructor for clarification.";
}

function writeEssay($topic, $length = 'medium') {
    // Simulate essay writing assistance
    $lengths = [
        'short' => 300,
        'medium' => 500,
        'long' => 1000
    ];
    
    $word_count = $lengths[$length] ?? 500;
    
    return "Here's an outline for your essay on '{$topic}':\n\nIntroduction:\n- Hook to grab reader attention\n- Background information on the topic\n- Thesis statement presenting your main argument\n\nBody Paragraphs:\n1. First supporting point with evidence\n2. Second supporting point with examples\n3. Third supporting point with analysis\n\nConclusion:\n- Restate thesis in new words\n- Summarize main points\n- Final thought or call to action\n\nThis structure will help you organize your thoughts and create a compelling essay.";
}

// Integration with existing functions
function getAIRecommendations($user_id, $course_id = null) {
    // Get user's enrolled courses
    $enrollments = getUserEnrollments($user_id);
    $courses = [];
    
    foreach ($enrollments as $enrollment) {
        $course = getProgramById($enrollment['program_id']);
        if ($course) {
            $courses[] = $course;
        }
    }
    
    // Generate recommendations based on enrolled courses
    $recommendations = [];
    
    foreach ($courses as $course) {
        // Simulate AI-generated recommendations
        $recommendations[] = [
            'title' => "Study tips for {$course['name']}",
            'type' => 'study_tips',
            'content' => "Focus on understanding core concepts in {$course['name']}. Practice regularly and seek help when needed.",
            'course_id' => $course['id']
        ];
        
        $recommendations[] = [
            'title' => "Recommended resources for {$course['name']}",
            'type' => 'resources',
            'content' => "Check out the latest e-books and lecture notes for {$course['name']} to enhance your learning.",
            'course_id' => $course['id']
        ];
    }
    
    return $recommendations;
}

function getAIDifficultyAssessment($user_id, $course_id) {
    // Simulate AI assessment of user's difficulty with a course
    $enrollments = getUserEnrollments($user_id);
    $enrolled = false;
    
    foreach ($enrollments as $enrollment) {
        if ($enrollment['program_id'] == $course_id) {
            $enrolled = true;
            break;
        }
    }
    
    if (!$enrolled) {
        return "You are not enrolled in this course.";
    }
    
    // Simulate assessment
    $difficulty_levels = ['Easy', 'Moderate', 'Challenging', 'Difficult'];
    $difficulty = $difficulty_levels[array_rand($difficulty_levels)];
    
    $tips = [
        'Easy' => "You're doing well in this course! Consider challenging yourself with additional materials.",
        'Moderate' => "You're performing at an average level. Keep practicing and reviewing regularly.",
        'Challenging' => "This course may require extra effort. Focus on weak areas and seek help from your instructor.",
        'Difficult' => "You're finding this course challenging. Consider forming study groups or getting tutoring."
    ];
    
    return [
        'difficulty' => $difficulty,
        'tip' => $tips[$difficulty],
        'recommended_actions' => [
            "Review previous materials",
            "Attend office hours",
            "Form a study group",
            "Practice with sample problems"
        ]
    ];
}
// M-Pesa payment functions
function initiateMpesaPayment($user_id, $material_id, $amount, $phone_number) {
    // In a real implementation, you would integrate with M-Pesa Daraja API
    // This is a simulation for demonstration purposes
    
    // Generate a unique reference
    $reference = 'MPESA_' . uniqid();
    
    // Create payment record
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, method, reference, status) VALUES (?, ?, 'mpesa', ?, 'pending')");
    if ($stmt->execute([$user_id, $amount, $reference])) {
        $payment_id = $pdo->lastInsertId();
        
        // Create purchase record
        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, material_id, payment_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $material_id, $payment_id])) {
            // In a real implementation, you would call M-Pesa API here
            // For now, we'll simulate success
            return [
                'success' => true,
                'message' => 'M-Pesa payment initiated successfully',
                'checkout_url' => 'https://mpesa.example.com/checkout/' . $reference,
                'reference' => $reference
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create purchase record'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Failed to create payment record'
        ];
    }
}

function processMpesaCallback($reference, $transaction_id, $status) {
    global $pdo;
    
    // Update payment status
    $stmt = $pdo->prepare("UPDATE payments SET transaction_id = ?, status = ? WHERE reference = ?");
    if ($stmt->execute([$transaction_id, $status, $reference])) {
        // If payment is completed, grant access to material
        if ($status === 'completed') {
            // Get payment details
            $stmt = $pdo->prepare("SELECT * FROM payments WHERE reference = ?");
            $stmt->execute([$reference]);
            $payment = $stmt->fetch();
            
            if ($payment) {
                // Get purchase details
                $stmt = $pdo->prepare("SELECT * FROM purchases WHERE payment_id = ?");
                $stmt->execute([$payment['id']]);
                $purchase = $stmt->fetch();
                
                if ($purchase) {
                    // Grant access by updating material status
                    $stmt = $pdo->prepare("UPDATE unit_materials SET access_level = 'registered' WHERE id = ?");
                    $stmt->execute([$purchase['material_id']]);
                }
            }
        }
        return true;
    }
    return false;
}

// PayPal payment functions
function initiatePaypalPayment($user_id, $material_id, $amount) {
    // In a real implementation, you would integrate with PayPal API
    // This is a simulation for demonstration purposes
    
    // Generate a unique reference
    $reference = 'PAYPAL_' . uniqid();
    
    // Create payment record
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, amount, method, reference, status) VALUES (?, ?, 'paypal', ?, 'pending')");
    if ($stmt->execute([$user_id, $amount, $reference])) {
        $payment_id = $pdo->lastInsertId();
        
        // Create purchase record
        $stmt = $pdo->prepare("INSERT INTO purchases (user_id, material_id, payment_id) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $material_id, $payment_id])) {
            // In a real implementation, you would redirect to PayPal checkout
            // For now, we'll simulate success
            return [
                'success' => true,
                'message' => 'PayPal payment initiated successfully',
                'checkout_url' => 'https://paypal.example.com/checkout/' . $reference,
                'reference' => $reference
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to create purchase record'
            ];
        }
    } else {
        return [
            'success' => false,
            'message' => 'Failed to create payment record'
        ];
    }
}

// Sales tracking functions
function getAllSales() {
    global $pdo;
    $stmt = $pdo->query("SELECT p.*, u.name as user_name, um.title as material_title FROM payments p JOIN users u ON p.user_id = u.id JOIN purchases pur ON p.id = pur.payment_id JOIN unit_materials um ON pur.material_id = um.id WHERE p.status = 'completed' ORDER BY p.created_at DESC");
    return $stmt->fetchAll();
}

function getMonthlySales() {
    global $pdo;
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count, SUM(amount) as amount FROM payments WHERE status = 'completed' GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC");
    return $stmt->fetchAll();
}

function getTopSellingMaterials() {
    global $pdo;
    $stmt = $pdo->query("SELECT um.*, COUNT(pur.id) as sales_count, SUM(p.amount) as total_revenue, AVG(um.price) as avg_price FROM unit_materials um JOIN purchases pur ON um.id = pur.material_id JOIN payments p ON pur.payment_id = p.id WHERE p.status = 'completed' GROUP BY um.id ORDER BY sales_count DESC LIMIT 10");
    return $stmt->fetchAll();
}

function getSalesByUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, um.title as material_title FROM payments p JOIN purchases pur ON p.id = pur.payment_id JOIN unit_materials um ON pur.material_id = um.id WHERE p.user_id = ? AND p.status = 'completed' ORDER BY p.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getSalesByMaterial($material_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, u.name as user_name FROM payments p JOIN purchases pur ON p.id = pur.payment_id JOIN users u ON p.user_id = u.id WHERE pur.material_id = ? AND p.status = 'completed' ORDER BY p.created_at DESC");
    $stmt->execute([$material_id]);
    return $stmt->fetchAll();
}

function getSalesReport($from_date = null, $to_date = null, $method = null) {
    global $pdo;
    
    $query = "SELECT p.*, u.name as user_name, um.title as material_title FROM payments p JOIN users u ON p.user_id = u.id JOIN purchases pur ON p.id = pur.payment_id JOIN unit_materials um ON pur.material_id = um.id WHERE p.status = 'completed'";
    $params = [];
    
    if ($from_date) {
        $query .= " AND p.created_at >= ?";
        $params[] = $from_date;
    }
    
    if ($to_date) {
        $query .= " AND p.created_at <= ?";
        $params[] = $to_date;
    }
    
    if ($method) {
        $query .= " AND p.method = ?";
        $params[] = $method;
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Revenue calculation functions
function getTotalRevenue() {
    global $pdo;
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getMonthlyRevenue($month = null) {
    global $pdo;
    
    if ($month) {
        $stmt = $pdo->prepare("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = ?");
        $stmt->execute([$month]);
    } else {
        $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed' AND DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
    }
    
    $result = $stmt->fetch();
    return $result['total'] ?? 0;
}

function getRevenueByMaterial() {
    global $pdo;
    $stmt = $pdo->query("SELECT um.title, SUM(p.amount) as revenue FROM payments p JOIN purchases pur ON p.id = pur.payment_id JOIN unit_materials um ON pur.material_id = um.id WHERE p.status = 'completed' GROUP BY um.id ORDER BY revenue DESC");
    return $stmt->fetchAll();
}

function getRevenueByUser() {
    global $pdo;
    $stmt = $pdo->query("SELECT u.name, SUM(p.amount) as revenue FROM payments p JOIN users u ON p.user_id = u.id WHERE p.status = 'completed' GROUP BY u.id ORDER BY revenue DESC");
    return $stmt->fetchAll();
}

// Export functions
function exportSalesToCSV($sales_data) {
    $filename = 'sales_report_' . date('Y-m-d') . '.csv';
    $fp = fopen('php://output', 'w');
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Add CSV headers
    fputcsv($fp, ['ID', 'User', 'Material', 'Amount', 'Method', 'Reference', 'Date', 'Status']);
    
    // Add data rows
    foreach ($sales_data as $sale) {
        fputcsv($fp, [
            $sale['id'],
            $sale['user_name'],
            $sale['material_title'] ?? 'N/A',
            'KES ' . number_format($sale['amount'], 2),
            $sale['method'],
            $sale['reference'] ?? 'N/A',
            date('Y-m-d H:i:s', strtotime($sale['created_at'])),
            $sale['status']
        ]);
    }
    
    fclose($fp);
    exit();
}

// Material functions
function getMaterialsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT um.*, u.name as unit_name, p.name as program_name, d.name as department_name FROM unit_materials um JOIN units u ON um.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id JOIN courses c ON u.id = c.unit_id WHERE c.trainer_id = ? AND um.status = 'published' ORDER BY um.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function createMaterial($unit_id, $title, $description, $type, $access_level, $price, $file_path, $cover_image = null) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO unit_materials (unit_id, title, description, type, access_level, price, file_path, cover_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published')");
    return $stmt->execute([$unit_id, $title, $description, $type, $access_level, $price, $file_path, $cover_image]);
}

function updateMaterial($id, $title, $description, $type, $access_level, $price, $cover_image = null) {
    global $pdo;
    if ($cover_image) {
        $stmt = $pdo->prepare("UPDATE unit_materials SET title = ?, description = ?, type = ?, access_level = ?, price = ?, cover_image = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $access_level, $price, $cover_image, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE unit_materials SET title = ?, description = ?, type = ?, access_level = ?, price = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $type, $access_level, $price, $id]);
    }
}

function deleteMaterial($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE unit_materials SET status = 'archived' WHERE id = ?");
    return $stmt->execute([$id]);
}

// User functions
function getUserPurchases($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT pur.*, um.title as material_title, um.type as material_type, p.amount, p.method, p.reference FROM purchases pur JOIN unit_materials um ON pur.material_id = um.id JOIN payments p ON pur.payment_id = p.id WHERE pur.user_id = ? AND p.status = 'completed' ORDER BY pur.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getPurchaseById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT pur.*, um.title as material_title, um.type as material_type, p.amount, p.method, p.reference, u.name as user_name FROM purchases pur JOIN unit_materials um ON pur.material_id = um.id JOIN payments p ON pur.payment_id = p.id JOIN users u ON pur.user_id = u.id WHERE pur.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getDepartmentsByLevel($level) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE level = ? AND status = 'active' ORDER BY name ASC");
    $stmt->execute([$level]);
    return $stmt->fetchAll();
}

function getProgramsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, d.name as department_name FROM programs p JOIN departments d ON p.department_id = d.id JOIN courses c ON p.id = c.program_id WHERE c.trainer_id = ? AND p.status = 'active' GROUP BY p.id ORDER BY d.name, p.name ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getUnitsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, p.name as program_name, d.name as department_name FROM units u JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id JOIN courses c ON u.id = c.unit_id WHERE c.trainer_id = ? AND u.status = 'active' ORDER BY u.year ASC, u.semester ASC, u.name ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getEbooksByUnit($unit_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM unit_materials WHERE unit_id = ? AND type = 'ebook' AND status = 'published' ORDER BY created_at DESC");
    $stmt->execute([$unit_id]);
    return $stmt->fetchAll();
}

// Live class functions
function getClassesByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT cl.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM classes cl JOIN courses c ON cl.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? ORDER BY cl.start_time ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function startClass($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE classes SET status = 'in_progress' WHERE id = ?");
    return $stmt->execute([$id]);
}

function endClass($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE classes SET status = 'completed' WHERE id = ?");
    return $stmt->execute([$id]);
}

function getUpcomingClassesByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT cl.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM classes cl JOIN courses c ON cl.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? AND cl.start_time > NOW() AND cl.status = 'scheduled' ORDER BY cl.start_time ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getPastClassesByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT cl.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM classes cl JOIN courses c ON cl.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? AND (cl.end_time < NOW() OR cl.status = 'completed') ORDER BY cl.start_time DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}
// Assignment functions
function getAssignmentsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM assignments a JOIN courses c ON a.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? AND a.status = 'active' ORDER BY a.due_date ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function updateAssignment($id, $title, $description, $due_date, $max_points, $file_path = null) {
    global $pdo;
    if ($file_path) {
        $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ?, max_points = ?, file_path = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $due_date, $max_points, $file_path, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ?, max_points = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $due_date, $max_points, $id]);
    }
}

function deleteAssignment($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE assignments SET status = 'inactive' WHERE id = ?");
    return $stmt->execute([$id]);
}

function getActiveAssignmentsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM assignments a JOIN courses c ON a.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? AND a.status = 'active' AND a.due_date > NOW() ORDER BY a.due_date ASC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getPastAssignmentsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name as course_name, u.name as unit_name, p.name as program_name FROM assignments a JOIN courses c ON a.course_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id WHERE c.trainer_id = ? AND (a.status = 'inactive' OR a.due_date < NOW()) ORDER BY a.due_date DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getAssignmentSubmissions($assignment_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT asub.*, u.name as student_name FROM assignment_submissions asub JOIN users u ON asub.user_id = u.id WHERE asub.assignment_id = ? ORDER BY asub.submitted_at DESC");
    $stmt->execute([$assignment_id]);
    return $stmt->fetchAll();
}



function returnAssignmentSubmission($submission_id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE assignment_submissions SET status = 'returned', returned_at = NOW() WHERE id = ?");
    return $stmt->execute([$submission_id]);
}

// Forum functions
function getForumTopicsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, c.name as course_name, p.name as program_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id JOIN courses c ON ft.course_id = c.id JOIN units un ON c.unit_id = un.id JOIN programs p ON un.program_id = p.id WHERE c.trainer_id = ? ORDER BY ft.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getForumTopicsByCourse($course_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author FROM forum_topics ft JOIN users u ON ft.user_id = u.id WHERE ft.course_id = ? ORDER BY ft.created_at DESC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll();
}

function updateForumTopic($id, $title, $content, $category) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET title = ?, content = ?, category = ? WHERE id = ?");
    return $stmt->execute([$title, $content, $category, $id]);
}

function deleteForumTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET status = 'closed' WHERE id = ?");
    return $stmt->execute([$id]);
}

function pinForumTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET status = 'pinned' WHERE id = ?");
    return $stmt->execute([$id]);
}

function unpinForumTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET status = 'open' WHERE id = ?");
    return $stmt->execute([$id]);
}

function closeForumTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET status = 'closed' WHERE id = ?");
    return $stmt->execute([$id]);
}

function openForumTopic($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_topics SET status = 'open' WHERE id = ?");
    return $stmt->execute([$id]);
}

function getOpenForumTopicsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, c.name as course_name, p.name as program_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id JOIN courses c ON ft.course_id = c.id JOIN units un ON c.unit_id = un.id JOIN programs p ON un.program_id = p.id WHERE c.trainer_id = ? AND ft.status = 'open' ORDER BY ft.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getPinnedForumTopicsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, c.name as course_name, p.name as program_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id JOIN courses c ON ft.course_id = c.id JOIN units un ON c.unit_id = un.id JOIN programs p ON un.program_id = p.id WHERE c.trainer_id = ? AND ft.status = 'pinned' ORDER BY ft.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

function getClosedForumTopicsByTrainer($trainer_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, c.name as course_name, p.name as program_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id JOIN courses c ON ft.course_id = c.id JOIN units un ON c.unit_id = un.id JOIN programs p ON un.program_id = p.id WHERE c.trainer_id = ? AND ft.status = 'closed' ORDER BY ft.created_at DESC");
    $stmt->execute([$trainer_id]);
    return $stmt->fetchAll();
}

// Helper functions
function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

function formatShortDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function timeAgo($timestamp) {
    $diff = time() - strtotime($timestamp);
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } else {
        return floor($diff / 86400) . ' days ago';
    }
}

function getFileIcon($type) {
    switch($type) {
        case 'lecture_note': return '📝';
        case 'assignment': return '📋';
        case 'video': return '🎬';
        case 'ebook': return '📖';
        default: return '📁';
    }
}

function getStatusBadge($status) {
    switch($status) {
        case 'active': return '<span class="badge badge-success">Active</span>';
        case 'inactive': return '<span class="badge badge-danger">Inactive</span>';
        case 'pending': return '<span class="badge badge-warning">Pending</span>';
        case 'suspended': return '<span class="badge badge-danger">Suspended</span>';
        case 'published': return '<span class="badge badge-success">Published</span>';
        case 'draft': return '<span class="badge badge-warning">Draft</span>';
        case 'archived': return '<span class="badge badge-secondary">Archived</span>';
        case 'open': return '<span class="badge badge-success">Open</span>';
        case 'closed': return '<span class="badge badge-danger">Closed</span>';
        case 'pinned': return '<span class="badge badge-warning">Pinned</span>';
        case 'scheduled': return '<span class="badge badge-info">Scheduled</span>';
        case 'in_progress': return '<span class="badge badge-warning">In Progress</span>';
        case 'completed': return '<span class="badge badge-success">Completed</span>';
        case 'cancelled': return '<span class="badge badge-danger">Cancelled</span>';
        case 'submitted': return '<span class="badge badge-info">Submitted</span>';
        case 'graded': return '<span class="badge badge-success">Graded</span>';
        case 'returned': return '<span class="badge badge-secondary">Returned</span>';
        default: return '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }
}
// Student functions
function getStudents() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'trainee' AND status = 'active' ORDER BY name ASC");
    return $stmt->fetchAll();
}

function getAllStudents() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'trainee' ORDER BY name ASC");
    return $stmt->fetchAll();
}

function getStudentById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'trainee'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getStudentEnrollments($student_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT e.*, p.name as program_name, d.name as department_name FROM enrollments e JOIN programs p ON e.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.user_id = ? AND e.status = 'active'");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}

function getStudentRegisteredUnits($student_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ur.*, u.name as unit_name, u.code as unit_code, p.name as program_name, d.name as department_name FROM unit_registrations ur JOIN units u ON ur.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id JOIN enrollments e ON ur.enrollment_id = e.id WHERE e.user_id = ? AND ur.status = 'registered'");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}

// Enrollment functions
function getAllEnrollments() {
    global $pdo;
    $stmt = $pdo->query("SELECT e.*, u.name as student_name, p.name as program_name, d.name as department_name FROM enrollments e JOIN users u ON e.user_id = u.id JOIN programs p ON e.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.status = 'active' ORDER BY e.enrollment_date DESC");
    return $stmt->fetchAll();
}

function createEnrollment($user_id, $program_id) {
    global $pdo;
    $enrollment_date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO enrollments (user_id, program_id, enrollment_date, status) VALUES (?, ?, ?, 'active')");
    return $stmt->execute([$user_id, $program_id, $enrollment_date]);
}

function updateEnrollment($id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE enrollments SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

function deleteEnrollment($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
    return $stmt->execute([$id]);
}

// Unit registration functions
function getAllRegisteredUnits() {
    global $pdo;
    $stmt = $pdo->query("SELECT ur.*, u.name as unit_name, u.code as unit_code, p.name as program_name, d.name as department_name, usr.name as student_name FROM unit_registrations ur JOIN units u ON ur.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id JOIN enrollments e ON ur.enrollment_id = e.id JOIN users usr ON e.user_id = usr.id WHERE ur.status = 'registered' ORDER BY ur.registration_date DESC");
    return $stmt->fetchAll();
}

function unregisterUnit($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE unit_registrations SET status = 'dropped' WHERE id = ?");
    return $stmt->execute([$id]);
}

// Exam functions
function createExam($unit_id, $title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO exams (unit_id, title, description, exam_date, start_time, end_time, max_points, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
    return $stmt->execute([$unit_id, $title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type]);
}

function updateExam($id, $title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE exams SET title = ?, description = ?, exam_date = ?, start_time = ?, end_time = ?, max_points = ?, exam_type = ? WHERE id = ?");
    return $stmt->execute([$title, $description, $exam_date, $start_time, $end_time, $max_points, $exam_type, $id]);
}

function deleteExam($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE exams SET status = 'cancelled' WHERE id = ?");
    return $stmt->execute([$id]);
}

function getAllExams() {
    global $pdo;
    $stmt = $pdo->query("SELECT e.*, u.name as unit_name, p.name as program_name, d.name as department_name FROM exams e JOIN units u ON e.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.status != 'cancelled' ORDER BY e.exam_date DESC, e.start_time DESC");
    return $stmt->fetchAll();
}

function getExamsByUnit($unit_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE unit_id = ? AND status != 'cancelled' ORDER BY exam_date DESC, start_time DESC");
    $stmt->execute([$unit_id]);
    return $stmt->fetchAll();
}

function getExamById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT e.*, u.name as unit_name, p.name as program_name, d.name as department_name FROM exams e JOIN units u ON e.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getExamsWithResults() {
    global $pdo;
    $stmt = $pdo->query("SELECT e.*, u.name as unit_name, p.name as program_name, d.name as department_name, COUNT(er.id) as results_count FROM exams e JOIN units u ON e.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id LEFT JOIN exam_results er ON e.id = er.exam_id WHERE e.status != 'cancelled' GROUP BY e.id ORDER BY e.exam_date DESC, e.start_time DESC");
    return $stmt->fetchAll();
}

function getRecentExams($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT e.*, u.name as unit_name, p.name as program_name, d.name as department_name FROM exams e JOIN units u ON e.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE e.status != 'cancelled' ORDER BY e.exam_date DESC, e.start_time DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Exam result functions
function recordExamResult($exam_id, $student_id, $points_awarded) {
    global $pdo;
    // Check if result already exists
    $stmt = $pdo->prepare("SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ?");
    $stmt->execute([$exam_id, $student_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing result
        $stmt = $pdo->prepare("UPDATE exam_results SET points_awarded = ?, graded_at = NOW() WHERE id = ?");
        return $stmt->execute([$points_awarded, $existing['id']]);
    } else {
        // Create new result
        $stmt = $pdo->prepare("INSERT INTO exam_results (exam_id, student_id, points_awarded, graded_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$exam_id, $student_id, $points_awarded]);
    }
}

function getExamResults($exam_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT er.*, u.name as student_name, e.max_points FROM exam_results er JOIN users u ON er.student_id = u.id JOIN exams e ON er.exam_id = e.id WHERE er.exam_id = ? ORDER BY er.points_awarded DESC");
    $stmt->execute([$exam_id]);
    return $stmt->fetchAll();
}

function getStudentExamResults($student_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT er.*, e.title as exam_title, u.name as unit_name, p.name as program_name, d.name as department_name, e.max_points FROM exam_results er JOIN exams e ON er.exam_id = e.id JOIN units u ON e.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE er.student_id = ? ORDER BY er.graded_at DESC");
    $stmt->execute([$student_id]);
    return $stmt->fetchAll();
}

function exportResultsToCSV($exam_id) {
    global $pdo;
    
    // Get exam details
    $exam = getExamById($exam_id);
    if (!$exam) {
        return false;
    }
    
    // Get results
    $results = getExamResults($exam_id);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="exam_results_' . $exam['title'] . '_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Student ID', 'Student Name', 'Points Awarded', 'Max Points', 'Percentage', 'Grade']);
    
    // Add data rows
    foreach ($results as $result) {
        $percentage = ($result['points_awarded'] / $exam['max_points']) * 100;
        
        // Determine grade
        if ($percentage >= 85) {
            $grade = 'A';
        } elseif ($percentage >= 75) {
            $grade = 'B+';
        } elseif ($percentage >= 65) {
            $grade = 'B';
        } elseif ($percentage >= 55) {
            $grade = 'C+';
        } elseif ($percentage >= 45) {
            $grade = 'C';
        } elseif ($percentage >= 35) {
            $grade = 'D';
        } else {
            $grade = 'F';
        }
        
        fputcsv($output, [
            $result['student_id'],
            $result['student_name'],
            $result['points_awarded'],
            $exam['max_points'],
            round($percentage, 2) . '%',
            $grade
        ]);
    }
    
    fclose($output);
    exit();
}

function getRecentResults($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT er.*, u.name as student_name, e.title as exam_title, un.name as unit_name, p.name as program_name, d.name as department_name FROM exam_results er JOIN users u ON er.student_id = u.id JOIN exams e ON er.exam_id = e.id JOIN units un ON e.unit_id = un.id JOIN programs p ON un.program_id = p.id JOIN departments d ON p.department_id = d.id ORDER BY er.graded_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Attendance functions
function getAttendanceRecordsBySession($session_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ar.*, u.name as student_name FROM attendance_records ar JOIN users u ON ar.user_id = u.id WHERE ar.session_id = ? ORDER BY ar.joined_at DESC");
    $stmt->execute([$session_id]);
    return $stmt->fetchAll();
}

function getAllSessions() {
    global $pdo;
    $stmt = $pdo->query("SELECT s.*, c.title as class_title, u.name as unit_name, p.name as program_name, d.name as department_name FROM sessions s JOIN classes c ON s.class_id = c.id JOIN units u ON c.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id ORDER BY s.start_time DESC");
    return $stmt->fetchAll();
}

function getRecentAttendanceRecords($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ar.*, u.name as student_name, s.title as session_title, c.title as class_title, un.name as unit_name, p.name as program_name, d.name as department_name FROM attendance_records ar JOIN users u ON ar.user_id = u.id JOIN sessions s ON ar.session_id = s.id JOIN classes c ON s.class_id = c.id JOIN units un ON c.unit_id = un.id JOIN programs p ON un.program_id = p.id JOIN departments d ON p.department_id = d.id ORDER BY ar.joined_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function updateAttendanceStatus($record_id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE attendance_records SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $record_id]);
}

function exportAttendanceToCSV($session_id) {
    global $pdo;
    
    // Get session details
    $session = getSessionById($session_id);
    if (!$session) {
        return false;
    }
    
    // Get attendance records
    $records = getAttendanceRecordsBySession($session_id);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="attendance_' . $session['title'] . '_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Student ID', 'Student Name', 'Joined At', 'Left At', 'Status']);
    
    // Add data rows
    foreach ($records as $record) {
        fputcsv($output, [
            $record['user_id'],
            $record['student_name'],
            $record['joined_at'] ? date('Y-m-d H:i:s', strtotime($record['joined_at'])) : 'N/A',
            $record['left_at'] ? date('Y-m-d H:i:s', strtotime($record['left_at'])) : 'N/A',
            ucfirst($record['status'])
        ]);
    }
    
    fclose($output);
    exit();
}

function getAttendanceSummary($session_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late
        FROM attendance_records WHERE session_id = ?");
    $stmt->execute([$session_id]);
    return $stmt->fetch();
}

// Assignment functions
function getRecentAssignments($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, u.name as unit_name, p.name as program_name, d.name as department_name FROM assignments a JOIN units u ON a.unit_id = u.id JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE a.status = 'active' ORDER BY a.due_date DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Unit functions
function getAllUnits() {
    global $pdo;
    $stmt = $pdo->query("SELECT u.*, p.name as program_name, d.name as department_name FROM units u JOIN programs p ON u.program_id = p.id JOIN departments d ON p.department_id = d.id WHERE u.status = 'active' ORDER BY d.name, p.name, u.year, u.semester, u.name");
    return $stmt->fetchAll();
}

// Forum functions
function getRecentForumTopics($limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ft.*, u.name as author, un.name as unit_name, p.name as program_name, d.name as department_name FROM forum_topics ft JOIN users u ON ft.user_id = u.id JOIN units un ON ft.unit_id = un.id JOIN programs p ON un.program_id = p.id JOIN departments d ON p.department_id = d.id ORDER BY ft.created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getForumReplies($topic_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT fr.*, u.name as author FROM forum_replies fr JOIN users u ON fr.user_id = u.id WHERE fr.topic_id = ? ORDER BY fr.created_at ASC");
    $stmt->execute([$topic_id]);
    return $stmt->fetchAll();
}

function updateForumReply($id, $content) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE forum_replies SET content = ?, updated_at = NOW() WHERE id = ?");
    return $stmt->execute([$content, $id]);
}

function deleteForumReply($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM forum_replies WHERE id = ?");
    return $stmt->execute([$id]);
}

function getCertificatesByUser($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, p.name as program_name FROM certificates c JOIN programs p ON c.program_id = p.id WHERE c.user_id = ? AND c.status = 'active' ORDER BY c.issued_date DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getCertificateById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT c.*, p.name as program_name, u.name as user_name FROM certificates c JOIN programs p ON c.program_id = p.id JOIN users u ON c.user_id = u.id WHERE c.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function updateEnrollmentStatus($enrollment_id, $status) {
    global $pdo;
    
    // Validate status
    $validStatuses = ['active', 'completed', 'dropped', 'suspended'];
    if (!in_array($status, $validStatuses)) {
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE enrollments SET status = ?, updated_at = NOW() WHERE id = ?");
        $result = $stmt->execute([$status, $enrollment_id]);
        
        if ($result) {
            // If enrollment is completed, update related unit registrations
            if ($status === 'completed') {
                $stmt = $pdo->prepare("UPDATE unit_registrations SET status = 'completed' WHERE enrollment_id = ?");
                $stmt->execute([$enrollment_id]);
            }
            
            // If enrollment is dropped, update related unit registrations
            if ($status === 'dropped') {
                $stmt = $pdo->prepare("UPDATE unit_registrations SET status = 'dropped' WHERE enrollment_id = ?");
                $stmt->execute([$enrollment_id]);
            }
            
            return true;
        }
        return false;
    } catch (PDOException $e) {
        // Log error for debugging
        error_log("Failed to update enrollment status: " . $e->getMessage());
        return false;
    }
}
?>
