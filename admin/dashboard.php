<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strictly protect this route - only users with role === 'admin'
requireAdmin();

$page_title = "Admin Dashboard";

// 1. Fetch System Metrics
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalTrips = $pdo->query("SELECT COUNT(*) FROM trips")->fetchColumn();
$totalActivities = $pdo->query("SELECT COUNT(*) FROM itinerary_items")->fetchColumn();

// 2. Fetch Recent Users
$usersStmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 10");
$users = $usersStmt->fetchAll();

// 3. Fetch All Platform Trips with Author info
$tripsStmt = $pdo->query("
    SELECT t.id, t.title, t.destination_city, t.start_date, t.end_date, t.total_budget, t.base_currency, u.name AS author_name, u.email AS author_email
    FROM trips t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$allTrips = $tripsStmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0"><i class="bi bi-shield-lock-fill text-warning"></i> Administrator Dashboard</h2>
        <p class="text-muted small mb-0">System metrics, platform activity, and management</p>
    </div>
    <span class="badge bg-danger fs-6 px-3 py-2">
        <i class="bi bi-person-badge"></i> Admin:
        <?= htmlspecialchars($_SESSION['user_name']) ?>
    </span>
</div>

<!-- 1. Metric Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small">Total Users</h6>
                    <h2 class="mb-0 fw-bold text-primary">
                        <?= number_format($totalUsers) ?>
                    </h2>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small">Total Trips Planned</h6>
                    <h2 class="mb-0 fw-bold text-success">
                        <?= number_format($totalTrips) ?>
                    </h2>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="bi bi-airplane-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1 text-uppercase small">Itinerary Items</h6>
                    <h2 class="mb-0 fw-bold text-info">
                        <?= number_format($totalActivities) ?>
                    </h2>
                </div>
                <div class="p-3 bg-info-subtle text-info rounded-circle">
                    <i class="bi bi-calendar-check-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Management Tables -->
<div class="row g-4">
    <!-- All Trips Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-globe-americas text-primary"></i> All Platform Itineraries</h5>
                <span class="badge bg-secondary">
                    <?= count($allTrips) ?> Total
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($allTrips)): ?>
                    <p class="text-muted text-center py-4">No trips have been created yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Trip Title</th>
                                    <th>Destination</th>
                                    <th>Author</th>
                                    <th>Budget</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allTrips as $t): ?>
                                    <tr>
                                        <td><strong>
                                                <?= htmlspecialchars($t['title']) ?>
                                            </strong></td>
                                        <td><span class="badge bg-info-subtle text-dark border">
                                                <?= htmlspecialchars($t['destination_city']) ?>
                                            </span></td>
                                        <td>
                                            <div class="small fw-semibold">
                                                <?= htmlspecialchars($t['author_name']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <?= htmlspecialchars($t['author_email']) ?>
                                            </div>
                                        </td>
                                        <td class="small">
                                            <?= htmlspecialchars($t['base_currency']) ?>
                                            <?= number_format($t['total_budget'], 2) ?>
                                        </td>
                                        <td>
                                            <a href="../trip_view.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"
                                                title="View"><i class="bi bi-eye"></i></a>
                                            <a href="../trip_delete.php?id=<?= $t['id'] ?>"
                                                class="btn btn-sm btn-outline-danger" title="Moderate/Delete"
                                                onclick="return confirm('As admin, are you sure you want to delete this trip?');"><i
                                                    class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Registered Users List -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill text-success"></i> Registered Accounts</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($users as $u): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="fw-bold">
                                    <?= htmlspecialchars($u['name']) ?>
                                </div>
                                <div class="text-muted small">
                                    <?= htmlspecialchars($u['email']) ?>
                                </div>
                                <div class="text-muted" style="font-size: 10px;">Joined:
                                    <?= date('M d, Y', strtotime($u['created_at'])) ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">User</span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>