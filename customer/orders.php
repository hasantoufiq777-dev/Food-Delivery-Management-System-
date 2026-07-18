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

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order_review'])) {
    $order_id = (int)$_POST['order_id'];
    $rating = (int)$_POST['rating'];
    $comments = trim($_POST['comments']);
    
    // Fetch order details to get restaurant_id
    $ord_details = get_db_order($order_id);
    if ($ord_details && (int)$ord_details['customer_id'] === $customer_id && $ord_details['status'] === 'delivered') {
        try {
            // Check if already reviewed
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE order_id = :oid");
            $check_stmt->execute(['oid' => $order_id]);
            if ($check_stmt->fetchColumn() == 0) {
                // Call stored procedure to submit review
                $stmt = $conn->prepare("CALL submit_review(?, ?, ?, ?, ?)");
                $stmt->execute([
                    $customer_id,
                    $ord_details['restaurant_id'],
                    $order_id,
                    $rating,
                    $comments
                ]);
                set_flash('success', 'Thank you for your feedback! Your review has been submitted.');
            } else {
                set_flash('error', 'You have already reviewed this order.');
            }
        } catch (PDOException $e) {
            set_flash('error', 'Database Error: ' . $e->getMessage());
        }
    } else {
        set_flash('error', 'Invalid order for review.');
    }
    header('Location: orders.php?id=' . $order_id);
    exit;
}

// Handle Order Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    
    // Fetch order details
    $ord_details = get_db_order($order_id);
    if ($ord_details && (int)$ord_details['customer_id'] === $customer_id) {
        if ($ord_details['status'] === 'placed') {
            try {
                // Update order status to cancelled
                $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', updated_at = SYSDATE WHERE order_id = :oid");
                $stmt->execute(['oid' => $order_id]);
                
                set_flash('success', 'Order #' . $order_id . ' has been cancelled successfully. A 100% refund has been processed.');
            } catch (PDOException $e) {
                set_flash('error', 'Database Error: ' . $e->getMessage());
            }
        } else {
            set_flash('error', 'Cannot cancel order because it is already being prepared or delivered.');
        }
    } else {
        set_flash('error', 'Invalid order for cancellation.');
    }
    header('Location: orders.php?id=' . $order_id);
    exit;
}

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
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <?php if ($detail_order['status'] === 'placed'): ?>
                <form method="POST" action="orders.php" onsubmit="return confirm('Are you sure you want to cancel this order? You will receive a 100% refund.');" style="margin: 0;">
                    <input type="hidden" name="order_id" value="<?= $detail_order['id'] ?>">
                    <input type="hidden" name="cancel_order" value="1">
                    <button type="submit" class="btn btn-danger btn-sm" style="display: inline-flex; align-items: center; gap: 0.25rem;">
                        ❌ Cancel Order
                    </button>
                </form>
            <?php endif; ?>
            <?= status_badge($detail_order['status']) ?>
        </div>
    </div>

    <!-- Status Tracking Timeline -->
    <div class="card mb-3">
        <h3 class="section-title">📦 Delivery Progress</h3>
        <?= order_timeline($detail_order['status']) ?>
    </div>

    <?php if ($detail_order['status'] === 'cancelled'): ?>
        <div class="card mb-3" style="border-left: 4px solid #EF4444; background-color: #FEF2F2; padding: 1rem 1.5rem;">
            <h3 class="section-title" style="color: #991B1B; margin: 0;">💵 Refund Processed</h3>
            <p style="margin: 0.5rem 0 0; font-size: 0.92rem; color: #7F1D1D; line-height: 1.4;">
                <strong>Refund Policy:</strong> Since this order was cancelled before the restaurant confirmed or prepared it, a <strong>100% full refund</strong> of <strong><?= format_price($detail_order['total']) ?></strong> has been successfully credited back to your original payment method.
            </p>
        </div>
    <?php endif; ?>

    <?php if ($detail_order['status'] === 'delivered'): ?>
        <?php
        // Fetch existing review
        $review_stmt = $conn->prepare("SELECT * FROM reviews WHERE order_id = :oid");
        $review_stmt->execute(['oid' => $detail_order['id']]);
        $existing_review = db_normalize($review_stmt->fetch(PDO::FETCH_ASSOC));
        ?>
        
        <div class="card mb-3">
            <h3 class="section-title">⭐ Restaurant Feedback</h3>
            <?php if ($existing_review): ?>
                <div class="review-display" style="padding: 0.5rem 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <span style="font-size: 1.25rem; color: #FBBF24;">
                            <?= str_repeat('★', $existing_review['rating']) . str_repeat('☆', 5 - $existing_review['rating']) ?>
                        </span>
                        <strong style="font-size: 1rem; color: var(--text-dark);"><?= $existing_review['rating'] ?>.0 / 5.0</strong>
                    </div>
                    <?php if (!empty($existing_review['comments'])): ?>
                        <blockquote style="margin: 0; padding: 0.75rem 1rem; background: var(--bg-tint); border-left: 4px solid var(--accent); border-radius: 4px; font-style: italic; color: var(--text-secondary);">
                            "<?= e($existing_review['comments']) ?>"
                        </blockquote>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <form method="POST" action="orders.php" style="display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem;">
                    <input type="hidden" name="order_id" value="<?= $detail_order['id'] ?>">
                    <input type="hidden" name="submit_order_review" value="1">
                    
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Your Rating *</label>
                        <div class="rating-selector" style="display: flex; gap: 0.5rem;">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required <?= $i === 5 ? 'checked' : '' ?> style="display:none;">
                                <label for="star<?= $i ?>" class="star-label" onclick="selectStars(<?= $i ?>)" id="lbl-star<?= $i ?>" style="font-size: 1.75rem; color: #FBBF24; cursor: pointer;">
                                    ★
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Comments & Feedback</label>
                        <textarea name="comments" class="form-input" rows="3" placeholder="Tell us about your food and delivery experience..." style="resize: none;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Submit Review</button>
                </form>
                
                <script>
                function selectStars(rating) {
                    for (let i = 1; i <= 5; i++) {
                        const lbl = document.getElementById('lbl-star' + i);
                        if (i <= rating) {
                            lbl.textContent = '★';
                        } else {
                            lbl.textContent = '☆';
                        }
                    }
                    document.getElementById('star' + rating).checked = true;
                }
                // Initialize default star state
                selectStars(5);
                </script>
            <?php endif; ?>
        </div>
    <?php endif; ?>

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
