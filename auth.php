<?php
// Session Management & Authentication
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']) && isset($_SESSION['role']);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit();
    }
}

// Require specific role
function requireRole($requiredRoles) {
    requireLogin();
    
    if (!is_array($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }
    
    if (!in_array($_SESSION['role'], $requiredRoles)) {
        header("Location: " . BASE_URL . "404.php");
        exit();
    }
}

// Redirect berdasarkan role setelah login
function redirectByRole($role) {
    switch($role) {
        case 'admin':
            header("Location: " . BASE_URL . "admin/index.php");
            break;
        case 'petugas':
            header("Location: " . BASE_URL . "petugas/index.php");
            break;
        case 'peminjam':
            header("Location: " . BASE_URL . "peminjam/index.php");
            break;
        default:
            header("Location: " . BASE_URL . "login.php");
    }
    exit();
}

// CSRF Token Generation
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF Token
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Logout
function logout() {
    session_destroy();
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Get Current User Info
function getCurrentUser($conn) {
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND status = 'aktif'");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    // User tidak ditemukan atau nonaktif
    logout();
}

// Log Activity
function logActivity($conn, $user_id, $aktivitas, $deskripsi = null, $tabel = null, $record_id = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare("INSERT INTO log_aktivitas (user_id, aktivitas, deskripsi, tabel, record_id, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssii", $user_id, $aktivitas, $deskripsi, $tabel, $record_id, $ip_address);
    return $stmt->execute();
}

// Hash Password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

// Verify Password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Set base URL
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/bangjo/');
}
