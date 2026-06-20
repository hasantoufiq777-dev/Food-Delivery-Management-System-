<?php
/**
 * Admin Customers List & Order History
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'admin';

require_once __DIR__ . '/../includes/header.php';

// Search Filter
$search = $_GET['search'] ?? '';
$customer_list = $customers;

if ($search !== '') {
    $customer_list = search_array($customer_list, $search, ['name', 'email', 'phone', 'address']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($customer_list, $page, 6);
$paginated_customers = $pagination['data'];

// Customer History Detail
$detail_customer = null;
$customer_orders = [];
if (isset($_GET['view_history']) && $_GET['view_history'] !== '') {
    $view_id = (int)$_GET['view_history'];
    $detail_customer = find_by_id($customers, $view_id);
    if ($detail_customer) {
        $customer_orders = get_customer_orders($orders, $view_id);
        // Sort newest first
        usort($customer_orders, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Customers Directory</h1>
        <p class="page-subtitle">View active users and their order history</p>
    </div>
</div>

<!-- History Detail Modal/Section (if view_history is requested) -->
<?php if ($detail_customer): ?>
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">📜 Order History: <?= e($detail_customer['name']) ?></h3>
            <a href="customers.php" class="modal-close" style="color: var(--text-muted); font-size: 1.25rem;">&times;</a>
        </div>
        <div style="padding: 1rem 0;">
            <p class="mb-2"><strong>Email:</strong> <?= e($detail_customer['email']) ?> | <strong>Phone:</strong> <?= e($detail_customer['phone']) ?></p>
            <p class="mb-3"><strong>Address:</strong> <?= e($detail_customer['address']) ?></p>
            
            <h4 class="section-title mt-2">Past Orders (<?= count($customer_orders) ?>)</h4>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Items</th>
                            <th>Total Price</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customer_orders)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-3">No orders found for this customer.</td>
                            </tr>
                        <?php else: 
                            foreach ($customer_orders as $ord): 
                                $items_summary = [];
                                foreach ($ord['items'] as $it) {
                                    $items_summary[] = $it['name'] . ' x' . $it['quantity'];
                                }
                            ?>
                                <tr>
                                    <td class="fw-600 text-accent">#<?= $ord['id'] ?></td>
                                    <td><?= e(implode(', ', $items_summary)) ?></td>
                                    <td class="fw-700"><?= format_price($ord['total']) ?></td>
                                    <td><?= e($ord['payment_method']) ?></td>
                                    <td class="text-muted"><?= format_date($ord['created_at']) ?></td>
                                    <td><?= status_badge($ord['status']) ?></td>
                                </tr>
                            <?php endforeach; 
                        endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search customer by name, email, phone, or address..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    <?php if ($search !== ''): ?>
        <a href="customers.php" class="btn btn-secondary btn-sm">Clear Search</a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Email Address</th>
                <th>Phone Number</th>
                <th>Default Address</th>
                <th>Join Date</th>
                <th style="width: 150px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_customers)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No customers found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginated_customers as $cust): ?>
                    <tr>
                        <td class="fw-600 text-accent">#<?= $cust['id'] ?></td>
                        <td class="fw-600"><?= e($cust['name']) ?></td>
                        <td><?= e($cust['email']) ?></td>
                        <td><?= e($cust['phone']) ?></td>
                        <td><?= e($cust['address']) ?></td>
                        <td class="text-muted"><?= format_date($cust['created_at']) ?></td>
                        <td>
                            <div class="action-cell" style="justify-content: center;">
                                <a href="customers.php?view_history=<?= $cust['id'] ?>" class="btn btn-secondary btn-sm">
                                    📜 History
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?= pagination_html($pagination, '?search=' . urlencode($search) . '&') ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
