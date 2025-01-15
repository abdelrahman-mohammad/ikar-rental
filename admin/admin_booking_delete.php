<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $bookings_storage = new Storage(new JsonIO('../data/bookings.json'));
    $bookings_storage->delete($booking_id);
}

header('Location: ./admin.php');
exit();