<?php
/**
 * Delivery Agent Update Order Status
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'agent';
if (!isset($_SESSION['agent_id'])) {
    $_SESSION['agent_id'] = 1;
}

$agent_id = $_SESSION['agent_id'];

require_once __DIR__ . '/../includes/header.php';

// Initialize session orders store if not set
if (!isset($_SESSION['orders_sim'])) {
    $_SESSION['orders_sim'] = $orders;
}

// ─── Handle status updates ───────────────────────────────────────
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $action = $_GET['action'];
    
    $valid_actions = [
        'pickup'   => 'out_for_delivery',
        'deliver'  => 'delivered'
    ];
    
    if (isset($valid_actions[$action])) {
        $new_status = $valid_actions[$action];
        foreach ($_SESSION['orders_sim'] as &$o) {
            if ($o['id'] == $order_id && ($o['agent_id'] == $agent_id || (isset($_SESSION['assigned_agents'][$o['id']]) && $_SESSION['assigned_agents'][$o['id']] == $agent_id))) {
                $o['status'] = $new_status;
                if ($new_status === 'delivered') {
                    $o['delivered_at'] = date('Y-m-d H:i:s');
                    
                    // Release agent status back to available
                    if (isset($_SESSION['agents_crud'])) {
                        foreach ($_SESSION['agents_crud'] as &$a) {
                            if ($a['id'] == $agent_id) {
                                $a['status'] = 'available';
                                $a['deliveries_completed']++;
                                break;
                            }
                        }
                    }
                }
                set_flash('success', 'Order #' . $order_id . ' is now ' . str_replace('_', ' ', $new_status) . '.');
                break;
            }
        }
    }
    header('Location: update_status.php');
    exit;
}

// Load active orders assigned to agent
$all_orders = $_SESSION['orders_sim'];

// Inject active session assignments
if (isset($_SESSION['assigned_agents'])) {
    foreach ($_SESSION['assigned_agents'] as $ord_id => $ag_id) {
        if ($ag_id == $agent_id) {
            foreach ($all_orders as &$o) {
                if ($o['id'] == $ord_id) {
                    $o['agent_id'] = $agent_id;
                }
            }
        }
    }
}

// Filter down to agent active delivery orders
$my_active = array_filter($all_orders, function($o) use ($agent_id) {
    $is_assigned = $o['agent_id'] == $agent_id;
    $is_active = in_array($o['status'], ['confirmed', 'preparing', 'ready', 'out_for_delivery']);
    return $is_assigned && $is_active;
});

// Sort active newest first
usort($my_active, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Update Transit Status</h1>
        <p class="page-subtitle">Pick up from restaurants and mark orders as delivered</p>
    </div>
</div>

<!-- Active deliveries list table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Your Active Shipments</h3>
    </div>
    
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Restaurant Pickup</th>
                    <th>Delivery Customer</th>
                    <th>Shipping Address</th>
                    <th>Subtotal</th>
                    <th>Current Status</th>
                    <th style="width: 220px; text-align: center;">Transit Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($my_active)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No active deliveries pending transit updates.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($my_active as $order): 
                        $r = find_by_id($restaurants, $order['restaurant_id']);
                        $c = find_by_id($customers, $order['customer_id']);
                    ?>
                        <tr>
                            <td class="fw-600 text-accent">#<?= $order['id'] ?></td>
                            <td>
                                <div class="fw-600"><?= e($r ? $r['name'] : 'Unknown') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">📍 <?= e($r ? $r['address'] : '') ?></div>
                            </td>
                            <td>
                                <div class="fw-600"><?= e($c ? $c['name'] : 'Unknown') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">📞 <?= e($c ? $c['phone'] : '') ?></div>
                            </td>
                            <td><?= e($order['delivery_address']) ?></td>
                            <td class="fw-700"><?= format_price($order['total']) ?></td>
                            <td><?= status_badge($order['status']) ?></td>
                            <td>
                                <div class="action-cell" style="justify-content: center;">
                                    <?php if (in_array($order['status'], ['confirmed', 'preparing'])): ?>
                                        <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5; cursor: not-allowed;" title="Wait for kitchen to cook the food">
                                            Awaiting Kitchen
                                        </button>
                                    <?php elseif ($order['status'] === 'ready'): ?>
                                        <a href="update_status.php?action=pickup&order_id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                            📦 Mark Picked Up
                                        </a>
                                    <?php elseif ($order['status'] === 'out_for_delivery'): ?>
                                        <a href="update_status.php?action=deliver&order_id=<?= $order['id'] ?>" class="btn btn-success btn-sm">
                                            ✅ Mark Delivered
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.82rem;">Finished</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
