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

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']['items'])) {
    set_flash('error', 'Your cart is empty. Please add items to proceed.');
    header('Location: browse.php');
    exit;
}

// Find customer details
$customer = find_by_id($customers, $customer_id);
$cart = $_SESSION['cart'];
$restaurant = find_by_id($restaurants, $cart['restaurant_id']);

// Handle Place Order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $delivery_address = trim($_POST['address']);
    $payment_method = trim($_POST['payment_method']);
    
    if (empty($delivery_address) || empty($payment_method)) {
        set_flash('error', 'Please fill in delivery details.');
    } else {
        // Place new order
        $new_order_id = count($orders) > 0 ? max(array_column($orders, 'id')) + 1 : 1001;
        
        // Re-simulate order insertion (we would put this in session database simulation)
        $new_order = [
            'id' => $new_order_id,
            'customer_id' => $customer_id,
            'restaurant_id' => $cart['restaurant_id'],
            'agent_id' => null, // Needs assignment by Admin
            'items' => $cart['items'],
            'subtotal' => $cart['subtotal'],
            'delivery_fee' => $cart['delivery_fee'],
            'total' => $cart['total'],
            'status' => 'placed',
            'payment_method' => $payment_method,
            'delivery_address' => $delivery_address,
            'created_at' => date('Y-m-d H:i:s'),
            'delivered_at' => null
        ];
        
        // Save to session-based orders simulation
        if (!isset($_SESSION['orders_sim'])) {
            $_SESSION['orders_sim'] = $orders;
        }
        $_SESSION['orders_sim'][] = $new_order;
        
        // Empty cart
        $_SESSION['cart'] = $default_cart;
        
        set_flash('success', 'Thank you! Your order #' . $new_order_id . ' has been placed successfully.');
        header('Location: orders.php?id=' . $new_order_id);
        exit;
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Checkout</h1>
        <p class="page-subtitle">Confirm your delivery details and place order</p>
    </div>
</div>

<div class="checkout-layout">
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
    <div class="cart-summary">
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
        <div class="summary-row total">
            <span>Total</span>
            <span class="summary-value"><?= format_price($cart['total']) ?></span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
