<?php
@include_once('../Storage.php');
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['full_name'])) {
        $errors['full_name'] = 'Full name is required';
    }

    if (empty($_POST['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($_POST['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($_POST['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if ($_POST['password'] !== $_POST['password_confirm']) {
        $errors['password_confirm'] = 'Passwords do not match';
    }

    if (empty($errors)) {
        $storage = new Storage(new JsonIO('../data/users.json'));
        $existing_user = $storage->findOne(['email' => $_POST['email']]);
        if ($existing_user) {
            $errors['email'] = 'Email already registered';
        } else {
            $is_admin = ($_POST['email'] === 'admin@ikarrental.hu');
            $user = [
                'full_name' => $_POST['full_name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'is_admin' => $is_admin
            ];
            
            $user_id = $storage->add($user);
            $user['id'] = $user_id;
            $_SESSION['user'] = $user;
            
            header('Location: ../index.php');
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
    <title>iKarRental - Registration</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <form method="POST" action="" class="auth-form" novalidate>
            <h1>Registration</h1>
            
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input 
                    type="text" 
                    id="full_name" 
                    name="full_name" 
                    value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                    class="<?= isset($errors['full_name']) ? 'error' : '' ?>"
                >
                <?php if (isset($errors['full_name'])): ?>
                    <span class="error-message"><?= $errors['full_name'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    class="<?= isset($errors['email']) ? 'error' : '' ?>"
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="error-message"><?= $errors['email'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="<?= isset($errors['password']) ? 'error' : '' ?>"
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="error-message"><?= $errors['password'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password</label>
                <input 
                    type="password" 
                    id="password_confirm" 
                    name="password_confirm"
                    class="<?= isset($errors['password_confirm']) ? 'error' : '' ?>"
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="error-message"><?= $errors['password_confirm'] ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">Register</button>
        </form>
    </main>
</body>
</html>

