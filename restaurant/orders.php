<?php
/**
 * Restaurant Orders Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'restaurant';
if (!isset($_SESSION['restaurant_id'])) {
    $_SESSION['restaurant_id'] = 1;
}

$restaurant_id = $_SESSION['restaurant_id'];

require_once __DIR__ . '/../includes/header.php';

// Initialize session orders store for simulation if not set
if (!isset($_SESSION['restaurant_orders_sim'])) {
    $_SESSION['restaurant_orders_sim'] = get_restaurant_orders($orders, $restaurant_id);
}

// Handle Status Updates (Accept, Prepare, Ready)
if (isset($_GET['action']) && isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $action = $_GET['action'];
    
    $valid_actions = [
        'accept'  => 'confirmed',
        'prepare' => 'preparing',
        'ready'   => 'ready'
    ];
    
    if (isset($valid_actions[$action])) {
        $new_status = $valid_actions[$action];
        foreach ($_SESSION['restaurant_orders_sim'] as &$o) {
            if ($o['id'] == $order_id) {
                $o['status'] = $new_status;
                set_flash('success', 'Order #' . $order_id . ' status updated to ' . ucfirst($new_status));
                break;
            }
        }
    }
    header('Location: orders.php');
    exit;
}

$current_orders = $_SESSION['restaurant_orders_sim'];

// Apply Search and Status Filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

if ($search !== '') {
    $current_orders = array_filter($current_orders, function($o) use ($search, $customers) {
        $c = find_by_id($customers, $o['customer_id']);
        $id_match = strpos((string)$o['id'], $search) !== false;
        $name_match = $c && strpos(strtolower($c['name']), strtolower($search)) !== false;
        return $id_match || $name_match;
    });
}

if ($status_filter !== '') {
    $current_orders = filter_by($current_orders, 'status', $status_filter);
}

// Order sorting (newest first)
usort($current_orders, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($current_orders, $page, 6);
$paginated_orders = $pagination['data'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Incoming & Active Orders</h1>
        <p class="page-subtitle">Accept, prepare, and update status of food delivery requests</p>
    </div>
</div>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search order by ID or customer..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    
    <select class="form-select filter-select" onchange="applyFilter('status', this.value)">
        <option value="">All Orders</option>
        <option value="placed" <?= $status_filter === 'placed' ? 'selected' : '' ?>>Placed (Incoming)</option>
        <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
        <option value="preparing" <?= $status_filter === 'preparing' ? 'selected' : '' ?>>Preparing</option>
        <option value="ready" <?= $status_filter === 'ready' ? 'selected' : '' ?>>Ready for Pickup</option>
        <option value="out_for_delivery" <?= $status_filter === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
        <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
    
    <?php if ($search !== '' || $status_filter !== ''): ?>
        <a href="orders.php" class="btn btn-secondary btn-sm">Clear Filters</a>
    <?php endif; ?>
</div>

<!-- Table list -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Info</th>
                <th>Items Details</th>
                <th>Subtotal</th>
                <th>Date Placed</th>
                <th>Status</th>
                <th style="width: 280px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_orders)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No matching orders found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginated_orders as $order): 
                    $c = find_by_id($customers, $order['customer_id']);
                ?>
                    <tr>
                        <td class="fw-600 text-accent">#<?= $order['id'] ?></td>
                        <td>
                            <div class="fw-600"><?= e($c ? $c['name'] : 'Unknown') ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= e($c ? $c['phone'] : '') ?></div>
                        </td>
                        <td>
                            <ul style="list-style: none; padding: 0;">
                                <?php foreach ($order['items'] as $item): ?>
                                    <li style="font-size: 0.85rem;"><?= e($item['name']) ?> <strong>x<?= $item['quantity'] ?></strong></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td class="fw-700"><?= format_price($order['subtotal']) ?></td>
                        <td class="text-muted"><?= format_datetime($order['created_at']) ?></td>
                        <td><?= status_badge($order['status']) ?></td>
                        <td>
                            <div class="action-cell" style="justify-content: center;">
                                <?php if ($order['status'] === 'placed'): ?>
                                    <a href="orders.php?action=accept&order_id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">
                                        Accept Order
                                    </a>
                                <?php elseif ($order['status'] === 'confirmed'): ?>
                                    <a href="orders.php?action=prepare&order_id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">
                                        Start Cooking
                                    </a>
                                <?php elseif ($order['status'] === 'preparing'): ?>
                                    <a href="orders.php?action=ready&order_id=<?= $order['id'] ?>" class="btn btn-success btn-sm">
                                        Mark Ready
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.8rem;">No Actions Pending</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?= pagination_html($pagination, '?search=' . urlencode($search) . '&status=' . urlencode($status_filter) . '&') ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
