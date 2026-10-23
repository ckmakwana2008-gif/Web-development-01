<?php
/* ==========================================================================
   StudentHub Session Security & RBAC Middleware Component
   Course Code: ITUE203 - Practical 10
   ========================================================================== */

// Configure Secure Session Parameters before starting session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session cookie
    ini_set('session.use_only_cookies', 1); // Prevent session ID in URL parameters
    ini_set('session.cookie_samesite', 'Lax'); // Cross-Site Request Forgery (CSRF) mitigation
    session_start();
}

/**
 * Validates active session and enforces inactivity timeout (e.g., 15 minutes = 900s)
 */
function checkSessionTimeout(int $maxInactivitySeconds = 900): void {
    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxInactivitySeconds)) {
            // Inactivity threshold reached: destroy session and redirect
            session_unset();
            session_destroy();
            header("Location: login.php?timeout=1");
            exit();
        }
        // Update last activity timestamp on each active request
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Access Control Guard: Enforces authentication and specific role requirements
 */
function requireAuth(string $requiredRole = null): void {
    checkSessionTimeout();

    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=unauthorized");
        exit();
    }

    // 2. Check Role-Based Access if specified
    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        // Redirect user to their own valid dashboard if role does not match
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: student_dashboard.php");
        }
        exit();
    }
}
?>
