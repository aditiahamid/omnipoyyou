<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Omnichannel App' ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="header">
    <nav class="navbar">
        <a href="<?= BASE_URL ?>" class="logo">
            <i class="fas fa-comments"></i> OmniChannel
        </a>

        <ul class="nav-menu">
            <li><a href="<?= BASE_URL ?>">Dashboard</a></li>
            <li><a href="<?= BASE_URL ?>messages/list.php">Messages</a></li>
            <li><a href="<?= BASE_URL ?>orders/list.php">Orders</a></li>
            <li><a href="<?= BASE_URL ?>customers/list.php">Customers</a></li>

            <li class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-circle"></i>
                    <?= $_SESSION['full_name'] ?? 'User' ?>
                </span>

                <a href="<?= BASE_URL ?>auth/logout.php" class="btn btn-sm btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
</header>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_URL ?>" class="<?= $current_page === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>messages/list.php" class="<?= str_contains($current_page, 'messages') ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Messages
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>customers/list.php" class="<?= str_contains($current_page, 'customers') ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> Customers
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>products/list.php" class="<?= str_contains($current_page, 'products') ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>orders/list.php" class="<?= str_contains($current_page, 'orders') ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>channels/list.php" class="<?= str_contains($current_page, 'channels') ? 'active' : '' ?>">
                    <i class="fas fa-plug"></i> Channels
                </a>
            </li>

            <li>
                <a href="<?= BASE_URL ?>logs/api_logs.php">
                    <i class="fas fa-history"></i> API Logs
                </a>
            </li>
        </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <?php
        $flash = getFlash();
        if ($flash):
        ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $flash['message'] ?>
        </div>
        <?php endif; ?>
