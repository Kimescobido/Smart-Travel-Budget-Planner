<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures user is authenticated.
 * If not, redirects to login page.
 */
function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false || strpos($uri, '/admin/') !== false) {
            header("Location: ../login.php?error=unauthorized");
        } else {
            header("Location: login.php?error=unauthorized");
        }
        exit;
    }
}

/**
 * Ensures user is authenticated AND has the 'admin' role.
 * If not admin, shows 403 Forbidden or redirects.
 */
function requireAdmin()
{
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light text-center py-5'><div class='container'><h1 class='display-4 text-danger'>403 Forbidden</h1><p class='lead'>Access Denied. You must be an administrator to view this page.</p><a href='../index.php' class='btn btn-primary'>Return to Home</a></div></body></html>";
        exit;
    }
}
