<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$cars_storage = new Storage(new JsonIO('../data/cars.json'));
$bookings_storage = new Storage(new JsonIO('../data/bookings.json'));
$users_storage = new Storage(new JsonIO('../data/users.json'));

$cars = $cars_storage->findAll();
$bookings = $bookings_storage->findAll();

foreach ($bookings as &$booking) {
    $user = $users_storage->findById($booking['user_id']);
    $car = $cars_storage->findById($booking['car_id']);
    $booking['user'] = $user;
    $booking['car'] = $car;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <a href="./admin_car_add.php" class="btn btn-primary">Add New Car</a>
        </div>

        <div class="admin-tabs">
            <button class="tab-button active" onclick="showTab('cars')">Cars</button>
            <button class="tab-button" onclick="showTab('bookings')">Bookings</button>
        </div>

        <div id="cars" class="tab-content active">
            <div class="cars-grid">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <img src="<?= htmlspecialchars($car['image']) ?>" 
                             alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" 
                             class="car-image">
                        <div class="car-info">
                            <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                            <p class="car-price"><?= number_format($car['daily_price_huf']) ?> HUF/day</p>
                            <div class="car-actions">
                                <a href="./admin_car_edit.php?id=<?= $car['id'] ?>" class="btn btn-secondary">Edit</a>
                                <form action="./admin_car_delete.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this car?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="bookings" class="tab-content">
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Car</th>
                        <th>User</th>
                        <th>Dates</th>
                        <th>Total Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= $booking['id'] ?></td>
                            <td><?= htmlspecialchars($booking['car']['brand'] . ' ' . $booking['car']['model']) ?></td>
                            <td><?= htmlspecialchars($booking['user']['full_name']) ?></td>
                            <td><?= htmlspecialchars($booking['start_date']) ?> - <?= htmlspecialchars($booking['end_date']) ?></td>
                            <td><?= number_format($booking['total_price']) ?> HUF</td>
                            <td>
                                <form action="./admin_booking_delete.php" method="POST">
                                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this booking?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
        
            document.getElementById(tabName).classList.add('active');
            document.querySelector(`button[onclick="showTab('${tabName}')"]`).classList.add('active');
        }
    </script>
</body>
</html>