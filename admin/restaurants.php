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
    $original_count = count($_SESSION['restaurants_crud']);
    $_SESSION['restaurants_crud'] = array_filter($_SESSION['restaurants_crud'], function($r) use ($id) {
        return $r['id'] !== $id;
    });
    
    if (count($_SESSION['restaurants_crud']) < $original_count) {
        set_flash('success', 'Restaurant deleted successfully.');
    } else {
        set_flash('error', 'Restaurant not found.');
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
        if ($id === null) {
            // Add New
            $new_id = count($_SESSION['restaurants_crud']) > 0 ? max(array_column($_SESSION['restaurants_crud'], 'id')) + 1 : 1;
            $new_rest = [
                'id' => $new_id,
                'name' => $name,
                'cuisine' => $cuisine,
                'rating' => $rating ?: 4.5,
                'address' => $address,
                'phone' => $phone,
                'image_url' => $image_url ?: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $_SESSION['restaurants_crud'][] = $new_rest;
            set_flash('success', 'Restaurant added successfully.');
        } else {
            // Edit Existing
            foreach ($_SESSION['restaurants_crud'] as &$r) {
                if ($r['id'] === $id) {
                    $r['name'] = $name;
                    $r['cuisine'] = $cuisine;
                    $r['address'] = $address;
                    $r['phone'] = $phone;
                    $r['rating'] = $rating;
                    if ($image_url !== '') {
                        $r['image_url'] = $image_url;
                    }
                    break;
                }
            }
            set_flash('success', 'Restaurant updated successfully.');
        }
        header('Location: restaurants.php');
        exit;
    }
}

// Get active items
$current_restaurants = $_SESSION['restaurants_crud'];

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
    $editing_rest = find_by_id($_SESSION['restaurants_crud'], $edit_id);
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
