<?php
/**
 * Admin Restaurants CRUD Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'admin';

require_once __DIR__ . '/../includes/header.php';

// Initialize session store for restaurants CRUD if not set
if (!isset($_SESSION['restaurants_crud'])) {
    $_SESSION['restaurants_crud'] = $restaurants;
}

$message_action = '';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        // Delete user (cascades to restaurants due to ON DELETE CASCADE)
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = (SELECT user_id FROM restaurants WHERE restaurant_id = :rid)");
        $stmt->execute(['rid' => $id]);
        set_flash('success', 'Restaurant deleted successfully.');
    } catch (PDOException $ex) {
        set_flash('error', 'Database Error: ' . $ex->getMessage());
    }
    header('Location: restaurants.php');
    exit;
}

// Handle Add / Edit Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_restaurant'])) {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $name = trim($_POST['name']);
    $cuisine = trim($_POST['cuisine']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $rating = (float)$_POST['rating'];
    $image_url = trim($_POST['image_url']);
    
    if (empty($name) || empty($cuisine) || empty($address)) {
        set_flash('error', 'Please fill in all required fields.');
    } else {
        try {
            $img = $image_url ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop';
            if ($id === null) {
                // Add New Restaurant
                // 1. Create User
                $stmt = $conn->prepare("SELECT NVL(MAX(user_id), 0) + 1 FROM users");
                $stmt->execute();
                $new_user_id = $stmt->fetchColumn();
                
                $email = strtolower(str_replace(' ', '', $name)) . '@example.com';
                $stmt = $conn->prepare("CALL register_user(:uid, :name, :email, 'password', :phone, 'restaurant', NULL)");
                $stmt->execute([
                    'uid' => $new_user_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone
                ]);
                
                // 2. Create Restaurant mapping
                $stmt = $conn->prepare("SELECT NVL(MAX(restaurant_id), 0) + 1 FROM restaurants");
                $stmt->execute();
                $new_rest_id = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("INSERT INTO restaurants (restaurant_id, user_id, name, address, cuisine_type, rating, status, image_url) 
                                        VALUES (:rid, :uid, :name, :address, :cuisine, :rating, 'active', :img)");
                $stmt->execute([
                    'rid' => $new_rest_id,
                    'uid' => $new_user_id,
                    'name' => $name,
                    'address' => $address,
                    'cuisine' => $cuisine,
                    'rating' => $rating ?: 4.5,
                    'img' => $img
                ]);
                set_flash('success', 'Restaurant added successfully.');
            } else {
                // Edit Restaurant
                // 1. Update User details
                $stmt = $conn->prepare("UPDATE users SET name = :name, phone = :phone WHERE user_id = (SELECT user_id FROM restaurants WHERE restaurant_id = :rid)");
                $stmt->execute(['name' => $name, 'phone' => $phone, 'rid' => $id]);
                
                // 2. Update Restaurant details
                $stmt = $conn->prepare("UPDATE restaurants SET name = :name, address = :address, cuisine_type = :cuisine, rating = :rating, image_url = :img 
                                        WHERE restaurant_id = :rid");
                $stmt->execute([
                    'name' => $name,
                    'address' => $address,
                    'cuisine' => $cuisine,
                    'rating' => $rating,
                    'img' => $img,
                    'rid' => $id
                ]);
                set_flash('success', 'Restaurant updated successfully.');
            }
        } catch (PDOException $ex) {
            set_flash('error', 'Database Error: ' . $ex->getMessage());
        }
        header('Location: restaurants.php');
        exit;
    }
}

// Get active items from database
$current_restaurants = get_db_restaurants();

// Search Filter
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $current_restaurants = search_array($current_restaurants, $search, ['name', 'cuisine', 'address']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($current_restaurants, $page, 5);
$paginated_restaurants = $pagination['data'];

// Determine if we are editing an item
$editing_rest = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $editing_rest = get_db_restaurant($edit_id);
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Manage Restaurants</h1>
        <p class="page-subtitle">Add, edit, or remove restaurant partners</p>
    </div>
    <button class="btn btn-primary" onclick="toggleForm('restaurantFormCard')">
        <?= icon('plus', 16) ?> Add Restaurant
    </button>
</div>

<!-- Add/Edit Form Card -->
<div class="card mb-3" id="restaurantFormCard" style="<?= ($editing_rest !== null) ? 'display: block;' : 'display: none;' ?>">
    <div class="card-header">
        <h3 class="card-title"><?= $editing_rest ? '✏️ Edit Restaurant: ' . e($editing_rest['name']) : '➕ Add New Restaurant' ?></h3>
        <button class="modal-close" onclick="toggleForm('restaurantFormCard')"><?= icon('close', 20) ?></button>
    </div>
    <form method="POST" action="restaurants.php" class="crud-form">
        <input type="hidden" name="id" value="<?= $editing_rest ? $editing_rest['id'] : '' ?>">
        <input type="hidden" name="save_restaurant" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Restaurant Name *</label>
                <input type="text" name="name" class="form-input" required value="<?= $editing_rest ? e($editing_rest['name']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Cuisine Type *</label>
                <select name="cuisine" class="form-select" required>
                    <option value="">Select Cuisine</option>
                    <?php foreach ($cuisine_types as $c_type): ?>
                        <option value="<?= $c_type ?>" <?= ($editing_rest && $editing_rest['cuisine'] === $c_type) ? 'selected' : '' ?>><?= $c_type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-input" value="<?= $editing_rest ? e($editing_rest['phone']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Rating (1.0 to 5.0)</label>
                <input type="number" name="rating" class="form-input" step="0.1" min="1" max="5" value="<?= $editing_rest ? e($editing_rest['rating']) : '4.5' ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Restaurant Address *</label>
            <input type="text" name="address" class="form-input" required value="<?= $editing_rest ? e($editing_rest['address']) : '' ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Image URL</label>
            <input type="url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg" value="<?= $editing_rest ? e($editing_rest['image_url']) : '' ?>">
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Restaurant</button>
            <button type="button" class="btn btn-secondary" onclick="toggleForm('restaurantFormCard')">Cancel</button>
        </div>
    </form>
</div>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search by name, cuisine, address..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    <?php if ($search !== ''): ?>
        <a href="restaurants.php" class="btn btn-secondary btn-sm">Clear Search</a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70px;">Image</th>
                <th>Restaurant Info</th>
                <th>Cuisine</th>
                <th>Rating</th>
                <th>Phone</th>
                <th>Address</th>
                <th style="width: 140px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_restaurants)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No restaurants found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginated_restaurants as $rest): ?>
                    <tr>
                        <td>
                            <img src="<?= e($rest['image_url']) ?>" alt="Logo" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td class="fw-600"><?= e($rest['name']) ?></td>
                        <td><span class="badge badge-default"><?= e($rest['cuisine']) ?></span></td>
                        <td><?= star_rating($rest['rating']) ?></td>
                        <td><?= e($rest['phone']) ?></td>
                        <td><?= e($rest['address']) ?></td>
                        <td>
                            <div class="action-cell" style="justify-content: center;">
                                <a href="restaurants.php?action=edit&id=<?= $rest['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                    <?= icon('edit', 16) ?>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm btn-icon" title="Delete" 
                                   data-delete="restaurants.php?action=delete&id=<?= $rest['id'] ?>" 
                                   data-name="<?= e($rest['name']) ?>">
                                    <?= icon('delete', 16) ?>
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
