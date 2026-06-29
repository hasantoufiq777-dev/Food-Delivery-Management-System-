<?php
/**
 * Customer Orders & Tracking Detail
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'customer';
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['customer_id'] = 1;
}

$customer_id = $_SESSION['customer_id'];

require_once __DIR__ . '/../includes/header.php';

// Load customer orders from database
$my_orders = get_db_orders(['customer_id' => $customer_id]);

// Check if viewing a specific order detail
$view_order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$detail_order = null;

if ($view_order_id) {
    $detail_order = get_db_order($view_order_id);
    if ($detail_order && (int)$detail_order['customer_id'] !== $customer_id) {
        $detail_order = null;
    }
}

// Order sorting (newest first)
usort($my_orders, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($my_orders, $page, 5);
$paginated_orders = $pagination['data'];
?>

<?php if ($detail_order): 
    $rest = find_by_id($restaurants, $detail_order['restaurant_id']);
    // Check if agent assigned in simulated agents or dummy
    $agent_id = null;
    if (isset($_SESSION['assigned_agents'][$detail_order['id']])) {
        $agent_id = $_SESSION['assigned_agents'][$detail_order['id']];
    } else {
        $agent_id = $detail_order['agent_id'];
    }
    
    $agent_obj = $agent_id ? find_by_id($agents, $agent_id) : null;
?>
    <!-- ─── Detailed Order Tracking View ──────────────────────── -->
    <div class="page-header">
        <div>
            <a href="orders.php" class="btn btn-secondary btn-sm mb-1">← My Orders List</a>
            <h1 class="page-title">Track Order #<?= $detail_order['id'] ?></h1>
            <p class="page-subtitle">From: <strong><?= e($rest ? $rest['name'] : 'Restaurant') ?></strong></p>
        </div>
        <div>
            <?= status_badge($detail_order['status']) ?>
        </div>
    </div>

    <!-- Status Tracking Timeline -->
    <div class="card mb-3">
        <h3 class="section-title">📦 Delivery Progress</h3>
        <?= order_timeline($detail_order['status']) ?>
    </div>

    <div class="two-col">
        <!-- Order items details -->
        <div class="card">
            <h3 class="section-title">🍽️ Items Ordered</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Dish Name</th>
                        <th style="width: 80px; text-align: center;">Qty</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detail_order['items'] as $item): ?>
                        <tr>
                            <td><?= e($item['name']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-right"><?= format_price($item['price']) ?></td>
                            <td class="text-right fw-600"><?= format_price($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 1.5rem; border-top: 1px solid var(--border); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                <div style="font-size: 0.9rem;">Subtotal: <strong><?= format_price($detail_order['subtotal']) ?></strong></div>
                <div style="font-size: 0.9rem;">Delivery: <strong><?= format_price($detail_order['delivery_fee']) ?></strong></div>
                <div style="font-size: 1.1rem; color: var(--accent);">Total Paid: <strong><?= format_price($detail_order['total']) ?></strong></div>
            </div>
        </div>

        <!-- Courier / Agent details card -->
        <div class="card">
            <h3 class="section-title">🛵 Delivery Agent</h3>
            <?php if ($agent_obj): ?>
                <div class="agent-card-inline">
                    <div class="agent-card-avatar">
                        <?= strtoupper(substr($agent_obj['name'], 0, 1)) ?>
                    </div>
                    <div class="agent-card-info">
                        <h4><?= e($agent_obj['name']) ?></h4>
                        <span>Active Courier (<?= e($agent_obj['vehicle']) ?>)</span>
                        <div class="text-accent mt-1" style="font-size: 0.8rem;">
                            📞 <?= e($agent_obj['phone']) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state" style="padding: 1.5rem 0;">
                    <div class="empty-state-icon" style="font-size: 2rem;">⏳</div>
                    <div class="empty-state-title" style="font-size: 0.95rem;">Finding courier agent...</div>
                    <div class="empty-state-desc" style="font-size: 0.78rem;">We are assigning a delivery agent to pick up your order shortly.</div>
                </div>
            <?php endif; ?>
            
            <h3 class="section-title mt-3">📍 Shipping Details</h3>
            <div class="profile-info-item">
                <div class="profile-info-label">Address</div>
                <div class="profile-info-value" style="font-size: 0.88rem; font-weight: 500;"><?= e($detail_order['delivery_address']) ?></div>
            </div>
            <div class="profile-info-item mt-1">
                <div class="profile-info-label">Payment</div>
                <div class="profile-info-value" style="font-size: 0.88rem; font-weight: 500;"><?= e($detail_order['payment_method']) ?></div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ─── Order list view ───────────────────────────────────── -->
    <div class="page-header">
        <div>
            <h1 class="page-title">My Orders</h1>
            <p class="page-subtitle">Track and view history of all your orders</p>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Restaurant</th>
                    <th>Items details</th>
                    <th>Total</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th style="width: 130px; text-align: center;">Track</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($paginated_orders)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">You haven't placed any orders yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paginated_orders as $ord): 
                        $r = find_by_id($restaurants, $ord['restaurant_id']);
                        $items_sum = [];
                        foreach ($ord['items'] as $it) {
                            $items_sum[] = $it['name'] . ' x' . $it['quantity'];
                        }
                    ?>
                        <tr>
                            <td class="fw-600 text-accent">#<?= $ord['id'] ?></td>
                            <td class="fw-600"><?= e($r ? $r['name'] : 'Unknown') ?></td>
                            <td><?= e(implode(', ', $items_sum)) ?></td>
                            <td class="fw-700"><?= format_price($ord['total']) ?></td>
                            <td class="text-muted"><?= format_datetime($ord['created_at']) ?></td>
                            <td><?= status_badge($ord['status']) ?></td>
                            <td style="text-align: center;">
                                <a href="orders.php?id=<?= $ord['id'] ?>" class="btn btn-secondary btn-sm">
                                    Track Live ⚡
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?= pagination_html($pagination, '?') ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
