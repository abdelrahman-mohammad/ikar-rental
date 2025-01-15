<?php
@include_once('../Storage.php');
session_start();

$cars_storage = new Storage(new JsonIO('../data/cars.json'));
$bookings_storage = new Storage(new JsonIO('../data/bookings.json'));

$car_id = $_GET['id'] ?? null;
if (!$car_id || !($car = $cars_storage->findById($car_id))) {
    header('Location: ../index.php');
    exit();
}

$is_logged_in = isset($_SESSION['user']);

function getUnavailableDates($car_id, $bookings_storage) {
    $car_bookings = $bookings_storage->findAll(['car_id' => $car_id]);
    $unavailable_dates = [];
    
    foreach ($car_bookings as $booking) {
        $start = strtotime($booking['start_date']);
        $end = strtotime($booking['end_date']);
        
        for ($date = $start; $date <= $end; $date = strtotime("+1 day", $date)) {
            $unavailable_dates[] = date('Y-m-d', $date);
        }
    }
    
    return $unavailable_dates;
}

function validateBooking($start_date, $end_date, $car_id, $bookings_storage) {
    $errors = [];
    
    if (empty($start_date)) {
        $errors[] = 'Start date is required';
    }
    if (empty($end_date)) {
        $errors[] = 'End date is required';
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        $start_timestamp = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        $current_timestamp = strtotime(date('Y-m-d'));
        
        if ($start_timestamp < $current_timestamp) {
            $errors[] = 'Start date cannot be in the past';
        }
        if ($end_timestamp < $start_timestamp) {
            $errors[] = 'End date must be after start date';
        }
        
        if (empty($errors)) {
            $existing_bookings = $bookings_storage->findAll(['car_id' => $car_id]);
            
            foreach ($existing_bookings as $booking) {
                $booking_start = strtotime($booking['start_date']);
                $booking_end = strtotime($booking['end_date']);
                
                if (
                    ($start_timestamp >= $booking_start && $start_timestamp <= $booking_end) ||
                    ($end_timestamp >= $booking_start && $end_timestamp <= $booking_end) ||
                    ($start_timestamp <= $booking_start && $end_timestamp >= $booking_end)
                ) {
                    $errors[] = 'Selected dates are not available';
                    break;
                }
            }
        }
    }
    
    return $errors;
}

