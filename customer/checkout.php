<?php
/**
 * Customer Checkout Page
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

// Get database cart
$cart = get_db_cart($customer_id);

// Check if cart is empty
if (empty($cart['items'])) {
    set_flash('error', 'Your cart is empty. Please add items to proceed.');
    header('Location: browse.php');
    exit;
}

// Find customer details
$customer = get_db_customer($customer_id);
$restaurant = find_by_id($restaurants, $cart['restaurant_id']);

// Handle Place Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['address']);
    $payment_method = trim($_POST['payment_method']);
    
    if (empty($delivery_address) || empty($payment_method)) {
        set_flash('error', 'Please fill in delivery details.');
    } else {
        try {
            $new_order_id = 0;
            // Call the database stored procedure
            $stmt = $conn->prepare("CALL place_order_from_cart(:cid, :rid, :address, :payment, :fee, :out_id)");
            $stmt->bindParam(':cid', $customer_id, PDO::PARAM_INT);
            $stmt->bindParam(':rid', $cart['restaurant_id'], PDO::PARAM_INT);
            $stmt->bindParam(':address', $delivery_address, PDO::PARAM_STR);
            $stmt->bindParam(':payment', $payment_method, PDO::PARAM_STR);
            $stmt->bindValue(':fee', $cart['delivery_fee']);
            $stmt->bindParam(':out_id', $new_order_id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT, 38);
            $stmt->execute();
            
            // Sync local session states
            $_SESSION['cart'] = $default_cart;
            unset($_SESSION['orders_sim']); // Purge simulation cache so header.php fetches fresh from DB
            
            set_flash('success', 'Thank you! Your order #' . $new_order_id . ' has been placed successfully.');
            header('Location: orders.php?id=' . $new_order_id);
            exit;
        } catch (PDOException $ex) {
            set_flash('error', 'Database Error: ' . $ex->getMessage());
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Confirm your delivery details and place order</p>
    </div>
</div>

<div class="cart-layout">
    <!-- Delivery Address and Payment Methods Form -->
    <div class="card">
        <h3 class="section-title">📍 Delivery Details</h3>
        <form method="POST" action="checkout.php" class="crud-form">
            <input type="hidden" name="place_order" value="1">
            
            <div class="form-group">
                <label class="form-label">Deliver To *</label>
                <input type="text" class="form-input" readonly value="<?= e($customer ? $customer['name'] : '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Contact *</label>
                <input type="text" class="form-input" readonly value="<?= e($customer ? $customer['phone'] : '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Delivery Address *</label>
                <textarea name="address" class="form-textarea" required placeholder="Enter detailed delivery address..."><?= e($customer ? $customer['address'] : '') ?></textarea>
            </div>
            
            <h3 class="section-title mt-3">💳 Payment Method</h3>
            <div class="form-group">
                <div class="form-row">
                    <label class="profile-info-item d-flex align-center gap-1" style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="Credit Card" checked style="accent-color: var(--accent);">
                        <span>💳 Credit Card</span>
                    </label>
                    <label class="profile-info-item d-flex align-center gap-1" style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="PayPal" style="accent-color: var(--accent);">
                        <span>🌐 PayPal</span>
                    </label>
                    <label class="profile-info-item d-flex align-center gap-1" style="cursor: pointer;">
                        <input type="radio" name="payment_method" value="Cash on Delivery" style="accent-color: var(--accent);">
                        <span>💵 Cash on Delivery</span>
                    </label>
                </div>
            </div>
            
            <div class="btn-group mt-3">
                <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                <a href="cart.php" class="btn btn-secondary btn-lg">Back to Cart</a>
            </div>
        </form>
    </div>

    <!-- Right Side Order Details Summary -->
    <div class="cart-summary sticky-summary">
        <h3 class="cart-summary-title">Summary</h3>
        <ul style="list-style: none; padding: 0; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
            <?php foreach ($cart['items'] as $item): ?>
                <li style="display: flex; justify-content: space-between; font-size: 0.88rem;">
                    <span><?= e($item['name']) ?> <strong>x<?= $item['quantity'] ?></strong></span>
                    <span class="text-secondary"><?= format_price($item['price'] * $item['quantity']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="summary-row">
            <span>Subtotal</span>
            <span><?= format_price($cart['subtotal']) ?></span>
        </div>
        <div class="summary-row">
            <span>Delivery Fee</span>
            <span><?= format_price($cart['delivery_fee']) ?></span>
        </div>
        <div class="summary-row total" style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 1rem;">
            <span>Total</span>
            <span class="summary-value"><?= format_price($cart['total']) ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
