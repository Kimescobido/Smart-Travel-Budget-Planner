<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/api/weather.php';
require_once __DIR__ . '/api/currency.php';
require_once __DIR__ . '/api/photos.php';
requireLogin();

$trip_id = $_GET['id'] ?? null;
if (!$trip_id) {
    header("Location: trips.php");
    exit;
}

// Fetch trip details
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

// 1. External API #1: OpenWeatherMap
$weather = getDestinationWeather($trip['destination_city']);

// 2. External API #4: Unsplash Photo
$photo = getDestinationPhoto($trip['destination_city']);

// 3. Handle adding an activity/expense with API #2 (Currency Conversion)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_activity'])) {
    $day_number = intval($_POST['day_number'] ?? 1);
    $activity_title = trim($_POST['activity_title'] ?? '');
    $cost_local = floatval($_POST['cost_local'] ?? 0);
    $local_currency = $_POST['local_currency'] ?? $trip['base_currency'];
    $notes = trim($_POST['notes'] ?? '');

    if (!empty($activity_title)) {
        $cost_base = convertCurrency($cost_local, $local_currency, $trip['base_currency']);

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

// Handle deleting item
if (isset($_GET['delete_item'])) {
    $item_id = intval($_GET['delete_item']);
    $del = $pdo->prepare("DELETE FROM itinerary_items WHERE id = :id AND trip_id = :tid");
    $del->execute(['id' => $item_id, 'tid' => $trip['id']]);
    header("Location: trip_view.php?id=" . $trip['id']);
    exit;
}

// Fetch all itinerary items
$itemsStmt = $pdo->prepare("SELECT * FROM itinerary_items WHERE trip_id = :tid ORDER BY day_number ASC, id ASC");
$itemsStmt->execute(['tid' => $trip['id']]);
$items = $itemsStmt->fetchAll();

// Calculate total spent
$totalSpent = 0;
foreach ($items as $item) {
    $totalSpent += floatval($item['cost_base']);
}
$remainingBudget = floatval($trip['total_budget']) - $totalSpent;

$page_title = $trip['title'];
require_once __DIR__ . '/includes/header.php';
?>

<!-- API #4: Unsplash Cover Image Banner -->
<div class="position-relative text-white rounded-3 overflow-hidden shadow-sm mb-4"
    style="height: 240px; background: url('<?= htmlspecialchars($photo['url']) ?>') center/cover no-repeat;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.55);"></div>
    <div class="position-relative h-100 d-flex flex-column justify-content-between p-4">
        <div class="d-flex justify-content-between align-items-start">
            <span class="badge bg-primary fs-6"><i class="bi bi-geo-alt"></i>
                <?= htmlspecialchars($trip['destination_city']) ?>
            </span>
            <div class="d-flex">
                <!-- API #5: Email Itinerary Button -->
                <form method="POST" action="api/send_itinerary.php" class="d-inline">
                    <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-primary me-1">
                        <i class="bi bi-envelope-fill"></i> Email Itinerary (API #5)
                    </button>
                </form>
                <a href="trip_edit.php?id=<?= $trip['id'] ?>" class="btn btn-sm btn-light me-1"><i
                        class="bi bi-pencil"></i> Edit</a>
                <a href="trips.php" class="btn btn-sm btn-outline-light"><i class="bi bi-arrow-left"></i> Back</a>
            </div>
        </div>
        <div>
            <h1 class="display-6 fw-bold mb-1 text-white">
                <?= htmlspecialchars($trip['title']) ?>
            </h1>
            <p class="mb-0 text-white-50 small">
                <i class="bi bi-calendar-event"></i>
                <?= date('M d, Y', strtotime($trip['start_date'])) ?> &rarr;
                <?= date('M d, Y', strtotime($trip['end_date'])) ?>
                • Photo via <a href="<?= htmlspecialchars($photo['photographer_url']) ?>" target="_blank"
                    class="text-white-50 text-decoration-underline">
                    <?= htmlspecialchars($photo['photographer']) ?>
                </a> (Unsplash API)
            </p>
        </div>
    </div>
</div>

<!-- API #5 Email Status Alert -->
<?php if (isset($_GET['email_status'])): ?>
    <?php if ($_GET['email_status'] === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <strong>Itinerary Emailed!</strong> A summary has been dispatched via Resend
            API (API #5) to your inbox.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['email_status'] === 'error'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Email Notice:</strong> Resend sandbox allows sending emails
            directly to the account email you signed up with.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($_GET['email_status'] === 'no_key'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle-fill"></i> Resend API key is missing in <code>.env</code>.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- API Widgets Row -->
<div class="row g-3 mb-4">
    <!-- API #1: Live Weather Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="text-muted mb-0"><i class="bi bi-cloud-sun text-warning"></i> Destination Weather (API #1)
                </h6>
                <span class="badge bg-info-subtle text-info border">Live</span>
            </div>
            <?php if ($weather): ?>
                <div class="d-flex align-items-center mt-2">
                    <img src="https://openweathermap.org/img/wn/<?= $weather['icon'] ?>@2x.png" alt="Weather" width="60"
                        height="60">
                    <div>
                        <h4 class="mb-0">
                            <?= $weather['temp'] ?>°C
                        </h4>
                        <div class="text-muted small">
                            <?= $weather['condition'] ?> • Humidity:
                            <?= $weather['humidity'] ?>%
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-muted small mt-2">
                    <i class="bi bi-exclamation-triangle"></i> Weather data unavailable for this city.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- API #2: Multi-Currency Budget Card -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3 bg-white h-100">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h6 class="text-muted mb-0"><i class="bi bi-currency-exchange text-success"></i> Budget Tracker (API #2)
                </h6>
                <span class="badge bg-success-subtle text-success border">Auto-Converted</span>
            </div>
            <div class="row text-center mt-2">
                <div class="col-4 border-end">
                    <small class="text-muted">Budget</small>
                    <div class="fw-bold">
                        <?= htmlspecialchars($trip['base_currency']) ?>
                        <?= number_format($trip['total_budget'], 2) ?>
                    </div>
                </div>
                <div class="col-4 border-end">
                    <small class="text-muted">Spent</small>
                    <div class="fw-bold text-danger">
                        <?= htmlspecialchars($trip['base_currency']) ?>
                        <?= number_format($totalSpent, 2) ?>
                    </div>
                </div>
                <div class="col-4">
                    <small class="text-muted">Remaining</small>
                    <div class="fw-bold <?= $remainingBudget >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= htmlspecialchars($trip['base_currency']) ?>
                        <?= number_format($remainingBudget, 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- API #3: Interactive Map Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-map text-primary"></i> Interactive Destination Map (API #3 - OpenStreetMap &
            Nominatim)</h6>
        <span class="badge bg-secondary-subtle text-secondary border">Leaflet.js</span>
    </div>
    <div class="card-body p-0">
        <div id="trip-map" style="height: 280px; width: 100%; border-radius: 0 0 0.375rem 0.375rem;"></div>
    </div>
</div>

<div class="row">
    <!-- Itinerary Items Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Itinerary & Expenses</h5>
            </div>
            <div class="card-body">
                <?php if (empty($items)): ?>
                    <p class="text-muted text-center py-3">No activities or expenses added yet. Use the form to add one!</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Day</th>
                                    <th>Activity</th>
                                    <th>Logged Cost</th>
                                    <th>Converted (
                                        <?= htmlspecialchars($trip['base_currency']) ?>)
                                    </th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><span class="badge bg-primary">Day
                                                <?= htmlspecialchars($item['day_number']) ?>
                                            </span></td>
                                        <td><strong>
                                                <?= htmlspecialchars($item['activity_title']) ?>
                                            </strong></td>
                                        <td>
                                            <?= htmlspecialchars($item['local_currency']) ?>
                                            <?= number_format($item['cost_local'], 2) ?>
                                        </td>
                                        <td><strong class="text-primary">
                                                <?= htmlspecialchars($trip['base_currency']) ?>
                                                <?= number_format($item['cost_base'], 2) ?>
                                            </strong></td>
                                        <td class="text-muted small">
                                            <?= htmlspecialchars($item['notes'] ?? '-') ?>
                                        </td>
                                        <td>
                                            <a href="trip_view.php?id=<?= $trip['id'] ?>&delete_item=<?= $item['id'] ?>"
                                                class="text-danger small" onclick="return confirm('Delete this item?');"><i
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
                        <input type="text" name="activity_title" class="form-control" placeholder="e.g. Ramen Lunch"
                            required>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label class="form-label">Cost</label>
                            <input type="number" step="0.01" name="cost_local" class="form-control" value="0.00"
                                required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Currency</label>
                            <select name="local_currency" class="form-select">
                                <option value="JPY">JPY (¥)</option>
                                <option value="USD">USD ($)</option>
                                <option value="PHP">PHP (₱)</option>
                                <option value="EUR">EUR (€)</option>
                                <option value="GBP">GBP (£)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"
                            placeholder="e.g. Near train station"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Add with Live
                        Rate</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- API #3 Script: Geocode city using Nominatim & render Leaflet Map -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const cityName = <?= json_encode($trip['destination_city']) ?>;
        const mapElement = document.getElementById('trip-map');

        if (!mapElement) return;

        let lat = 35.6762;
        let lon = 139.6503;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(cityName)}&limit=1`)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    lat = parseFloat(data[0].lat);
                    lon = parseFloat(data[0].lon);
                }
                initMap(lat, lon, cityName);
            })
            .catch(() => {
                initMap(lat, lon, cityName);
            });

        function initMap(latitude, longitude, label) {
            const map = L.map('trip-map').setView([latitude, longitude], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.marker([latitude, longitude])
                .addTo(map)
                .bindPopup(`<b>${label}</b><br>Your travel destination`)
                .openPopup();
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>