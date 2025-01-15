<?php
@include_once('../Storage.php');
session_start();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    if (empty($errors)) {
        $storage = new Storage(new JsonIO('../data/users.json'));
        $user = $storage->findOne(['email' => $email]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header('Location: ../index.php');
            exit();
        } else {
            $errors['login'] = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iKarRental - Login</title>
    <link rel="stylesheet" href="../css/common.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <?php @include_once('../common/header.php'); ?>

    <main class="container">
        <form method="POST" action="" class="auth-form" novalidate>
            <h1>Login</h1>

            <?php if (isset($errors['login'])): ?>
                <div class="error-banner">
                    <?= $errors['login'] ?>
                </div>
            <?php endif; ?>
            
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

            <button type="submit" class="btn-submit">Login</button>
            <div class="auth-links">
                <p>Don't have an account? <a href="./register.php">Sign up</a></p>
            </div>
        </form>
    </main>
</body>
</html>

