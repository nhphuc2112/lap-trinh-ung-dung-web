<?php
session_start();

date_default_timezone_set('Asia/Ho_Chi_Minh');

define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('ASSETS_PATH', ROOT_PATH . '/assets');

define('BASE_URL', 'http://localhost/ungdungweb');
define('ASSETS_URL', BASE_URL . '/assets');

define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Hotel Management System');

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        redirect('/403.php');
    }
}

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function validateToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function handleError($errno, $errstr, $errfile, $errline) {
    $error = "Error [$errno] $errstr - $errfile:$errline";
    error_log($error);
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "An error occurred. Please try again later.";
        echo "</div>";
    }
    return true;
}

set_error_handler('handleError');

function handleException($e) {
    $error = "Exception: " . $e->getMessage() . " - " . $e->getFile() . ":" . $e->getLine();
    error_log($error);
    if (ini_get('display_errors')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px;'>";
        echo "An error occurred. Please try again later.";
        echo "</div>";
    }
}

set_exception_handler('handleException'); 