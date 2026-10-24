<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();

$page_title = "Edit Trip";
$error = '';
$trip_id = $_GET['id'] ?? null;

if (!$trip_id) {
    header("Location: trips.php");
    exit;
}

// Ensure the user owns this trip (or is admin)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $destination_city = trim($_POST['destination_city'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $base_currency = $_POST['base_currency'] ?? 'USD';
    $total_budget = floatval($_POST['total_budget'] ?? 0);

    if (empty($title) || empty($destination_city) || empty($start_date) || empty($end_date)) {
        $error = "Please fill in all required fields.";
    } elseif ($end_date < $start_date) {
        $error = "End date cannot be earlier than start date.";
    } else {
        $updateStmt = $pdo->prepare("UPDATE trips SET title = :title, destination_city = :city, start_date = :start, end_date = :end, base_currency = :currency, total_budget = :budget WHERE id = :id");
        $updated = $updateStmt->execute([
            'title' => $title,
            'city' => $destination_city,
            'start' => $start_date,
            'end' => $end_date,
            'currency' => $base_currency,
            'budget' => $total_budget,
            'id' => $trip_id
        ]);

        if ($updated) {
            header("Location: trip_view.php?id=" . $trip_id);
            exit;
        } else {
            $error = "Failed to update trip.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="card-title mb-3"><i class="bi bi-pencil-square text-primary"></i> Edit Trip</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="trip_edit.php?id=<?= $trip['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Trip Title *</label>
                        <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($trip['title']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destination City *</label>
                        <input type="text" name="destination_city" class="form-control"
                            value="<?= htmlspecialchars($trip['destination_city']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $trip['start_date'] ?>"
                                required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $trip['end_date'] ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Base Currency</label>
                            <select name="base_currency" class="form-select">
                                <option value="USD" <?= $trip['base_currency'] === 'USD' ? 'selected' : '' ?>>USD ($)
                                </option>
                                <option value="PHP" <?= $trip['base_currency'] === 'PHP' ? 'selected' : '' ?>>PHP (₱)
                                </option>
                                <option value="EUR" <?= $trip['base_currency'] === 'EUR' ? 'selected' : '' ?>>EUR (€)
                                </option>
                                <option value="JPY" <?= $trip['base_currency'] === 'JPY' ? 'selected' : '' ?>>JPY (¥)
                                </option>
                                <option value="GBP" <?= $trip['base_currency'] === 'GBP' ? 'selected' : '' ?>>GBP (£)
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Budget</label>
                            <input type="number" step="0.01" name="total_budget" class="form-control"
                                value="<?= $trip['total_budget'] ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="trip_view.php?id=<?= $trip['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>