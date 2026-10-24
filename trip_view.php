<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) {
    header("Location: trips.php");
    exit;
}

// Fetch trip (allow admin or owner)
if ($_SESSION['user_role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = :id");
    $stmt->execute(['id' => $trip_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $trip_id, 'uid' => $_SESSION['user_id']]);
}
$trip = $stmt->fetch();

if (!$trip) {
    echo "Trip not found or unauthorized.";
    exit;
}

// Handle adding an activity/expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $day_number = intval($_POST['day_number'] ?? 1);
    $activity_title = trim($_POST['activity_title'] ?? '');
    $cost_local = floatval($_POST['cost_local'] ?? 0);
    $local_currency = $_POST['local_currency'] ?? $trip['base_currency'];
    $notes = trim($_POST['notes'] ?? '');

    if (!empty($activity_title)) {
        // In Phase 6, ExchangeRate API will automatically convert cost_local into cost_base!
        $cost_base = $cost_local; // placeholder until API connects in Phase 6

        $insertItem = $pdo->prepare("INSERT INTO itinerary_items (trip_id, day_number, activity_title, cost_local, local_currency, cost_base, notes) VALUES (:tid, :day, :title, :cost_l, :curr_l, :cost_b, :notes)");
        $insertItem->execute([
            'tid' => $trip['id'],
            'day' => $day_number,
            'title' => $activity_title,
            'cost_l' => $cost_local,
            'curr_l' => $local_currency,
            'cost_b' => $cost_base,
            'notes' => $notes
        ]);
        header("Location: trip_view.php?id=" . $trip['id']);
        exit;
    }
}

// Handle deleting single activity
if (isset($_GET['delete_item'])) {
    $item_id = intval($_GET['delete_item']);
    $del = $pdo->prepare("DELETE FROM itinerary_items WHERE id = :id AND trip_id = :tid");
    $del->execute(['id' => $item_id, 'tid' => $trip['id']]);
    header("Location: trip_view.php?id=" . $trip['id']);
    exit;
}

// Fetch all itinerary items for this trip
$itemsStmt = $pdo->prepare("SELECT * FROM itinerary_items WHERE trip_id = :tid ORDER BY day_number ASC, id ASC");
$itemsStmt->execute(['tid' => $trip['id']]);
$items = $itemsStmt->fetchAll();

$page_title = $trip['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2><?= htmlspecialchars($trip['title']) ?></h2>
        <span class="badge bg-secondary"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($trip['destination_city']) ?></span>
        <span class="text-muted small ms-2">
            <?= date('M d, Y', strtotime($trip['start_date'])) ?> to <?= date('M d, Y', strtotime($trip['end_date'])) ?>
        </span>
    </div>
    <div>
        <a href="trip_edit.php?id=<?= $trip['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i> Edit Trip</a>
        <a href="trips.php" class="btn btn-outline-dark btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<!-- API Widgets Placeholder row (We will wire these up in Phases 6, 7 & 8) -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 bg-white">
            <h6 class="text-muted"><i class="bi bi-cloud-sun text-warning"></i> Weather Forecast (API #1)</h6>
            <div id="weather-box" class="small text-muted">Will load live weather for <?= htmlspecialchars($trip['destination_city']) ?> in Phase 6.</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 bg-white">
            <h6 class="text-muted"><i class="bi bi-wallet2 text-success"></i> Budget Overview (API #2)</h6>
            <div class="small">
                <strong>Allocated Budget:</strong> <?= htmlspecialchars($trip['base_currency']) ?> <?= number_format($trip['total_budget'], 2) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Itinerary Items list -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Itinerary & Expenses</h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p class="text-muted text-center py-3">No activities or expenses added yet. Use the form on the right to add one!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Activity / Expense</th>
                                    <th>Cost</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><span class="badge bg-primary">Day <?= htmlspecialchars($item['day_number']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($item['activity_title']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['local_currency']) ?> <?= number_format($item['cost_local'], 2) ?></td>
                                        <td class="text-muted small"><?= htmlspecialchars($item['notes'] ?? '-') ?></td>
                                        <td>
                                            <a href="trip_view.php?id=<?= $trip['id'] ?>&delete_item=<?= $item['id'] ?>" class="text-danger small" onclick="return confirm('Delete this item?');"><i class="bi bi-trash"></i></a>
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

    <!-- Form to Add Activity/Expense -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-plus-lg text-primary"></i> Add Plan / Expense</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="trip_view.php?id=<?= $trip['id'] ?>">
                    <input type="hidden" name="add_activity" value="1">
                    <div class="mb-3">
                        <label class="form-label">Day Number</label>
                        <input type="number" name="day_number" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Activity / Item Title *</label>
                        <input type="text" name="activity_title" class="form-control" placeholder="e.g. Museum Ticket" required>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label">Cost</label>
                            <input type="number" step="0.01" name="cost_local" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Currency</label>
                            <select name="local_currency" class="form-select">
                                <option value="<?= htmlspecialchars($trip['base_currency']) ?>"><?= htmlspecialchars($trip['base_currency']) ?></option>
                                <option value="USD">USD</option>
                                <option value="PHP">PHP</option>
                                <option value="EUR">EUR</option>
                                <option value="JPY">JPY</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Opens at 9 AM"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Add to Itinerary</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>