function createBooking($car_id, $car, $user_id, $start_date, $end_date, $bookings_storage) {
    $start_timestamp = strtotime($start_date);
    $end_timestamp = strtotime($end_date);
    $days = ceil(($end_timestamp - $start_timestamp) / (60 * 60 * 24)) + 1;
    $total_price = $days * $car['daily_price_huf'];
    
    $booking = [
        'car_id' => $car_id,
        'user_id' => $user_id,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'total_price' => $total_price,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $bookings_storage->add($booking);
    
    return [
        'car_brand' => $car['brand'],
        'car_model' => $car['model'],
        'start_date' => $start_date,
        'end_date' => $end_date,
        'total_price' => $total_price
    ];
}

$booking_errors = [];
$unavailable_dates = getUnavailableDates($car_id, $bookings_storage);
$unavailable_dates_json = json_encode($unavailable_dates);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['car_id'])) {
    if (!$is_logged_in) {
        $booking_errors[] = 'Please log in to book a car';
    } else {
        $booking_errors = validateBooking(
            $_POST['start_date'] ?? '',
            $_POST['end_date'] ?? '',
            $car_id,
            $bookings_storage
        );
        
        if (empty($booking_errors)) {
            $booking_info = createBooking(
                $car_id,
                $car,
                $_SESSION['user']['id'],
                $_POST['start_date'],
                $_POST['end_date'],
                $bookings_storage
            );
            
            $_SESSION['booking'] = $booking_info;
            header('Location: success.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/car_details.css">
</head>
<body>
    <?php @include_once("../common/header.php"); ?>

    <main class="container car-details">
        <div class="car-details-grid">
            <div class="car-image-container">
                <img src="<?= htmlspecialchars($car['image']) ?>" 
                     alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" 
                     class="car-detail-image">
            </div>
            <div class="car-info-container">
                <h1 class="car-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h1>
                
                <div class="car-specs">
                    <div class="spec-item">
                        <span class="spec-label">Fuel:</span>
                        <span class="spec-value"><?= htmlspecialchars($car['fuel_type']) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Shifter:</span>
                        <span class="spec-value"><?= htmlspecialchars($car['transmission']) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Year of manufacture:</span>
                        <span class="spec-value"><?= htmlspecialchars($car['year']) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Number of seats:</span>
                        <span class="spec-value"><?= htmlspecialchars($car['passengers']) ?></span>
                    </div>
                </div>

                <div class="booking-section">
                    <div class="price">
                        <span class="currency">HUF</span>
                        <span class="amount"><?= number_format($car['daily_price_huf']) ?></span>
                        <span class="period">/day</span>
                    </div>
                    
                    <?php if ($is_logged_in): ?>
                        <button class="btn btn-primary" onclick="openBookingModal()">Book Now</button>
                    <?php else: ?>
                        <div class="login-prompt">
                            <p>Please log in to book this car.</p>
                            <a href="./login.php" class="btn btn-primary">Login to Book</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="bookingModal" class="modal">
                    <div class="modal-content">
                        <span class="close-button" onclick="closeBookingModal()">&times;</span>
                        <h2>Book <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                        
                        <form method="POST" id="bookingForm">
                            <input type="hidden" name="car_id" value="<?= $car_id ?>">
                            
                            <div class="date-inputs">
                                <div class="date-field">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" required>
                                </div>
                                <div class="date-field">
                                    <label for="end_date">End Date</label>
                                    <input type="date" name="end_date" id="end_date" required>
                                </div>
                            </div>

                            <div class="booking-preview">
                                <div class="preview-row">
                                    <span>Total days:</span>
                                    <span id="totalDays">0</span>
                                </div>
                                <div class="preview-row">
                                    <span>Daily rate:</span>
                                    <span><?= number_format($car['daily_price_huf']) ?> HUF</span>
                                </div>
                                <div class="preview-row total">
                                    <span>Total price:</span>
                                    <span id="totalPrice">0 HUF</span>
                                </div>
                            </div>

                            <?php if (!empty($booking_errors)): ?>
                                <div class="booking-errors">
                                    <?php foreach ($booking_errors as $error): ?>
                                        <div class="error-message">
                                            <span class="error-icon">⚠</span>
                                            <?= htmlspecialchars($error) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="modal-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeBookingModal()">Cancel</button>
                                <button type="submit" class="btn btn-primary">Confirm Booking</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
    const modal = document.getElementById('bookingModal');
    const dailyPrice = <?= $car['daily_price_huf'] ?>;
    const unavailableDates = <?= $unavailable_dates_json ?>;

    function openBookingModal() {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').min = today;
        document.getElementById('end_date').min = today;
    }

    function closeBookingModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    window.onclick = function(event) {
        if (event.target === modal) {
            closeBookingModal();
        }
    }

    document.getElementById('start_date').addEventListener('change', updateTotalPrice);
    document.getElementById('end_date').addEventListener('change', updateTotalPrice);

    function updateTotalPrice() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate && endDate && endDate >= startDate) {
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            const total = days * dailyPrice;
            
            document.getElementById('totalDays').textContent = days;
            document.getElementById('totalPrice').textContent = 
                total.toLocaleString('hu-HU') + ' HUF';
        }
    }

    function showDatePicker() {
        const form = document.getElementById('bookingForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function submitBooking() {
        const form = document.getElementById('bookingForm');
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        if (startDate && endDate) {
            form.submit();
        } else {
            alert('Please select both start and end dates');
        }
    }
    </script>
</body>
</html>