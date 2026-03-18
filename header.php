<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FURLINK - Pet Adoption</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="nav-container">
        <a href="index.php" class="logo">FURLINK</a>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'seller'): ?>
                    <a href="dashboard_seller.php">My Dashboard</a>
                <?php elseif ($_SESSION['role'] === 'adopter'): ?>
                    <a href="dashboard_adopter.php">My Requests</a>
                <?php elseif ($_SESSION['role'] === 'admin'): ?>
                    <a href="dashboard_admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-secondary">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
