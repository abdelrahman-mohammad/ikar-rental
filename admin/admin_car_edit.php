<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$storage = new Storage(new JsonIO('../data/cars.json'));
$errors = [];

$car_id = $_GET['id'] ?? null;
if (!$car_id) {
    header('Location: ./admin.php');
    exit();
}

$car = $storage->findById($car_id);
if (!$car) {
    header('Location: ./admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated_car = [
        'id' => $car_id,
        'brand' => $_POST['brand'] ?? '',
        'model' => $_POST['model'] ?? '',
        'year' => intval($_POST['year'] ?? 0),
        'transmission' => $_POST['transmission'] ?? '',
        'fuel_type' => $_POST['fuel_type'] ?? '',
        'passengers' => intval($_POST['passengers'] ?? 0),
        'daily_price_huf' => intval($_POST['daily_price_huf'] ?? 0),
        'image' => $_POST['image'] ?? ''
    ];

    if (empty($updated_car['brand'])) $errors['brand'] = 'Brand is required';
    if (empty($updated_car['model'])) $errors['model'] = 'Model is required';
    if ($updated_car['year'] < 1900) $errors['year'] = 'Invalid year';
    if (empty($updated_car['transmission'])) $errors['transmission'] = 'Transmission is required';
    if (empty($updated_car['fuel_type'])) $errors['fuel_type'] = 'Fuel type is required';
    if ($updated_car['passengers'] < 1) $errors['passengers'] = 'Invalid number of passengers';
    if ($updated_car['daily_price_huf'] < 1) $errors['daily_price_huf'] = 'Invalid price';
    if (empty($updated_car['image'])) $errors['image'] = 'Image URL is required';

    if (empty($errors)) {
        $storage->update($car_id, $updated_car);
        header('Location: ./admin.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Edit Car</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <h1>Edit Car</h1>

        <form method="POST" action="" class="car-form">
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" 
                       value="<?= htmlspecialchars($car['brand']) ?>" required>
                <?php if (isset($errors['brand'])): ?>
                    <span class="error"><?= $errors['brand'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" 
                       value="<?= htmlspecialchars($car['model']) ?>" required>
                <?php if (isset($errors['model'])): ?>
                    <span class="error"><?= $errors['model'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" 
                       value="<?= $car['year'] ?>" required>
                <?php if (isset($errors['year'])): ?>
                    <span class="error"><?= $errors['year'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="transmission">Transmission</label>
                <select id="transmission" name="transmission" required>
                    <option value="Manual" <?= $car['transmission'] === 'Manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="Automatic" <?= $car['transmission'] === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                </select>
                <?php if (isset($errors['transmission'])): ?>
                    <span class="error"><?= $errors['transmission'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="fuel_type">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="Petrol" <?= $car['fuel_type'] === 'Petrol' ? 'selected' : '' ?>>Petrol</option>
                    <option value="Diesel" <?= $car['fuel_type'] === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    <option value="Hybrid" <?= $car['fuel_type'] === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                    <option value="Electric" <?= $car['fuel_type'] === 'Electric' ? 'selected' : '' ?>>Electric</option>
                </select>
                <?php if (isset($errors['fuel_type'])): ?>
                    <span class="error"><?= $errors['fuel_type'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="passengers">Number of Passengers</label>
                <input type="number" id="passengers" name="passengers" 
                       value="<?= $car['passengers'] ?>" required>
                <?php if (isset($errors['passengers'])): ?>
                    <span class="error"><?= $errors['passengers'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="daily_price_huf">Daily Price (HUF)</label>
                <input type="number" id="daily_price_huf" name="daily_price_huf" 
                       value="<?= $car['daily_price_huf'] ?>" required>
                <?php if (isset($errors['daily_price_huf'])): ?>
                    <span class="error"><?= $errors['daily_price_huf'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Image URL</label>
                <input type="url" id="image" name="image" 
                       value="<?= htmlspecialchars($car['image']) ?>" required>
                <?php if (isset($errors['image'])): ?>
                    <span class="error"><?= $errors['image'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Car</button>
                <a href="./admin.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>