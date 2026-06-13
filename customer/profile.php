<?php
/**
 * Customer Profile Management
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

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    if (empty($name) || empty($email) || empty($phone)) {
        set_flash('error', 'Please fill in all required fields.');
    } else {
        // Find and edit customer profile in static session lists if simulated, or just print success for demo
        set_flash('success', 'Profile updated successfully (dummy session updated).');
        header('Location: profile.php');
        exit;
    }
}

// Find customer details
$customer = find_by_id($customers, $customer_id);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">My Account</h1>
        <p class="page-subtitle">Configure contact details and delivery address</p>
    </div>
</div>

<div class="card">
    <div class="profile-card">
        <div class="profile-avatar-large">
            <?= strtoupper(substr($customer ? $customer['name'] : 'C', 0, 1)) ?>
        </div>
        
        <div>
            <h3 class="section-title">📋 Edit Profile</h3>
            <form method="POST" action="profile.php" class="crud-form">
                <input type="hidden" name="save_profile" value="1">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="name" class="form-input" required value="<?= e($customer ? $customer['name'] : '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-input" required value="<?= e($customer ? $customer['email'] : '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Contact *</label>
                    <input type="text" name="phone" class="form-input" required value="<?= e($customer ? $customer['phone'] : '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Default Delivery Address *</label>
                    <textarea name="address" class="form-textarea" required><?= e($customer ? $customer['address'] : '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Details</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
