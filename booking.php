<?php
@include_once('./Storage.php');
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ./pages/login.php');
    exit();
}

$bookings_storage = new Storage(new JsonIO('./data/bookings.json'));
$cars_storage = new Storage(new JsonIO('./data/cars.json'));

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = $_POST['car_id'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    

    if (empty($car_id)) {
        $errors[] = 'Car ID is required';
    }
    if (empty($start_date)) {
        $errors[] = 'Start date is required';
    }
    if (empty($end_date)) {
        $errors[] = 'End date is required';
    }


    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    $current_timestamp = strtotime(date('Y-m-d'));

    if ($start_timestamp < $current_timestamp) {
        $errors[] = 'Start date cannot be in the past';
    }
    if ($end_timestamp <= $start_timestamp) {
        $errors[] = 'End date must be after start date';
    }


    $car = $cars_storage->findById($car_id);
    if (!$car) {
        $errors[] = 'Invalid car selected';
    }


    if (empty($errors)) {
        $existing_bookings = $bookings_storage->findAll([
            'car_id' => $car_id
        ]);

        foreach ($existing_bookings as $booking) {
            $booking_start = strtotime($booking['start_date']);
            $booking_end = strtotime($booking['end_date']);

            if (
                ($start_timestamp >= $booking_start && $start_timestamp <= $booking_end) ||
                ($end_timestamp >= $booking_start && $end_timestamp <= $booking_end) ||
                ($start_timestamp <= $booking_start && $end_timestamp >= $booking_end)
            ) {
                $errors[] = 'Car is not available for selected dates';
                break;
            }
        }
    }


    if (empty($errors)) {
        $booking = [
            'car_id' => $car_id,
            'user_id' => $_SESSION['user']['id'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'created_at' => date('Y-m-d H:i:s'),
            'total_price' => calculateTotalPrice($car['daily_price_huf'], $start_timestamp, $end_timestamp)
        ];

        $bookings_storage->add($booking);

    
        $_SESSION['booking'] = [
            'car_brand' => $car['brand'],
            'car_model' => $car['model'],
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

    
        header('Location: ./pages/success.php');
        exit();
    }
}

function calculateTotalPrice($daily_price, $start_timestamp, $end_timestamp) {
    $days = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24));
    return $daily_price * $days;
}

if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header('Location: ./pages/car_details.php?id=' . $car_id);
    exit();
}
?>