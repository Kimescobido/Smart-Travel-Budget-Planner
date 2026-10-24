<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Protect this page - only admins allowed!
requireAdmin();

$page_title = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shield-lock text-warning"></i> Administrator Dashboard</h2>
    <span class="badge bg-danger fs-6">Admin Mode</span>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle-fill"></i> Welcome, <strong>
        <?= htmlspecialchars($_SESSION['user_name']) ?>
    </strong>! RBAC security check passed. You have full administrative access.
</div>

<p class="text-muted">In Phase 9, we will populate this dashboard with full system metrics, active users, and travel
    analytics.</p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>