<?php
session_start();

// Prevent caching of protected pages
function preventCache()
{
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Check if user has specific role
function hasRole($role)
{
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['user_role'] === $role;
}

// Check if user is admin
function isAdmin()
{
    return hasRole('admin');
}

// Check if user is employee or admin
function isEmployee()
{
    return hasRole('employee') || hasRole('admin');
}

// Require login
function requireLogin()
{
    preventCache();
    if (!isLoggedIn()) {
        header('Location: /../../public/pages/login.php');
        exit();
    }
}

// Require specific role
function requireRole($role)
{
    preventCache();
    if (!hasRole($role)) {
        header('Location: /../../401.shtml');
        exit();
    }
}

// Require employee or admin access
function requireEmployee()
{
    preventCache();
    if (!isEmployee()) {
        header('Location: /../../401.shtml');
        exit();
    }
}

// Get current user info
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['user_role']
    ];
}

// Logout user
function logout()
{
    // Clear all session variables
    $_SESSION = array();

    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_unset();
    session_destroy();

    // Prevent caching
    preventCache();

    // Redirect to login
    header('Location: /../../public/pages/login.php');
    exit();
}

// Sanitize input
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format currency
function formatCurrency($amount)
{
    return '₱' . number_format($amount, 2);
}

// Generate order number
function generateOrderNumber()
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
