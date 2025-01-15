<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['user']);
?>

<header class="header">
    <a href="../index.php" class="logo">
        <svg class="logo-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 17H21V15H19V17ZM3 17H5V15H3V17ZM21 13V7C21 5.9 20.1 5 19 5H5C3.9 5 3 5.9 3 7V13C3 14.1 3.9 15 5 15H19C20.1 15 21 14.1 21 13ZM19 13H5V7H19V13ZM12 18C13.1 18 14 17.1 14 16H10C10 17.1 10.9 18 12 18Z" fill="#ffb800"/>
        </svg>
        iKarRental
    </a>
    <div class="nav-buttons">
        <?php if ($is_logged_in): ?>
            <div class="profile-menu">
                <div class="profile-info">
                    <img src="https://i.pravatar.cc/150?u=<?= urlencode($_SESSION['user']['email']) ?>" 
                         alt="Profile" 
                         class="profile-picture">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user']['full_name']) ?></span>
                </div>
                <div class="profile-dropdown">
                    <?php if ($_SESSION['user']['is_admin']): ?>
                        <a href="../admin/admin.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <a href="../pages/profile.php">My Profile</a>
                    <a href="../logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="../pages/login.php" class="btn btn-secondary">Login</a>
            <a href="../pages/register.php" class="btn btn-primary">Registration</a>
        <?php endif; ?>
    </div>
</header>