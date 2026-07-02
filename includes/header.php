<?php
/**
 * Header Include
 * Food Delivery Management System
 */
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Check
if (!isset($_SESSION['role'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

require_once __DIR__ . '/../config/dummy_data.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

// Initialize global database connection
try {
    $conn = get_db_connection_pdo();
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Load global arrays from Oracle Database
$restaurants = get_db_restaurants();
$menu_items = get_db_menu_items();
$agents = get_db_agents();
$customers = get_db_customers();

// Clear orders simulation if requested by admin
if (isset($_GET['clear_orders']) && $_GET['clear_orders'] === 'true' && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $_SESSION['orders_sim'] = [];
    $_SESSION['restaurant_orders_sim'] = [];
    
    $current_page = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $current_page);
    exit;
}

// Sync global $orders with session simulation or DB
if (!isset($_SESSION['orders_sim'])) {
    $_SESSION['orders_sim'] = get_db_orders();
}
$orders = &$_SESSION['orders_sim'];

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
if ($current_role === 'customer' && isset($_SESSION['customer_id'])) {
    $cart = get_db_cart($_SESSION['customer_id']);
    if ($cart && !empty($cart['items'])) {
        foreach ($cart['items'] as $ci) {
            $cart_count += (int)$ci['quantity'];
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
