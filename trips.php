<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();

$page_title = "My Trips";

// Fetch only the trips belonging to this logged-in user
$stmt = $pdo->prepare("SELECT * FROM trips WHERE user_id = :uid ORDER BY created_at DESC");
$stmt->execute(['uid' => $_SESSION['user_id']]);
$trips = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-airplane-engines text-primary"></i> My Trips</h2>
    <a href="trip_create.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Plan New Trip</a>
</div>

<?php if (empty($trips)): ?>
    <div class="card p-5 text-center border-0 shadow-sm">
        <i class="bi bi-map text-muted" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No trips found</h4>
        <p class="text-muted">You haven't created any travel itineraries yet.</p>
        <div>
            <a href="trip_create.php" class="btn btn-primary">Create Your First Trip</a>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($trips as $trip): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">
                                <?= htmlspecialchars($trip['title']) ?>
                            </h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                <?= htmlspecialchars($trip['destination_city']) ?>
                            </span>
                        </div>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-calendar-event"></i>
                            <?= date('M d, Y', strtotime($trip['start_date'])) ?> &rarr;
                            <?= date('M d, Y', strtotime($trip['end_date'])) ?>
                        </p>
                        <p class="card-text small">
                            <strong>Budget:</strong>
                            <?= htmlspecialchars($trip['base_currency']) ?>
                            <?= number_format($trip['total_budget'], 2) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 d-flex justify-content-between">
                        <a href="trip_view.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-outline-primary">View Itinerary</a>
                        <div>
                            <a href="trip_edit.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-outline-secondary"
                                title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="trip_delete.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                onclick="return confirm('Are you sure you want to delete this trip?');"><i
                                    class="bi bi-trash"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>