<?php
session_start();

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
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
    if (!isLoggedIn()) {
        header('Location: /Bros-Cafe/public/pages/login.php');
        exit();
    }
}

// Require specific role
function requireRole($role)
{
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /Bros-Cafe/public/pages/unauthorized.php');
        exit();
    }
}

// Require employee or admin access
function requireEmployee()
{
    requireLogin();
    if (!isEmployee()) {
        header('Location: /Bros-Cafe/public/pages/unauthorized.php');
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
    session_unset();
    session_destroy();
    header('Location: /Bros-Cafe/public/pages/login.php');
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
