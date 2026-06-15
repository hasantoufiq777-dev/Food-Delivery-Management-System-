<?php
/**
 * Restaurant Manager Dashboard
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'restaurant';
// Default to Restaurant ID 1 (The Spice Garden) if none is set
if (!isset($_SESSION['restaurant_id'])) {
    $_SESSION['restaurant_id'] = 1;
}

$restaurant_id = $_SESSION['restaurant_id'];

require_once __DIR__ . '/../includes/header.php';

// Find current restaurant
$restaurant = find_by_id($restaurants, $restaurant_id);

// Load restaurant specific orders
$restaurant_orders = get_restaurant_orders($orders, $restaurant_id);

// If user performed quick updates via session store simulation
if (!isset($_SESSION['restaurant_orders_sim'])) {
    $_SESSION['restaurant_orders_sim'] = $restaurant_orders;
}
$current_orders = $_SESSION['restaurant_orders_sim'];

// Today's metrics (Simulation on order list)
$incoming_orders_count = count(array_filter($current_orders, function($o) {
    return in_array($o['status'], ['placed', 'confirmed']);
}));

$preparing_orders_count = count(array_filter($current_orders, function($o) {
    return $o['status'] === 'preparing';
}));

$ready_orders_count = count(array_filter($current_orders, function($o) {
    return $o['status'] === 'ready';
}));

$completed_orders_count = count(array_filter($current_orders, function($o) {
    return $o['status'] === 'delivered';
}));

$today_revenue = array_sum(array_map(function($o) {
    return ($o['status'] !== 'cancelled') ? $o['subtotal'] : 0;
}, $current_orders));

// Load active menu count
$active_menu_items = count(array_filter($menu_items, function($m) use ($restaurant_id) {
    return $m['restaurant_id'] == $restaurant_id && $m['available'];
}));
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Manager Dashboard</h1>
        <p class="page-subtitle">Welcome back, manager of <strong><?= e($restaurant ? $restaurant['name'] : 'Restaurant') ?></strong></p>
    </div>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="text-muted" style="font-size: 0.82rem;">Switch Restaurant:</span>
        <select class="form-select filter-select" style="min-width: 200px;" onchange="applyFilter('restaurant_id_switch', this.value)">
            <?php foreach ($restaurants as $r): ?>
                <option value="<?= $r['id'] ?>" <?= $restaurant_id == $r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<?php
// Handle restaurant switcher query
if (isset($_GET['restaurant_id_switch'])) {
    $_SESSION['restaurant_id'] = (int)$_GET['restaurant_id_switch'];
    unset($_SESSION['restaurant_orders_sim']); // Reset simulation context
    unset($_SESSION['menu_items_crud']);
    set_flash('success', 'Switched restaurant view.');
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <?= icon('orders', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Incoming Orders</div>
            <div class="stat-value"><?= $incoming_orders_count ?></div>
            <div class="stat-change">🔔 Needs confirmation</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            👨‍🍳
        </div>
        <div class="stat-info">
            <div class="stat-label">In Kitchen</div>
            <div class="stat-value"><?= $preparing_orders_count ?></div>
            <div class="stat-change">🔥 Being prepared now</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            📦
        </div>
        <div class="stat-info">
            <div class="stat-label">Ready for Pickup</div>
            <div class="stat-value"><?= $ready_orders_count ?></div>
            <div class="stat-change">🛵 Awaiting courier</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <?= icon('revenue', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-value"><?= format_price($today_revenue) ?></div>
            <div class="stat-change up">▲ Subtotal earnings</div>
        </div>
    </div>
</div>

<div class="two-col">
    <!-- Active Kitchen Orders -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">👨‍🍳 Active Kitchen Orders</h3>
            <a href="orders.php" class="btn btn-secondary btn-sm">Manage Orders</a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Date Placed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $active_kitchen = array_filter($current_orders, function($o) {
                        return in_array($o['status'], ['placed', 'confirmed', 'preparing', 'ready']);
                    });
                    
                    if (empty($active_kitchen)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Kitchen is clear! No active orders.</td>
                        </tr>
                    <?php else: 
                        foreach (array_slice($active_kitchen, 0, 5) as $order):
                            $items_summary = [];
                            foreach ($order['items'] as $it) {
                                $items_summary[] = $it['name'] . ' x' . $it['quantity'];
                            }
                        ?>
                            <tr>
                                <td class="fw-600 text-accent">#<?= $order['id'] ?></td>
                                <td><?= e(implode(', ', $items_summary)) ?></td>
                                <td><?= status_badge($order['status']) ?></td>
                                <td class="text-muted"><?= format_datetime($order['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Info & Stats -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🍽️ Menu Summary</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="profile-info-item">
                <div class="profile-info-label">Active Dishes</div>
                <div class="profile-info-value" style="font-size: 1.5rem; color: var(--accent);"><?= $active_menu_items ?></div>
            </div>
            
            <div class="profile-info-item">
                <div class="profile-info-label">Total Completed Today</div>
                <div class="profile-info-value" style="font-size: 1.5rem; color: var(--success);"><?= $completed_orders_count ?></div>
            </div>
            
            <a href="menu.php" class="btn btn-primary mt-1">
                <?= icon('menu', 16) ?> Edit Menu Card
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
