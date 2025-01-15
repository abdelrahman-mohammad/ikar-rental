<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    $cars_storage = new Storage(new JsonIO('../data/cars.json'));
    $bookings_storage = new Storage(new JsonIO('../data/bookings.json'));
    $bookings_storage->deleteMany(function($booking) use ($car_id) {
        return $booking['car_id'] === $car_id;
    });

    $cars_storage->delete($car_id);
}

header('Location: ./admin.php');
exit();