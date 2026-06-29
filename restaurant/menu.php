<?php
/**
 * Restaurant Menu Item CRUD Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'restaurant';
if (!isset($_SESSION['restaurant_id'])) {
    $_SESSION['restaurant_id'] = 1;
}

$restaurant_id = $_SESSION['restaurant_id'];

require_once __DIR__ . '/../includes/header.php';

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurant_id]);
        set_flash('success', 'Menu item deleted successfully.');
    } catch (PDOException $ex) {
        set_flash('error', 'Database Error: ' . $ex->getMessage());
    }
    header('Location: menu.php');
    exit;
}

// Handle Toggle Availability
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $conn->prepare("UPDATE menu_items SET is_available = CASE WHEN is_available = 1 THEN 0 ELSE 1 END WHERE item_id = :id AND restaurant_id = :rid");
        $stmt->execute(['id' => $id, 'rid' => $restaurant_id]);
        set_flash('success', 'Dish availability updated.');
    } catch (PDOException $ex) {
        set_flash('error', 'Database Error: ' . $ex->getMessage());
    }
    header('Location: menu.php');
    exit;
}

// Handle Add / Edit Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_menu_item'])) {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category = trim($_POST['category']);
    $image_url = trim($_POST['image_url']);
    $available = isset($_POST['available']) ? 1 : 0;
    
    if (empty($name) || empty($category) || $price <= 0) {
        set_flash('error', 'Please fill in all required fields and input a valid price.');
    } else {
        try {
            if ($id === null) {
                // Add New Menu Item
                $stmt = $conn->prepare("SELECT NVL(MAX(item_id), 0) + 1 FROM menu_items");
                $stmt->execute();
                $new_id = $stmt->fetchColumn();
                
                $img = $image_url ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&h=200&fit=crop';
                
                $stmt = $conn->prepare("INSERT INTO menu_items (item_id, restaurant_id, name, description, price, category, image_url, is_available) 
                                        VALUES (:id, :rid, :name, :desc, :price, :cat, :img, :avail)");
                $stmt->execute([
                    'id' => $new_id,
                    'rid' => $restaurant_id,
                    'name' => $name,
                    'desc' => $description,
                    'price' => $price,
                    'cat' => $category,
                    'img' => $img,
                    'avail' => $available
                ]);
                set_flash('success', 'New dish added to menu successfully.');
            } else {
                // Edit Menu Item
                $img = $image_url ?: 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&h=200&fit=crop';
                
                $stmt = $conn->prepare("UPDATE menu_items SET name = :name, description = :desc, price = :price, category = :cat, image_url = :img, is_available = :avail 
                                        WHERE item_id = :id AND restaurant_id = :rid");
                $stmt->execute([
                    'name' => $name,
                    'desc' => $description,
                    'price' => $price,
                    'cat' => $category,
                    'img' => $img,
                    'avail' => $available,
                    'id' => $id,
                    'rid' => $restaurant_id
                ]);
                set_flash('success', 'Dish updated successfully.');
            }
        } catch (PDOException $ex) {
            set_flash('error', 'Database Error: ' . $ex->getMessage());
        }
        header('Location: menu.php');
        exit;
    }
}

// Fetch menu items for this restaurant from database
$this_menu = get_db_menu_items($restaurant_id);

// Search and Category filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

if ($search !== '') {
    $this_menu = search_array($this_menu, $search, ['name', 'category', 'description']);
}
if ($category_filter !== '') {
    $this_menu = filter_by($this_menu, 'category', $category_filter);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($this_menu, $page, 6);
$paginated_menu = $pagination['data'];

// Editing Menu Item
$editing_item = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $editing_item = get_db_menu_item($edit_id);
    if ($editing_item && (int)$editing_item['restaurant_id'] !== (int)$restaurant_id) {
        $editing_item = null; // Prevent editing other restaurants' menu
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Manage Menu</h1>
        <p class="page-subtitle">Configure dishes offered in your restaurant menu card</p>
    </div>
    <button class="btn btn-primary" onclick="toggleForm('menuFormCard')">
        <?= icon('plus', 16) ?> Add Dish
    </button>
</div>

<!-- Add/Edit Form Card -->
<div class="card mb-3" id="menuFormCard" style="<?= ($editing_item !== null) ? 'display: block;' : 'display: none;' ?>">
    <div class="card-header">
        <h3 class="card-title"><?= $editing_item ? '✏️ Edit Dish: ' . e($editing_item['name']) : '➕ Add New Dish' ?></h3>
        <button class="modal-close" onclick="toggleForm('menuFormCard')"><?= icon('close', 20) ?></button>
    </div>
    <form method="POST" action="menu.php" class="crud-form">
        <input type="hidden" name="id" value="<?= $editing_item ? $editing_item['id'] : '' ?>">
        <input type="hidden" name="save_menu_item" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Dish Name *</label>
                <input type="text" name="name" class="form-input" required value="<?= $editing_item ? e($editing_item['name']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php foreach ($menu_categories as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editing_item && $editing_item['category'] === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Price ($) *</label>
                <input type="number" name="price" class="form-input" step="0.01" min="0.1" required value="<?= $editing_item ? e($editing_item['price']) : '' ?>">
            </div>
            <div class="form-group" style="display: flex; align-items: center; padding-top: 1.5rem;">
                <div class="form-check">
                    <input type="checkbox" name="available" id="avail_chk" <?= (!$editing_item || $editing_item['available']) ? 'checked' : '' ?>>
                    <label class="form-label" style="display: inline; cursor: pointer;" for="avail_chk">Available for order</label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-textarea" placeholder="Describe this dish..."><?= $editing_item ? e($editing_item['description']) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Image URL</label>
            <input type="url" name="image_url" class="form-input" placeholder="https://example.com/dish.jpg" value="<?= $editing_item ? e($editing_item['image_url']) : '' ?>">
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Dish</button>
            <button type="button" class="btn btn-secondary" onclick="toggleForm('menuFormCard')">Cancel</button>
        </div>
    </form>
</div>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search dish..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    
    <select class="form-select filter-select" onchange="applyFilter('category', this.value)">
        <option value="">All Categories</option>
        <?php foreach ($menu_categories as $cat): ?>
            <option value="<?= $cat ?>" <?= $category_filter === $cat ? 'selected' : '' ?>><?= $cat ?></option>
        <?php endforeach; ?>
    </select>
    
    <?php if ($search !== '' || $category_filter !== ''): ?>
        <a href="menu.php" class="btn btn-secondary btn-sm">Clear Filters</a>
    <?php endif; ?>
</div>

<!-- Grid Display of Menu Items -->
<div class="menu-grid">
    <?php if (empty($paginated_menu)): ?>
        <div class="card" style="grid-column: 1 / -1;">
            <div class="empty-state">
                <div class="empty-state-icon">🍽️</div>
                <div class="empty-state-title">No menu items found</div>
                <div class="empty-state-desc">Start adding dishes to make your restaurant active for food delivery order requests.</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($paginated_menu as $item): ?>
            <div class="menu-card <?= !$item['available'] ? 'menu-card-unavailable' : '' ?>">
                <div class="restaurant-card-img-wrapper" style="height: 150px;">
                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="menu-card-img">
                    <?php if (!$item['available']): ?>
                        <span class="unavailable-tag">Unavailable</span>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="d-flex align-center" style="justify-content: space-between; margin-bottom: 0.25rem;">
                        <span class="badge badge-default"><?= e($item['category']) ?></span>
                        <span class="menu-card-price"><?= format_price($item['price']) ?></span>
                    </div>
                    <h4 class="menu-card-name"><?= e($item['name']) ?></h4>
                    <p class="menu-card-desc"><?= e($item['description']) ?></p>
                    
                    <div class="menu-card-footer mt-2" style="border-top: 1px solid var(--border); padding-top: 0.75rem;">
                        <div class="d-flex gap-1 align-center">
                            <label class="toggle">
                                <input type="checkbox" <?= $item['available'] ? 'checked' : '' ?>
                                       onchange="window.location.href='menu.php?action=toggle&id=<?= $item['id'] ?>'">
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="text-muted" style="font-size: 0.75rem;">Available</span>
                        </div>
                        
                        <div class="action-cell">
                            <a href="menu.php?action=edit&id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <?= icon('edit', 14) ?>
                            </a>
                            <a href="#" class="btn btn-danger btn-sm btn-icon" title="Delete" 
                               data-delete="menu.php?action=delete&id=<?= $item['id'] ?>" 
                               data-name="<?= e($item['name']) ?>">
                                <?= icon('delete', 14) ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?= pagination_html($pagination, '?search=' . urlencode($search) . '&category=' . urlencode($category_filter) . '&') ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
