<?php
/**
 * Admin Orders Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/header.php';

// Check if viewing a specific order detail
$view_order_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$detail_order = null;

if ($view_order_id) {
    $detail_order = get_db_order($view_order_id);
}

if ($detail_order):
    $rest = find_by_id($restaurants, $detail_order['restaurant_id']);
    $agent_id = null;
    if (isset($_SESSION['assigned_agents'][$detail_order['id']])) {
        $agent_id = $_SESSION['assigned_agents'][$detail_order['id']];
    } else {
        $agent_id = $detail_order['agent_id'];
    }
    $agent_obj = $agent_id ? find_by_id($agents, $agent_id) : null;
?>
    <!-- ─── Detailed Order Tracking View (Live Update Progress for Admin) ─── -->
    <div class="page-header">
        <div>
            <a href="orders.php" class="btn btn-secondary btn-sm mb-1">← Orders List</a>
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
    <?php
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

    // Paginate (8 items per page)
    $pagination = paginate($filtered_orders, $page, 8);
    $paginated_orders = $pagination['data'];
    ?>

    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Orders</h1>
            <p class="page-subtitle">View and filter all system orders</p>
        </div>
        <div>
            <a href="?clear_orders=true" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure you want to clear all simulated orders?');">Clear Simulated Orders</a>
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
                            <td>
                                <a href="orders.php?id=<?= $order['id'] ?>" style="text-decoration: none;">
                                    <?= status_badge($order['status']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="action-cell">
                                    <button class="btn btn-secondary btn-sm btn-icon" title="View Details" 
                                            onclick="window.location.href='orders.php?id=<?= $order['id'] ?>'">
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

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
