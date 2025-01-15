<?php
@include_once('../Storage.php');
session_start();

if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header('Location: ../index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car = [
        'brand' => $_POST['brand'] ?? '',
        'model' => $_POST['model'] ?? '',
        'year' => intval($_POST['year'] ?? 0),
        'transmission' => $_POST['transmission'] ?? '',
        'fuel_type' => $_POST['fuel_type'] ?? '',
        'passengers' => intval($_POST['passengers'] ?? 0),
        'daily_price_huf' => intval($_POST['daily_price_huf'] ?? 0),
        'image' => $_POST['image'] ?? ''
    ];

    if (empty($car['brand'])) $errors['brand'] = 'Brand is required';
    if (empty($car['model'])) $errors['model'] = 'Model is required';
    if ($car['year'] < 1900) $errors['year'] = 'Invalid year';
    if (empty($car['transmission'])) $errors['transmission'] = 'Transmission is required';
    if (empty($car['fuel_type'])) $errors['fuel_type'] = 'Fuel type is required';
    if ($car['passengers'] < 1) $errors['passengers'] = 'Invalid number of passengers';
    if ($car['daily_price_huf'] < 1) $errors['daily_price_huf'] = 'Invalid price';
    if (empty($car['image'])) $errors['image'] = 'Image URL is required';

    if (empty($errors)) {
        $storage = new Storage(new JsonIO('../data/cars.json'));
        $allCars = $storage->findAll();
        $nextId = count($allCars) + 1;
        $car['id'] = strval($nextId);
        $storage->add($car);
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
    <title>iKarRental - Add New Car</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <h1>Add New Car</h1>

        <form method="POST" action="" class="car-form">
            <div class="form-group">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?= $_POST['brand'] ?? '' ?>" required>
                <?php if (isset($errors['brand'])): ?>
                    <span class="error"><?= $errors['brand'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="model">Model</label>
                <input type="text" id="model" name="model" value="<?= $_POST['model'] ?? '' ?>" required>
                <?php if (isset($errors['model'])): ?>
                    <span class="error"><?= $errors['model'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="year">Year</label>
                <input type="number" id="year" name="year" value="<?= $_POST['year'] ?? '' ?>" required>
                <?php if (isset($errors['year'])): ?>
                    <span class="error"><?= $errors['year'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="transmission">Transmission</label>
                <select id="transmission" name="transmission" required>
                    <option value="">Select transmission</option>
                    <option value="Manual">Manual</option>
                    <option value="Automatic">Automatic</option>
                </select>
                <?php if (isset($errors['transmission'])): ?>
                    <span class="error"><?= $errors['transmission'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="fuel_type">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" required>
                    <option value="">Select fuel type</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Electric">Electric</option>
                </select>
                <?php if (isset($errors['fuel_type'])): ?>
                    <span class="error"><?= $errors['fuel_type'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="passengers">Number of Passengers</label>
                <input type="number" id="passengers" name="passengers" value="<?= $_POST['passengers'] ?? '' ?>" required>
                <?php if (isset($errors['passengers'])): ?>
                    <span class="error"><?= $errors['passengers'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="daily_price_huf">Daily Price (HUF)</label>
                <input type="number" id="daily_price_huf" name="daily_price_huf" 
                       value="<?= $_POST['daily_price_huf'] ?? '' ?>" required>
                <?php if (isset($errors['daily_price_huf'])): ?>
                    <span class="error"><?= $errors['daily_price_huf'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Image URL</label>
                <input type="url" id="image" name="image" value="<?= $_POST['image'] ?? '' ?>" required>
                <?php if (isset($errors['image'])): ?>
                    <span class="error"><?= $errors['image'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Car</button>
                <a href="admin.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
</body>
</html>