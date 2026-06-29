<?php
/**
 * Customer Restaurant & Menu Browser
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


//  Handle Add To Cart 
if (isset($_GET['add_to_cart']) && isset($_GET['restaurant_id'])) {
    $item_id = (int)$_GET['add_to_cart'];
    $rest_id = (int)$_GET['restaurant_id'];
    
    // Get database cart
    $cart = get_db_cart($_SESSION['customer_id']);
    
    // Check if cart already has items from another restaurant
    if ($cart['restaurant_id'] !== null && (int)$cart['restaurant_id'] !== $rest_id) {
        set_flash('error', 'Your cart contains items from another restaurant. Please empty your cart first.');
    } else {
        // Find item details
        $item = get_db_menu_item($item_id);
        if ($item && $item['available']) {
            try {
                // Call stored procedure
                $stmt = $conn->prepare("CALL add_to_cart(:cid, :iid, 1)");
                $stmt->execute([
                    'cid' => $_SESSION['customer_id'],
                    'iid' => $item_id
                ]);
                set_flash('success', '"' . $item['name'] . '" added to cart.');
            } catch (PDOException $ex) {
                set_flash('error', 'Database Error: ' . $ex->getMessage());
            }
        } else {
            set_flash('error', 'Item is not available.');
        }
    }
    
    // Redirect back to browse
    $redirect_url = 'browse.php';
    if (isset($_GET['view_menu_id'])) {
        $redirect_url .= '?view_menu_id=' . (int)$_GET['view_menu_id'];
    }
    header('Location: ' . $redirect_url);
    exit;
}

// ─── Browser Modes (Browse Restaurants vs View Menu) ─────────────
$view_restaurant_id = isset($_GET['view_menu_id']) ? (int)$_GET['view_menu_id'] : null;

if ($view_restaurant_id) {
    // ─── Mode 2: View Menu of Specific Restaurant ────────────────
    $restaurant = find_by_id($restaurants, $view_restaurant_id);
    if (!$restaurant) {
        set_flash('error', 'Restaurant not found.');
        header('Location: browse.php');
        exit;
    }
    
    // Get menu items of this restaurant
    $restaurant_menu = array_filter($menu_items, function($m) use ($view_restaurant_id) {
        return $m['restaurant_id'] == $view_restaurant_id;
    });
    
    // Category filter for menu
    $menu_cat_filter = $_GET['category'] ?? '';
    if ($menu_cat_filter !== '') {
        $restaurant_menu = filter_by($restaurant_menu, 'category', $menu_cat_filter);
    }
    
    // Extract unique categories for filter pills
    $avail_categories = array_unique(array_column($restaurant_menu, 'category'));
    
    ?>
    <div class="page-header">
        <div>
            <a href="browse.php" class="btn btn-secondary btn-sm mb-1">← Back to Restaurants</a>
            <h1 class="page-title"><?= e($restaurant['name']) ?></h1>
            <p class="page-subtitle"><?= e($restaurant['cuisine']) ?> Cuisine • 📍 <?= e($restaurant['address']) ?></p>
        </div>
        <div>
            <?= star_rating($restaurant['rating']) ?>
        </div>
    </div>
    
    <!-- Category Filter Pills -->
    <div class="category-filter">
        <a href="browse.php?view_menu_id=<?= $view_restaurant_id ?>" class="category-pill <?= $menu_cat_filter === '' ? 'active' : '' ?>">All Dishes</a>
        <?php foreach ($menu_categories as $cat): 
            // Only show category if restaurant has items in it
            $has_items = count(array_filter($menu_items, function($m) use ($view_restaurant_id, $cat) {
                return $m['restaurant_id'] == $view_restaurant_id && $m['category'] === $cat;
            })) > 0;
            if ($has_items):
        ?>
            <a href="browse.php?view_menu_id=<?= $view_restaurant_id ?>&category=<?= urlencode($cat) ?>" 
               class="category-pill <?= $menu_cat_filter === $cat ? 'active' : '' ?>"><?= e($cat) ?></a>
        <?php endif; endforeach; ?>
    </div>

    <!-- Dishes Grid -->
    <div class="menu-grid">
        <?php if (empty($restaurant_menu)): ?>
            <div class="card" style="grid-column: 1 / -1;">
                <div class="empty-state">
                    <div class="empty-state-icon">🍽️</div>
                    <div class="empty-state-title">No items in this category</div>
                    <div class="empty-state-desc">Try checking other categories for delicious options.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($restaurant_menu as $item): ?>
                <div class="menu-card <?= !$item['available'] ? 'menu-card-unavailable' : '' ?>">
                    <div class="restaurant-card-img-wrapper" style="height: 160px;">
                        <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?>" class="menu-card-img">
                        <?php if (!$item['available']): ?>
                            <span class="unavailable-tag">Sold Out</span>
                        <?php endif; ?>
                    </div>
                    <div class="menu-card-body">
                        <div class="d-flex align-center" style="justify-content: space-between; margin-bottom: 0.25rem;">
                            <span class="badge badge-default"><?= e($item['category']) ?></span>
                            <span class="menu-card-price"><?= format_price($item['price']) ?></span>
                        </div>
                        <h4 class="menu-card-name"><?= e($item['name']) ?></h4>
                        <p class="menu-card-desc"><?= e($item['description']) ?></p>
                        
                        <div class="mt-2" style="border-top: 1px solid var(--border); padding-top: 0.75rem;">
                            <?php if ($item['available']): ?>
                                <button onclick="addToCart(<?= $item['id'] ?>, <?= $view_restaurant_id ?>)" class="btn btn-primary btn-sm" style="width: 100%;">
                                    <?= icon('cart', 14) ?> Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" style="width: 100%;" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php } else { 
    // ─── Mode 1: Browse Restaurants List ─────────────────────────
    $cuisine_filter = $_GET['cuisine'] ?? '';
    $search = $_GET['search'] ?? '';
    
    $active_restaurants = $restaurants;
    
    if ($cuisine_filter !== '') {
        $active_restaurants = filter_by($active_restaurants, 'cuisine', $cuisine_filter);
    }
    if ($search !== '') {
        $active_restaurants = search_array($active_restaurants, $search, ['name', 'cuisine', 'address']);
    }
    
    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $pagination = paginate($active_restaurants, $page, 6);
    $paginated_restaurants = $pagination['data'];
    ?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Order Food Online</h1>
            <p class="page-subtitle">Browse premium local restaurants & menus</p>
        </div>
    </div>
    
    <!-- Filter bar -->
    <div class="filter-bar">
        <div class="search-box">
            <span class="search-icon"><?= icon('browse', 18) ?></span>
            <input type="text" class="form-input" placeholder="Search restaurant, cuisine, location..." 
                   value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
        </div>
        
        <select class="form-select filter-select" onchange="applyFilter('cuisine', this.value)">
            <option value="">All Cuisines</option>
            <?php foreach ($cuisine_types as $c_type): ?>
                <option value="<?= $c_type ?>" <?= $cuisine_filter === $c_type ? 'selected' : '' ?>><?= $c_type ?></option>
            <?php endforeach; ?>
        </select>
        
        <?php if ($search !== '' || $cuisine_filter !== ''): ?>
            <a href="browse.php" class="btn btn-secondary btn-sm">Clear Filters</a>
        <?php endif; ?>
    </div>

    <!-- Restaurants list grid -->
    <div class="restaurant-grid">
        <?php if (empty($paginated_restaurants)): ?>
            <div class="card" style="grid-column: 1 / -1;">
                <div class="empty-state">
                    <div class="empty-state-icon">🏢</div>
                    <div class="empty-state-title">No restaurants found</div>
                    <div class="empty-state-desc">Try widening your search terms or selecting another cuisine.</div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($paginated_restaurants as $rest): ?>
                <a href="browse.php?view_menu_id=<?= $rest['id'] ?>" class="restaurant-card">
                    <div class="restaurant-card-img-wrapper">
                        <img src="<?= e($rest['image_url']) ?>" alt="<?= e($rest['name']) ?>" class="restaurant-card-img">
                        <span class="restaurant-card-cuisine"><?= e($rest['cuisine']) ?></span>
                    </div>
                    <div class="restaurant-card-body">
                        <div class="d-flex align-center" style="justify-content: space-between; margin-bottom: 0.25rem;">
                            <h3 class="restaurant-card-name"><?= e($rest['name']) ?></h3>
                            <?= star_rating($rest['rating']) ?>
                        </div>
                        <div class="restaurant-card-address">
                            <?= icon('location', 14) ?> <?= e($rest['address']) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?= pagination_html($pagination, '?search=' . urlencode($search) . '&cuisine=' . urlencode($cuisine_filter) . '&') ?>

<?php } ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
