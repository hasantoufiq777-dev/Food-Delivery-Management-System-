<?php
/**
 * Customer Shopping Cart Page
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'customer';
if (!isset($_SESSION['customer_id'])) {
    $_SESSION['customer_id'] = 1;
}

require_once __DIR__ . '/../includes/header.php';

// ─── Handle Quantity Actions (Increment, Decrement, Remove) ────
if (isset($_GET['action']) && isset($_GET['item_id'])) {
    $action = $_GET['action'];
    $item_id = (int)$_GET['item_id'];
    
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart']['items'])) {
        foreach ($_SESSION['cart']['items'] as $index => &$ci) {
            if ($ci['menu_item_id'] === $item_id) {
                if ($action === 'inc') {
                    $ci['quantity']++;
                } elseif ($action === 'dec') {
                    $ci['quantity']--;
                    if ($ci['quantity'] <= 0) {
                        unset($_SESSION['cart']['items'][$index]);
                    }
                } elseif ($action === 'remove') {
                    unset($_SESSION['cart']['items'][$index]);
                }
                break;
            }
        }
        
        // Re-index array
        $_SESSION['cart']['items'] = array_values($_SESSION['cart']['items']);
        
        // Recalculate totals
        if (empty($_SESSION['cart']['items'])) {
            $_SESSION['cart'] = $default_cart;
            set_flash('success', 'Cart emptied.');
        } else {
            $subtotal = 0;
            foreach ($_SESSION['cart']['items'] as $ci) {
                $subtotal += $ci['price'] * $ci['quantity'];
            }
            $_SESSION['cart']['subtotal'] = $subtotal;
            $_SESSION['cart']['total'] = $subtotal + $_SESSION['cart']['delivery_fee'];
        }
    }
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? $default_cart;
$restaurant = $cart['restaurant_id'] ? find_by_id($restaurants, $cart['restaurant_id']) : null;
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Shopping Cart</h1>
        <p class="page-subtitle">Verify items and proceed to checkout</p>
    </div>
</div>

<?php if (empty($cart['items'])): ?>
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">🛒</div>
            <div class="empty-state-title">Your cart is empty</div>
            <div class="empty-state-desc">Looks like you haven't added any items to your cart yet. Go back to browsing and find your favorite dishes!</div>
            <a href="browse.php" class="btn btn-primary">Browse Restaurants</a>
        </div>
    </div>
<?php else: ?>
    <div class="two-col">
        <!-- Cart Items List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🍽️ Items from <?= e($restaurant ? $restaurant['name'] : 'Restaurant') ?></h3>
                <a href="browse.php?view_menu_id=<?= $cart['restaurant_id'] ?>" class="btn btn-secondary btn-sm">+ Add More</a>
            </div>
            
            <div class="cart-items">
                <?php foreach ($cart['items'] as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <h4 class="cart-item-name"><?= e($item['name']) ?></h4>
                            <span class="cart-item-price"><?= format_price($item['price']) ?> each</span>
                        </div>
                        
                        <div class="cart-quantity">
                            <button class="qty-btn" onclick="updateCartQuantity(<?= $item['menu_item_id'] ?>, 'dec')">-</button>
                            <span class="qty-value"><?= $item['quantity'] ?></span>
                            <button class="qty-btn" onclick="updateCartQuantity(<?= $item['menu_item_id'] ?>, 'inc')">+</button>
                        </div>
                        
                        <div class="cart-item-total">
                            <?= format_price($item['price'] * $item['quantity']) ?>
                        </div>
                        
                        <a href="cart.php?action=remove&item_id=<?= $item['menu_item_id'] ?>" class="btn btn-secondary btn-icon btn-sm" title="Remove" style="border-color: transparent;">
                            <?= icon('delete', 16) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary & Checkout card -->
        <div class="cart-summary">
            <h3 class="cart-summary-title">Order Summary</h3>
            <div class="summary-row">
                <span>Subtotal</span>
                <span class="summary-value"><?= format_price($cart['subtotal']) ?></span>
            </div>
            <div class="summary-row">
                <span>Delivery Fee</span>
                <span class="summary-value"><?= format_price($cart['delivery_fee']) ?></span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span class="summary-value"><?= format_price($cart['total']) ?></span>
            </div>
            
            <a href="checkout.php" class="btn btn-primary mt-3" style="width: 100%; justify-content: center;">
                <?= icon('checkout', 18) ?> Proceed to Checkout
            </a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
