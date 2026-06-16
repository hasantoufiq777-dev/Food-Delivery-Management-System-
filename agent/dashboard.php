<?php
/**
 * Delivery Agent Dashboard
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'agent';
if (!isset($_SESSION['agent_id'])) {
    $_SESSION['agent_id'] = 1; // Default to Marcus Reed
}

$agent_id = $_SESSION['agent_id'];

require_once __DIR__ . '/../includes/header.php';

// Find current agent
$agent_obj = find_by_id($agents, $agent_id);

// Load orders assigned to this agent from simulated orders if set, otherwise fallback
$all_orders = $_SESSION['orders_sim'] ?? $orders;

// Check for newly assigned agent from the admin session simulation
if (isset($_SESSION['assigned_agents'])) {
    foreach ($_SESSION['assigned_agents'] as $ord_id => $ag_id) {
        if ($ag_id == $agent_id) {
            // Update local copy of order list to reflect assignment simulation
            foreach ($all_orders as &$o) {
                if ($o['id'] == $ord_id) {
                    $o['agent_id'] = $agent_id;
                    // Auto advance status to confirmed if it was placed
                    if ($o['status'] === 'placed') {
                        $o['status'] = 'confirmed';
                    }
                }
            }
        }
    }
}

$my_deliveries = get_agent_orders($all_orders, $agent_id);

// Separate active vs completed
$active_deliveries = array_filter($my_deliveries, function($d) {
    return in_array($d['status'], ['confirmed', 'preparing', 'ready', 'out_for_delivery']);
});

$completed_deliveries = array_filter($my_deliveries, function($d) {
    return $d['status'] === 'delivered';
});

// Sort active newest first, completed newest first
usort($active_deliveries, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
usort($completed_deliveries, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Courier Dashboard</h1>
        <p class="page-subtitle">Welcome back, <strong><?= e($agent_obj ? $agent_obj['name'] : 'Agent') ?></strong> (<?= e($agent_obj ? $agent_obj['vehicle'] : '') ?>)</p>
    </div>
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <span class="text-muted" style="font-size: 0.82rem;">Switch Agent:</span>
        <select class="form-select filter-select" style="min-width: 200px;" onchange="applyFilter('agent_id_switch', this.value)">
            <?php foreach ($agents as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $agent_id == $a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
</div>

<?php
// Handle agent switcher query
if (isset($_GET['agent_id_switch'])) {
    $_SESSION['agent_id'] = (int)$_GET['agent_id_switch'];
    set_flash('success', 'Switched agent view.');
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
?>

<!-- Stats cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            🚚
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Trips</div>
            <div class="stat-value"><?= count($active_deliveries) ?></div>
            <div class="stat-change">📦 Assigned to you</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            🎉
        </div>
        <div class="stat-info">
            <div class="stat-label">Delivered Today</div>
            <div class="stat-value"><?= count($completed_deliveries) ?></div>
            <div class="stat-change">🟢 Successful deliveries</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            ⭐
        </div>
        <div class="stat-info">
            <div class="stat-label">Your Rating</div>
            <div class="stat-value"><?= number_format($agent_obj ? $agent_obj['rating'] : 5.0, 1) ?></div>
            <div class="stat-change">★ Average score</div>
        </div>
    </div>
</div>

<!-- Active Deliveries list -->
<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">🛵 Active Delivery Jobs</h3>
        <a href="update_status.php" class="btn btn-primary btn-sm">Update Transit Status</a>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Restaurant Address</th>
                    <th>Customer Name</th>
                    <th>Customer Address</th>
                    <th>Subtotal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($active_deliveries)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No active deliveries assigned. Grab some rest!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($active_deliveries as $deliv): 
                        $r = find_by_id($restaurants, $deliv['restaurant_id']);
                        $c = find_by_id($customers, $deliv['customer_id']);
                    ?>
                        <tr>
                            <td class="fw-600 text-accent">#<?= $deliv['id'] ?></td>
                            <td class="fw-600"><?= e($r ? $r['name'] : 'Unknown') ?></td>
                            <td><?= e($r ? $r['address'] : '') ?></td>
                            <td class="fw-600"><?= e($c ? $c['name'] : 'Unknown') ?></td>
                            <td><?= e($deliv['delivery_address']) ?></td>
                            <td class="fw-700"><?= format_price($deliv['total']) ?></td>
                            <td><?= status_badge($deliv['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- History of completed deliveries -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Delivery History</h3>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Earnings (Total)</th>
                    <th>Completed Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($completed_deliveries)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No completed deliveries on record.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($completed_deliveries as $deliv): 
                        $r = find_by_id($restaurants, $deliv['restaurant_id']);
                        $c = find_by_id($customers, $deliv['customer_id']);
                    ?>
                        <tr>
                            <td class="fw-600 text-accent">#<?= $deliv['id'] ?></td>
                            <td><?= e($r ? $r['name'] : 'Unknown') ?></td>
                            <td><?= e($c ? $c['name'] : 'Unknown') ?></td>
                            <td><?= e($deliv['delivery_address']) ?></td>
                            <td class="fw-700"><?= format_price($deliv['total']) ?></td>
                            <td class="text-muted"><?= $deliv['delivered_at'] ? format_datetime($deliv['delivered_at']) : 'N/A' ?></td>
                            <td><?= status_badge($deliv['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
