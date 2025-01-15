<?php
@include_once('./Storage.php');
@include_once('./common/header.php');

$storage = new Storage(new JsonIO('./data/cars.json'));

function filterCars($cars, $filters) {
    $bookings_storage = new Storage(new JsonIO('./data/bookings.json'));
    
    return array_filter($cars, function($car) use ($filters, $bookings_storage) {
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $requested_start = strtotime($filters['start_date']);
            $requested_end = strtotime($filters['end_date']);
            
            $car_bookings = $bookings_storage->findAll(['car_id' => $car['id']]);
            
            foreach ($car_bookings as $booking) {
                $booking_start = strtotime($booking['start_date']);
                $booking_end = strtotime($booking['end_date']);
                
                if (
                    ($requested_start >= $booking_start && $requested_start <= $booking_end) ||
                    ($requested_end >= $booking_start && $requested_end <= $booking_end) ||      
                    ($requested_start <= $booking_start && $requested_end >= $booking_end)       
                ) {
                    return false;
                }
            }
        }

        if (isset($filters['transmission']) && $car['transmission'] !== $filters['transmission']) {
            return false;
        }
        
        if (isset($filters['passengers']) && $car['passengers'] < $filters['passengers']) {
            return false;
        }
        
        if (isset($filters['min_price']) && $car['daily_price_huf'] < $filters['min_price']) {
            return false;
        }
        
        if (isset($filters['max_price']) && $car['daily_price_huf'] > $filters['max_price']) {
            return false;
        }
        
        return true;
    });
}

$allCars = $storage->findAll();

$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['start_date'])) $filters['start_date'] = $_POST['start_date'];
    if (!empty($_POST['end_date'])) $filters['end_date'] = $_POST['end_date'];
    if (!empty($_POST['transmission'])) $filters['transmission'] = $_POST['transmission'];
    if (!empty($_POST['passengers'])) $filters['passengers'] = intval($_POST['passengers']);
    if (!empty($_POST['min_price'])) $filters['min_price'] = intval($_POST['min_price']);
    if (!empty($_POST['max_price'])) $filters['max_price'] = intval($_POST['max_price']);
    
    $filteredCars = filterCars($allCars, $filters);
} else {
    $filteredCars = $allCars;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Home</title>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>
    <main class="container">
        <h1>Rent cars easily!</h1>
        <form method="POST" action="" class="filter-section">
            <div class="filter-group">
                <button type="button" class="btn btn-secondary" onclick="decrementSeats()">-</button>
                <input type="number" id="passengers" name="passengers" value="<?= $filters['passengers'] ?? 0 ?>" class="filter-input" readonly>
                <button type="button" class="btn btn-secondary" onclick="incrementSeats()">+</button>
                <span>seats</span>
            </div>
            <div class="filter-group">
                <span>from</span>
                <input type="date" name="start_date" value="<?= $filters['start_date'] ?? '' ?>" class="filter-input">
            </div>
            <div class="filter-group">
                <span>until</span>
                <input type="date" name="end_date" value="<?= $filters['end_date'] ?? '' ?>" class="filter-input">
            </div>
            <div class="filter-group">
                <select name="transmission" class="filter-input">
                    <option value="">Gear type</option>
                    <option value="Automatic" <?= ($filters['transmission'] ?? '') === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                    <option value="Manual" <?= ($filters['transmission'] ?? '') === 'Manual' ? 'selected' : '' ?>>Manual</option>
                </select>
            </div>
            <div class="filter-group">
                <input type="number" name="min_price" placeholder="14,000" value="<?= $filters['min_price'] ?? '' ?>" class="filter-input">
                <span>-</span>
                <input type="number" name="max_price" placeholder="21,000" value="<?= $filters['max_price'] ?? '' ?>" class="filter-input">
                <span>Ft</span>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>

        <div class="car-grid">
            <?php foreach ($filteredCars as $car): ?>
                <div class="car-card">
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>" class="car-image">
                    <div class="car-info">
                        <div class="car-price"><?= number_format($car['daily_price_huf']) ?> Ft</div>
                        <div class="car-details">
                            <span><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></span>
                            <span><?= $car['passengers'] ?> seats - <?= strtolower($car['transmission']) ?></span>
                        </div>
                        <a href="./pages/car_details.php?id=<?= $car['id'] ?>" class="btn btn-primary">Book</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <script>
        function decrementSeats() {
            var input = document.getElementById('passengers');
            var value = parseInt(input.value, 10);
            if (value > 0) {
                input.value = value - 1;
            }
        }

        function incrementSeats() {
            var input = document.getElementById('passengers');
            var value = parseInt(input.value, 10);
            input.value = value + 1;
        }
    </script>
</body>
</html>