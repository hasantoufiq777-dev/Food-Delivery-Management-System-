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

// Handle status updates
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $action = $_GET['action'];
    
    $valid_actions = [
        'pickup'   => 'out_for_delivery',
        'deliver'  => 'delivered'
    ];
    
    if (isset($valid_actions[$action])) {
        $new_status = $valid_actions[$action];
        try {
            // Get user_id for this agent
            $a_stmt = $conn->prepare("SELECT user_id FROM delivery_agents WHERE agent_id = :aid");
            $a_stmt->execute(['aid' => $agent_id]);
            $user_id = $a_stmt->fetchColumn();
            
            // Call stored procedure
            $stmt = $conn->prepare("CALL update_order_status(:oid, :status, :changed_by)");
            $stmt->execute([
                'oid' => $order_id,
                'status' => $new_status,
                'changed_by' => $user_id
            ]);
            
            unset($_SESSION['orders_sim']); // Purge global simulation orders cache
            set_flash('success', 'Order #' . $order_id . ' is now ' . str_replace('_', ' ', $new_status) . '.');
        } catch (PDOException $ex) {
            set_flash('error', 'Database Error: ' . $ex->getMessage());
        }
    }
    header('Location: update_status.php');
    exit;
}

// Load active orders assigned to agent from database
$all_my_orders = get_db_orders(['agent_id' => $agent_id]);
$my_active = array_filter($all_my_orders, function($o) {
    return in_array($o['status'], ['confirmed', 'preparing', 'ready', 'out_for_delivery']);
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
