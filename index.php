<?php
$page_title = "Welcome";
require_once __DIR__ . '/includes/header.php';
?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm border">
    <div class="container-fluid py-3">
        <h1 class="display-6 fw-bold text-dark">Plan Your Next Adventure Smarter ✈️</h1>
        <p class="col-md-8 fs-6 text-muted">
            Create custom travel itineraries, track multi-currency budgets in real-time, preview live destination
            weather forecasts, and email complete schedules to yourself with a single click.
        </p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-primary btn-lg">Get Started Free</a>
            <a href="login.php" class="btn btn-outline-secondary btn-lg ms-2">Login</a>
        <?php else: ?>
            <a href="trip_create.php" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle"></i> Create a New Trip</a>
            <a href="trips.php" class="btn btn-outline-primary btn-lg ms-2"><i class="bi bi-map"></i> View My Trips</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 py-2">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <i class="bi bi-cloud-sun text-warning fs-1 mb-2"></i>
                <h5 class="card-title">Live Weather Forecasts</h5>
                <p class="card-text text-muted small">Stay prepared with live destination conditions and temperatures
                    powered by OpenWeatherMap API.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <i class="bi bi-currency-exchange text-success fs-1 mb-2"></i>
                <h5 class="card-title">Multi-Currency Budgeting</h5>
                <p class="card-text text-muted small">Log expenses in foreign currencies and convert back to your home
                    currency with real-time exchange rates.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-3">
            <div class="card-body">
                <i class="bi bi-envelope-check text-primary fs-1 mb-2"></i>
                <h5 class="card-title">Email Itinerary Export</h5>
                <p class="card-text text-muted small">One-click itinerary dispatch sends clean, formatted schedules
                    directly to your email inbox.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>