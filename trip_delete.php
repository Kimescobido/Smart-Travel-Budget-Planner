<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
requireLogin();

$trip_id = $_GET['id'] ?? null;
$from_admin = isset($_GET['from_admin']);

if ($trip_id) {
    if ($_SESSION['user_role'] === 'admin') {
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = :id");
        $stmt->execute(['id' => $trip_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM trips WHERE id = :id AND user_id = :uid");
        $stmt->execute(['id' => $trip_id, 'uid' => $_SESSION['user_id']]);
    }
}

if ($from_admin && ($_SESSION['user_role'] ?? '') === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: trips.php");
}
exit;
