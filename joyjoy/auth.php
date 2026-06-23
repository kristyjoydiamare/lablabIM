<?php
// Always start or resume the session at the very peak of execution
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user session is active.
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user holds an administrator role.
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    
    requireLogin();
    
    if (!isAdmin()) {
        http_response_code(403);
        die("Access denied: Administrator privileges required.");
    }
}

/**
 * Safe accessor for pulling the current username string.
 * @return string|null
 */
function getUsername() {
    return $_SESSION['username'] ?? null;
}