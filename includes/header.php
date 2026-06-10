<?php
/**
 * Header Include
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Check
if (!isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

require_once __DIR__ . '/../config/dummy_data.php';
require_once __DIR__ . '/functions.php';

$current_role = $_SESSION['role'];
$current_user_id = get_current_user_id();

// Authorization Check (Role-based folder restriction)
$request_uri = $_SERVER['REQUEST_URI'];
$is_admin = ($current_role === 'admin');
$in_admin_path = (strpos($request_uri, '/admin/') !== false);
$in_restaurant_path = (strpos($request_uri, '/restaurant/') !== false);
$in_customer_path = (strpos($request_uri, '/customer/') !== false);
$in_agent_path = (strpos($request_uri, '/agent/') !== false);

$is_authorized = false;
if ($is_admin) {
    $is_authorized = true; // Admin can view any path/role section
} elseif ($current_role === 'customer' && $in_customer_path) {
    $is_authorized = true;
} elseif ($current_role === 'restaurant' && $in_restaurant_path) {
    $is_authorized = true;
} elseif ($current_role === 'agent' && $in_agent_path) {
    $is_authorized = true;
}

if (!$is_authorized) {
    // Traverse guard: kick back to their own page
    if ($current_role === 'customer') {
        header('Location: ' . BASE_URL . 'customer/browse.php');
    } else {
        header('Location: ' . BASE_URL . $current_role . '/dashboard.php');
    }
    exit;
}

// Navigation items per role
$nav_items = [
    'admin' => [
        ['url' => BASE_URL . 'admin/dashboard.php', 'label' => 'Dashboard',   'icon' => 'dashboard'],
        ['url' => BASE_URL . 'admin/orders.php',    'label' => 'Orders',      'icon' => 'orders'],
        ['url' => BASE_URL . 'admin/restaurants.php','label' => 'Restaurants', 'icon' => 'restaurant'],
        ['url' => BASE_URL . 'admin/customers.php', 'label' => 'Customers',   'icon' => 'customers'],
        ['url' => BASE_URL . 'admin/agents.php',    'label' => 'Agents',      'icon' => 'agents'],
    ],
    'restaurant' => [
        ['url' => BASE_URL . 'restaurant/dashboard.php', 'label' => 'Dashboard', 'icon' => 'dashboard'],
        ['url' => BASE_URL . 'restaurant/menu.php',      'label' => 'Menu',      'icon' => 'menu'],
        ['url' => BASE_URL . 'restaurant/orders.php',    'label' => 'Orders',    'icon' => 'orders'],
    ],
    'customer' => [
        ['url' => BASE_URL . 'customer/browse.php',   'label' => 'Browse',    'icon' => 'browse'],
        ['url' => BASE_URL . 'customer/cart.php',      'label' => 'Cart',      'icon' => 'cart'],
        ['url' => BASE_URL . 'customer/orders.php',    'label' => 'My Orders', 'icon' => 'orders'],
        ['url' => BASE_URL . 'customer/profile.php',   'label' => 'Profile',   'icon' => 'profile'],
    ],
    'agent' => [
        ['url' => BASE_URL . 'agent/dashboard.php',       'label' => 'Dashboard',     'icon' => 'dashboard'],
        ['url' => BASE_URL . 'agent/update_status.php',   'label' => 'Update Status', 'icon' => 'update'],
    ],
];

$role_labels = [
    'admin'      => 'Administrator',
    'restaurant' => 'Restaurant Manager',
    'customer'   => 'Customer',
    'agent'      => 'Delivery Agent',
];

$role_colors = [
    'admin'      => '#FF6B2B',
    'restaurant' => '#4ECDC4',
    'customer'   => '#45B7D1',
    'agent'      => '#96CEB4',
];

// Get user display name
$current_user_name = 'Admin';
if ($current_role === 'customer') {
    $user = find_by_id($customers, $current_user_id);
    $current_user_name = $user ? $user['name'] : 'Customer';
} elseif ($current_role === 'restaurant') {
    $rest = find_by_id($restaurants, $current_user_id);
    $current_user_name = $rest ? $rest['name'] : 'Restaurant';
} elseif ($current_role === 'agent') {
    $ag = find_by_id($agents, $current_user_id);
    $current_user_name = $ag ? $ag['name'] : 'Agent';
}

// Cart count for customer
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['items'])) {
    foreach ($_SESSION['cart']['items'] as $ci) {
        $cart_count += $ci['quantity'];
    }
}

// Current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FlameRoute - Premium Food Delivery Management System">
    <title>FlameRoute — Food Delivery Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
        <?= icon('hamburger', 24) ?>
    </button>

    <!-- Sidebar Overlay (mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?= BASE_URL ?>" class="logo">
                <span class="logo-icon">🔥</span>
                <span class="logo-text">Flame<span class="logo-accent">Route</span></span>
            </a>
        </div>

        <!-- Role Switcher -->
        <?php if ($current_role === 'admin' || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'])): ?>
        <div class="role-switcher">
            <label class="role-switcher-label">Demo Role Switcher</label>
            <form method="POST" action="<?= BASE_URL ?>index.php" id="roleSwitchForm">
                <select name="role" class="role-select" onchange="this.form.submit()">
                    <option value="admin" <?= $current_role === 'admin' ? 'selected' : '' ?>>🛡️ Admin</option>
                    <option value="restaurant" <?= $current_role === 'restaurant' ? 'selected' : '' ?>>🍽️ Restaurant</option>
                    <option value="customer" <?= $current_role === 'customer' ? 'selected' : '' ?>>👤 Customer</option>
                    <option value="agent" <?= $current_role === 'agent' ? 'selected' : '' ?>>🚗 Agent</option>
                </select>
                <input type="hidden" name="switch_role" value="1">
            </form>
        </div>
        <?php endif; ?>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <?php foreach ($nav_items[$current_role] as $item): ?>
                    <?php 
                    $is_active = (basename($item['url']) === $current_page) ? 'active' : '';
                    ?>
                    <li class="nav-item">
                        <a href="<?= $item['url'] ?>" class="nav-link <?= $is_active ?>">
                            <span class="nav-icon"><?= icon($item['icon']) ?></span>
                            <span class="nav-label"><?= $item['label'] ?></span>
                            <?php if ($item['icon'] === 'cart' && $cart_count > 0): ?>
                                <span class="cart-badge"><?= $cart_count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-item" style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <a href="<?= BASE_URL ?>index.php?action=logout" class="nav-link" style="color: var(--danger);">
                        <span class="nav-icon"><?= icon('logout') ?></span>
                        <span class="nav-label">Sign Out</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar" style="background-color: <?= $role_colors[$current_role] ?>">
                    <?= strtoupper(substr($current_user_name, 0, 1)) ?>
                </div>
                <div class="user-details">
                    <span class="user-name"><?= e($current_user_name) ?></span>
                    <span class="user-role"><?= $role_labels[$current_role] ?></span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?= flash_html() ?>
