<?php
/**
 * Admin Orders Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/header.php';

// Retrieve values from GET for search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Load orders
$filtered_orders = $orders;

// Perform Filter by Status
if ($status_filter !== '') {
    $filtered_orders = filter_by($filtered_orders, 'status', $status_filter);
}

// Perform Search by Order ID, Customer Name, or Restaurant Name
if ($search !== '') {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($search, $customers, $restaurants) {
        $cust = find_by_id($customers, $o['customer_id']);
        $rest = find_by_id($restaurants, $o['restaurant_id']);
        
        $order_id_match = (strpos((string)$o['id'], $search) !== false);
        $cust_match = $cust && (strpos(strtolower($cust['name']), strtolower($search)) !== false);
        $rest_match = $rest && (strpos(strtolower($rest['name']), strtolower($search)) !== false);
        
        return $order_id_match || $cust_match || $rest_match;
    });
}

// Order sorting (newest first)
usort($filtered_orders, function($a, $b) {
    return strcmp($b['created_at'], $a['created_at']);
});

// Paginate (10 items per page)
$pagination = paginate($filtered_orders, $page, 8);
$paginated_orders = $pagination['data'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Manage Orders</h1>
        <p class="page-subtitle">View and filter all system orders</p>
    </div>
</div>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search by Order ID, customer, or restaurant..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    
    <select class="form-select filter-select" onchange="applyFilter('status', this.value)">
        <option value="">All Statuses</option>
        <?php foreach ($order_status_labels as $key => $label): ?>
            <option value="<?= $key ?>" <?= $status_filter === $key ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
    </select>
    
    <?php if ($search !== '' || $status_filter !== ''): ?>
        <a href="orders.php" class="btn btn-secondary btn-sm">Clear Filters</a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Restaurant</th>
                <th>Agent</th>
                <th>Items</th>
                <th>Total Price</th>
                <th>Order Date</th>
                <th>Status</th>
                <th style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_orders)): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">No orders found matching the filter criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginated_orders as $order): 
                    $cust = find_by_id($customers, $order['customer_id']);
                    $rest = find_by_id($restaurants, $order['restaurant_id']);
                    $agent_obj = $order['agent_id'] ? find_by_id($agents, $order['agent_id']) : null;
                ?>
                    <tr>
                        <td class="fw-600 text-accent">#<?= $order['id'] ?></td>
                        <td>
                            <div class="fw-600"><?= e($cust ? $cust['name'] : 'Unknown') ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= e($cust ? $cust['phone'] : '') ?></div>
                        </td>
                        <td><?= e($rest ? $rest['name'] : 'Unknown') ?></td>
                        <td>
                            <?php if ($agent_obj): ?>
                                <span class="fw-600"><?= e($agent_obj['name']) ?></span>
                            <?php else: ?>
                                <span class="text-warning font-size-sm">Not Assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="text-secondary"><?= order_items_count($order) ?> items</span>
                        </td>
                        <td class="fw-700"><?= format_price($order['total']) ?></td>
                        <td class="text-muted"><?= format_datetime($order['created_at']) ?></td>
                        <td><?= status_badge($order['status']) ?></td>
                        <td>
                            <div class="action-cell">
                                <button class="btn btn-secondary btn-sm btn-icon" title="View Details" 
                                        onclick="window.location.href='<?= BASE_URL ?>customer/orders.php?id=<?= $order['id'] ?>'">
                                    <?= icon('eye', 16) ?>
                                </button>
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
