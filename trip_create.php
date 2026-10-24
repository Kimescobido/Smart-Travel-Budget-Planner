<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();

$page_title = "Create New Trip";
$error = '';

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
        $stmt = $pdo->prepare("INSERT INTO trips (user_id, title, destination_city, start_date, end_date, base_currency, total_budget) VALUES (:user_id, :title, :city, :start, :end, :currency, :budget)");
        $saved = $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'city' => $destination_city,
            'start' => $start_date,
            'end' => $end_date,
            'currency' => $base_currency,
            'budget' => $total_budget
        ]);

        if ($saved) {
            $new_trip_id = $pdo->lastInsertId();
            header("Location: trip_view.php?id=" . $new_trip_id);
            exit;
        } else {
            $error = "Failed to save trip. Please try again.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="card-title mb-3"><i class="bi bi-plus-circle text-primary"></i> Plan a New Trip</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="trip_create.php">
                    <div class="mb-3">
                        <label class="form-label">Trip Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Summer Vacation in Tokyo" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Destination City *</label>
                        <input type="text" name="destination_city" class="form-control" placeholder="e.g. Tokyo, Paris, Cebu" required>
                        <div class="form-text">We will fetch live weather, photos, and map pins for this city!</div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Base Currency</label>
                            <select name="base_currency" class="form-select">
                                <option value="USD">USD ($)</option>
                                <option value="PHP" selected>PHP (₱)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Budget</label>
                            <input type="number" step="0.01" name="total_budget" class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="trips.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Create Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>