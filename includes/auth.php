<?php
session_start();

// config
define('SITE_NAME', 'Hotel Management System');
define('SITE_URL', 'http://localhost/ungdungweb');

// redirect
function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit;
}

// login check
function isLoggedIn() {
    return isset($_SESSION['user']);
}

// admin check
function isAdmin() {
    return isset($_SESSION['user']) && (
        $_SESSION['user']['is_admin'] === 'admin' || 
        $_SESSION['user']['is_admin'] === '1' || 
        $_SESSION['user']['is_admin'] === 1
    );
}

// require login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

// require admin
function requireAdmin() {
    if (!isAdmin()) {
        redirect('index.php');
    }
}

// flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// set flash message
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type
    ];
} 