<?php
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'auth/login.php');
        exit;
    }
}

// Check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Format date
function formatDate($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

// Generate order number
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

// Flash messages
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Upload file
function uploadFile($file, $folder = 'uploads') {
    $target_dir = "../assets/$folder/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    if ($file['size'] > 5000000) { // 5MB
        return ['success' => false, 'message' => 'File too large'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

// Pagination
function paginate($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    
    $pagination = [
        'total' => $total,
        'per_page' => $per_page,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'has_prev' => $current_page > 1,
        'has_next' => $current_page < $total_pages
    ];
    
    return $pagination;
}

// Get status badge
function getStatusBadge($status) {
    $badges = [
        'active' => 'badge-success',
        'inactive' => 'badge-danger',
        'pending' => 'badge-warning',
        'processing' => 'badge-info',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger',
        'paid' => 'badge-success',
        'unpaid' => 'badge-warning',
        'refunded' => 'badge-danger'
    ];
    
    $class = $badges[$status] ?? 'badge-primary';
    return "<span class='badge $class'>" . ucfirst($status) . "</span>";
}

// Get channel icon
function getChannelIcon($type) {
    $icons = [
        'whatsapp' => '📱',
        'instagram' => '📷',
        'facebook' => '👤',
        'telegram' => '✈️',
        'email' => '📧',
        'website' => '🌐'
    ];
    
    return $icons[$type] ?? '💬';
}

// Send notification (dapat dikembangkan)
function sendNotification($user_id, $title, $message) {
    // Implement notification system (email, push, etc)
    return true;
}

// Log activity
function logActivity($user_id, $action, $details) {
    $data = [
        'user_id' => $user_id,
        'action' => $action,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ];
    
    // Insert to activity log table (create if needed)
    return true;
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone
function isValidPhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}
?>