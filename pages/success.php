<?php
session_start();

if (!isset($_SESSION['user']) || !isset($_SESSION['booking'])) {
    header('Location: ../index.php');
    exit();
}

$booking = $_SESSION['booking'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Booking Successful</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/success.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <div class="success-message">
            <div class="success-icon">✓</div>
            <h1>Booking Successful!</h1>
            <p>
                You have successfully booked the 
                <strong><?= htmlspecialchars($booking['car_brand'] . ' ' . $booking['car_model']) ?></strong> 
                from <strong><?= htmlspecialchars(date('F j, Y', strtotime($booking['start_date']))) ?></strong> 
                to <strong><?= htmlspecialchars(date('F j, Y', strtotime($booking['end_date']))) ?></strong>.
            </p>
            <p>
                You can track your reservation status on your profile page.
            </p>
            <div class="button-group">
                <a href="./profile.php" class="btn btn-primary">View My Bookings</a>
                <a href="../index.php" class="btn btn-secondary">Back to Homepage</a>
            </div>
        </div>
    </main>

    <?php
        unset($_SESSION['booking']);
    ?>
</body>
</html>