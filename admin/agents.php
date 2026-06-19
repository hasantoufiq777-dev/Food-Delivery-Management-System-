<?php
/**
 * Admin Delivery Agents CRUD & Assignment Management
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'admin';

require_once __DIR__ . '/../includes/header.php';

// Initialize session store for agents CRUD if not set
if (!isset($_SESSION['agents_crud'])) {
    $_SESSION['agents_crud'] = $agents;
}

// Handle Delete Agent
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $original_count = count($_SESSION['agents_crud']);
    $_SESSION['agents_crud'] = array_filter($_SESSION['agents_crud'], function($a) use ($id) {
        return $a['id'] !== $id;
    });
    
    if (count($_SESSION['agents_crud']) < $original_count) {
        set_flash('success', 'Delivery Agent deleted successfully.');
    } else {
        set_flash('error', 'Agent not found.');
    }
    header('Location: agents.php');
    exit;
}

// Handle Add / Edit Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_agent'])) {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $vehicle = trim($_POST['vehicle']);
    $status = trim($_POST['status']);
    
    if (empty($name) || empty($phone) || empty($vehicle)) {
        set_flash('error', 'Please fill in all required fields.');
    } else {
        if ($id === null) {
            // Add New Agent
            $new_id = count($_SESSION['agents_crud']) > 0 ? max(array_column($_SESSION['agents_crud'], 'id')) + 1 : 1;
            $new_agent = [
                'id' => $new_id,
                'name' => $name,
                'phone' => $phone,
                'vehicle' => $vehicle,
                'status' => $status ?: 'available',
                'rating' => 5.0,
                'deliveries_completed' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $_SESSION['agents_crud'][] = $new_agent;
            set_flash('success', 'Agent added successfully.');
        } else {
            // Edit Agent
            foreach ($_SESSION['agents_crud'] as &$a) {
                if ($a['id'] === $id) {
                    $a['name'] = $name;
                    $a['phone'] = $phone;
                    $a['vehicle'] = $vehicle;
                    $a['status'] = $status;
                    break;
                }
            }
            set_flash('success', 'Agent updated successfully.');
        }
        header('Location: agents.php');
        exit;
    }
}

// Handle assigning agent to order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_agent'])) {
    $order_id = (int)$_POST['order_id'];
    $agent_id = (int)$_POST['agent_id'];
    
    // In real app, we would update orders database.
    // For dummy purposes, let's store assignments in a session variable.
    if (!isset($_SESSION['assigned_agents'])) {
        $_SESSION['assigned_agents'] = [];
    }
    $_SESSION['assigned_agents'][$order_id] = $agent_id;
    
    // Also update agent status to busy
    foreach ($_SESSION['agents_crud'] as &$a) {
        if ($a['id'] === $agent_id) {
            $a['status'] = 'busy';
            break;
        }
    }
    
    set_flash('success', 'Agent successfully assigned to Order #' . $order_id);
    header('Location: agents.php');
    exit;
}

// Get active items
$current_agents = $_SESSION['agents_crud'];

// Search Filter
$search = $_GET['search'] ?? '';
if ($search !== '') {
    $current_agents = search_array($current_agents, $search, ['name', 'phone', 'vehicle', 'status']);
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = paginate($current_agents, $page, 6);
$paginated_agents = $pagination['data'];

// Determine if editing
$editing_agent = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $editing_agent = find_by_id($_SESSION['agents_crud'], $edit_id);
}

// Find unassigned orders to support assign workflow
$unassigned_orders = array_filter($orders, function($o) {
    // Check if assigned in dummy data or session
    $assigned_in_session = isset($_SESSION['assigned_agents'][$o['id']]);
    return $o['agent_id'] === null && !$assigned_in_session && $o['status'] !== 'cancelled' && $o['status'] !== 'delivered';
});
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Delivery Agents</h1>
        <p class="page-subtitle">Manage fleet and assign active deliveries</p>
    </div>
    <div class="btn-group">
        <button class="btn btn-secondary" onclick="toggleForm('assignFormCard')">
            🔗 Assign Agent
        </button>
        <button class="btn btn-primary" onclick="toggleForm('agentFormCard')">
            <?= icon('plus', 16) ?> Add Agent
        </button>
    </div>
</div>

<!-- Assign Agent Form Card -->
<div class="card mb-3" id="assignFormCard" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">🔗 Assign Agent to Order</h3>
        <button class="modal-close" onclick="toggleForm('assignFormCard')"><?= icon('close', 20) ?></button>
    </div>
    <form method="POST" action="agents.php" class="crud-form">
        <input type="hidden" name="assign_agent" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Select Active Order *</label>
                <select name="order_id" class="form-select" required>
                    <option value="">Choose Order</option>
                    <?php if (empty($unassigned_orders)): ?>
                        <option value="" disabled>No active unassigned orders</option>
                    <?php else: ?>
                        <?php foreach ($unassigned_orders as $ord): 
                            $c = find_by_id($customers, $ord['customer_id']);
                            $r = find_by_id($restaurants, $ord['restaurant_id']);
                        ?>
                            <option value="<?= $ord['id'] ?>">Order #<?= $ord['id'] ?> (From: <?= e($r['name']) ?> To: <?= e($c['name']) ?>) - <?= format_price($ord['total']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Select Available Agent *</label>
                <select name="agent_id" class="form-select" required>
                    <option value="">Choose Agent</option>
                    <?php 
                    $avail_agents = array_filter($_SESSION['agents_crud'], function($a) { return $a['status'] === 'available'; });
                    if (empty($avail_agents)): ?>
                        <option value="" disabled>No agents currently available</option>
                    <?php else: ?>
                        <?php foreach ($avail_agents as $ag): ?>
                            <option value="<?= $ag['id'] ?>"><?= e($ag['name']) ?> (<?= e($ag['vehicle']) ?>)</option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" <?= (empty($unassigned_orders) || empty($avail_agents)) ? 'disabled' : '' ?>>Assign and Save</button>
    </form>
</div>

<!-- Add/Edit Agent Form Card -->
<div class="card mb-3" id="agentFormCard" style="<?= ($editing_agent !== null) ? 'display: block;' : 'display: none;' ?>">
    <div class="card-header">
        <h3 class="card-title"><?= $editing_agent ? '✏️ Edit Agent: ' . e($editing_agent['name']) : '➕ Add New Delivery Agent' ?></h3>
        <button class="modal-close" onclick="toggleForm('agentFormCard')"><?= icon('close', 20) ?></button>
    </div>
    <form method="POST" action="agents.php" class="crud-form">
        <input type="hidden" name="id" value="<?= $editing_agent ? $editing_agent['id'] : '' ?>">
        <input type="hidden" name="save_agent" value="1">
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Agent Full Name *</label>
                <input type="text" name="name" class="form-input" required value="<?= $editing_agent ? e($editing_agent['name']) : '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-input" required value="<?= $editing_agent ? e($editing_agent['phone']) : '' ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Vehicle Type *</label>
                <select name="vehicle" class="form-select" required>
                    <option value="">Select Vehicle</option>
                    <option value="Motorcycle" <?= ($editing_agent && $editing_agent['vehicle'] === 'Motorcycle') ? 'selected' : '' ?>>🛵 Motorcycle</option>
                    <option value="Bicycle" <?= ($editing_agent && $editing_agent['vehicle'] === 'Bicycle') ? 'selected' : '' ?>>🚲 Bicycle</option>
                    <option value="Car" <?= ($editing_agent && $editing_agent['vehicle'] === 'Car') ? 'selected' : '' ?>>🚗 Car</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= ($editing_agent && $editing_agent['status'] === 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="busy" <?= ($editing_agent && $editing_agent['status'] === 'busy') ? 'selected' : '' ?>>Busy</option>
                </select>
            </div>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Save Agent</button>
            <button type="button" class="btn btn-secondary" onclick="toggleForm('agentFormCard')">Cancel</button>
        </div>
    </form>
</div>

<!-- Filter and Search Bar -->
<div class="filter-bar">
    <div class="search-box">
        <span class="search-icon"><?= icon('browse', 18) ?></span>
        <input type="text" class="form-input" placeholder="Search agents..." 
               value="<?= e($search) ?>" onkeyup="if(event.key === 'Enter') applyFilter('search', this.value)">
    </div>
    <?php if ($search !== ''): ?>
        <a href="agents.php" class="btn btn-secondary btn-sm">Clear Search</a>
    <?php endif; ?>
</div>

<!-- Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Agent ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Vehicle</th>
                <th>Rating</th>
                <th>Completed Trips</th>
                <th>Status</th>
                <th style="width: 140px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($paginated_agents)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-3">No agents registered.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($paginated_agents as $ag): ?>
                    <tr>
                        <td class="fw-600 text-accent">#<?= $ag['id'] ?></td>
                        <td class="fw-600"><?= e($ag['name']) ?></td>
                        <td><?= e($ag['phone']) ?></td>
                        <td>
                            <?php 
                            $v_icon = $ag['vehicle'] === 'Bicycle' ? '🚲' : ($ag['vehicle'] === 'Car' ? '🚗' : '🛵');
                            echo $v_icon . ' ' . e($ag['vehicle']);
                            ?>
                        </td>
                        <td><?= star_rating($ag['rating']) ?></td>
                        <td><?= $ag['deliveries_completed'] ?> deliveries</td>
                        <td><?= agent_status_badge($ag['status']) ?></td>
                        <td>
                            <div class="action-cell" style="justify-content: center;">
                                <a href="agents.php?action=edit&id=<?= $ag['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                    <?= icon('edit', 16) ?>
                                </a>
                                <a href="#" class="btn btn-danger btn-sm btn-icon" title="Delete" 
                                   data-delete="agents.php?action=delete&id=<?= $ag['id'] ?>" 
                                   data-name="<?= e($ag['name']) ?>">
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
