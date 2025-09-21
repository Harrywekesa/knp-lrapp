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
?>