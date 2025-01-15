<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ./login.php');
    exit();
}

$user = $_SESSION['user'];
$bookings_storage = new Storage(new JsonIO('../data/bookings.json'));
$cars_storage = new Storage(new JsonIO('../data/cars.json'));

$user_bookings = [];
if (isset($user['id'])) {
    $all_bookings = $bookings_storage->findAll();
    $user_bookings = array_filter($all_bookings, function($booking) use ($user) {
        return isset($booking['user_id']) && $booking['user_id'] === $user['id'];
    });

    usort($user_bookings, function($a, $b) {
        return strtotime($a['start_date']) - strtotime($b['start_date']);
    });

    foreach ($user_bookings as &$booking) {
        if (isset($booking['car_id'])) {
            $car = $cars_storage->findById($booking['car_id']);
            if ($car) {
                $booking['car'] = $car;
            }
        }
    }
    unset($booking);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Profile</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <div class="profile-header">
            <div class="profile-info">
                <img src="https://i.pravatar.cc/150?u=<?= urlencode($user['email']) ?>" 
                     alt="Profile" 
                     class="profile-image">
                <div class="profile-text">
                    <span class="profile-label">Logged in as</span>
                    <h1 class="profile-name"><?= htmlspecialchars($user['full_name']) ?></h1>
                </div>
            </div>
        </div>

        <section class="reservations">
            <h2>My reservations</h2>
            <?php if (empty($user_bookings)): ?>
                <div class="no-reservations">
                    <p>You don't have any reservations yet.</p>
                    <a href="../index.php" class="btn btn-primary">Browse Cars</a>
                </div>
            <?php else: ?>
                <div class="reservations-grid">
                    <?php foreach ($user_bookings as $booking): ?>
                        <?php if (isset($booking['car'])): ?>
                            <div class="reservation-card">
                                <div class="reservation-image">
                                    <img src="<?= htmlspecialchars($booking['car']['image']) ?>" 
                                         alt="<?= htmlspecialchars($booking['car']['brand'] . ' ' . $booking['car']['model']) ?>">
                                    <div class="reservation-dates">
                                        <?= htmlspecialchars(date('m.d', strtotime($booking['start_date']))) ?>-<?= htmlspecialchars(date('m.d', strtotime($booking['end_date']))) ?>
                                    </div>
                                </div>
                                <div class="reservation-info">
                                    <h3><?= htmlspecialchars($booking['car']['brand'] . ' ' . $booking['car']['model']) ?></h3>
                                    <p class="reservation-details">
                                        <span><?= htmlspecialchars($booking['car']['passengers']) ?> seats</span>
                                        <span class="separator">•</span>
                                        <span><?= strtolower($booking['car']['transmission']) ?></span>
                                    </p>
                                    <p class="reservation-price">
                                        Total: <?= number_format($booking['total_price']) ?> HUF
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>