<?php
/**
 * Landing / Login Portal
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/dummy_data.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $conn = get_db_connection_pdo();
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    session_start();
    set_flash('success', 'Logged out successfully.');
    header('Location: index.php');
    exit;
}

// Handle Role Switch (Admin simulating other roles)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['switch_role'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        $_SESSION['role'] = $_POST['role'];
        set_flash('success', 'Switched simulated role to: ' . ucfirst($_SESSION['role']));
        if ($_SESSION['role'] === 'customer') {
            header('Location: customer/browse.php');
        } else {
            header('Location: ' . $_SESSION['role'] . '/dashboard.php');
        }
        exit;
    }
}

// Handle Login Submit
$error = '';
$authenticated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $selected_role = $_POST['role_select'] ?? '';

    if (empty($email) || empty($password) || empty($selected_role)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT user_id, name, email, password_hash, role FROM users WHERE LOWER(email) = LOWER(:email) AND role = :role");
            $stmt->execute([
                'email' => $email,
                'role' => $selected_role
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['PASSWORD_HASH'] === $password) {
                $_SESSION['role'] = $selected_role;
                
                if ($selected_role === 'admin') {
                    $_SESSION['is_admin'] = true;
                } elseif ($selected_role === 'customer') {
                    $_SESSION['customer_id'] = (int)$user['USER_ID'];
                } elseif ($selected_role === 'restaurant') {
                    $r_stmt = $conn->prepare("SELECT restaurant_id FROM restaurants WHERE user_id = :user_id");
                    $r_stmt->execute(['user_id' => $user['USER_ID']]);
                    $r_row = $r_stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['restaurant_id'] = (int)$r_row['RESTAURANT_ID'];
                } elseif ($selected_role === 'agent') {
                    $a_stmt = $conn->prepare("SELECT agent_id FROM delivery_agents WHERE user_id = :user_id");
                    $a_stmt->execute(['user_id' => $user['USER_ID']]);
                    $a_row = $a_stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['agent_id'] = (int)$a_row['AGENT_ID'];
                }
                
                $authenticated = true;
            } else {
                $error = 'Invalid email, password, or role.';
            }
        } catch (PDOException $ex) {
            $error = 'Database Error: ' . $ex->getMessage();
        }

        if ($authenticated) {
            set_flash('success', 'Logged in as ' . ucfirst($_SESSION['role']) . ' successfully.');
            
            // Redirect
            if ($_SESSION['role'] === 'customer') {
                header('Location: customer/browse.php');
            } else {
                header('Location: ' . $_SESSION['role'] . '/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email, password, or incorrect portal selected.';
        }
    }
}

// Handle Registration Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    
    if (empty($name) || empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all registration fields.';
    } else {
        try {
            // Get next user_id
            $stmt = $conn->prepare("SELECT NVL(MAX(user_id), 0) + 1 FROM users");
            $stmt->execute();
            $new_user_id = $stmt->fetchColumn();
            
            // Call stored procedure to register user (role is always 'customer') using positional parameters
            $stmt = $conn->prepare("CALL register_user(?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $new_user_id,
                $name,
                $email,
                $password,
                $phone,
                'customer',
                null
            ]);
            
            set_flash('success', 'Registration successful! You can now log in.');
            header('Location: index.php');
            exit;
        } catch (PDOException $ex) {
            $error = 'Registration Failed: ' . $ex->getMessage();
        }
    }
}

// Redirect if already logged in
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'customer') {
        header('Location: customer/browse.php');
    } else {
        header('Location: ' . $_SESSION['role'] . '/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FlameRoute - Premium Food Delivery Management System">
    <title>FlameRoute — Login Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .portal-tabs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.35rem;
            margin-bottom: 1.5rem;
            background: var(--bg-tertiary);
            padding: 0.25rem;
            border-radius: var(--border-radius-sm);
        }
        .portal-tab-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            padding: 0.5rem 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: var(--border-radius-xs);
            cursor: pointer;
            transition: var(--transition-fast);
            text-align: center;
        }
        .portal-tab-btn.active {
            background: var(--bg-secondary);
            color: var(--accent);
            box-shadow: var(--shadow-sm);
        }
        .guide-box {
            background: var(--bg-tertiary);
            border: 1px dashed var(--border-hover);
            border-radius: var(--border-radius-sm);
            padding: 0.85rem;
            margin-bottom: 1.5rem;
            font-size: 0.78rem;
            color: var(--text-secondary);
        }
        .guide-title {
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-split-container">
        <!-- Left Banner -->
        <div class="login-split-hero">
            <span style="font-size: 3rem; margin-bottom: 1.5rem; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));">🔥</span>
            <h1 class="login-hero-title">FlameRoute</h1>
            <p class="login-hero-desc">Streamlining kitchen operations, dispatch routing, and premium food delivery logistics in one clean, database-backed portal.</p>
        </div>
        
        <!-- Right Login Form -->
        <div class="login-split-form">
            <div class="login-form-card">
                <h2 id="formTitle" style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem; font-family: var(--font-heading);">Account Sign In</h2>
                <p id="formSubtitle" class="text-secondary mb-3" style="font-size: 0.9rem;">Select your workspace portal to log in.</p>
                
                <?= flash_html() ?>
                
                <?php if ($error): ?>
                    <div class="flash-message flash-error">
                        <span><?= e($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Portal selection tabs -->
                <div class="portal-tabs">
                    <button type="button" class="portal-tab-btn active" data-portal="customer" onclick="switchPortal('customer')">👤 Customer</button>
                    <button type="button" class="portal-tab-btn" data-portal="restaurant" onclick="switchPortal('restaurant')">🍽️ Shop</button>
                    <button type="button" class="portal-tab-btn" data-portal="agent" onclick="switchPortal('agent')">🛵 Courier</button>
                    <button type="button" class="portal-tab-btn" data-portal="admin" onclick="switchPortal('admin')">🛡️ Admin</button>
                </div>

                <!-- Login Form Wrapper -->
                <div id="loginFormContainer">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="login" value="1">
                        <input type="hidden" name="role_select" id="selectedPortal" value="customer">

                        <div class="form-group">
                            <label class="form-label" id="emailLabel">Customer Email Address</label>
                            <input type="email" name="email" id="emailInput" class="form-input" required placeholder="name@example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-input" required placeholder="••••••••">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; justify-content: center; margin-top: 1.5rem; font-size: 0.95rem;">
                            Sign In to Dashboard
                        </button>
                    </form>
                    <div class="text-center" style="font-size: 0.88rem; margin-top: 1.5rem;">
                        Don't have an account? <a href="#" onclick="showRegisterForm()" style="color: var(--accent); font-weight: 600;">Register here</a>
                    </div>
                </div>

                <!-- Register Form Wrapper -->
                <div id="registerFormContainer" style="display: none;">
                    <form method="POST" action="index.php">
                        <input type="hidden" name="register" value="1">

                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="name" class="form-input" required placeholder="John Doe">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-input" required placeholder="john@example.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-input" required placeholder="••••••••">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Contact *</label>
                            <input type="text" name="phone" class="form-input" required placeholder="+1-555-0199">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; justify-content: center; margin-top: 1.5rem; font-size: 0.95rem;">
                            Create Customer Account
                        </button>
                    </form>
                    <div class="text-center" style="font-size: 0.88rem; margin-top: 1.5rem;">
                        Already have an account? <a href="#" onclick="showLoginForm()" style="color: var(--accent); font-weight: 600;">Sign in here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const demoCreds = {
            customer: {
                title: "💡 Demo Customer Login",
                email: "alice@example.com",
                pass: "password",
                label: "Customer Email Address",
                placeholder: "alice@example.com"
            },
            restaurant: {
                title: "💡 Demo Restaurant Manager Login",
                email: "spicegarden@example.com",
                pass: "password",
                label: "Restaurant Manager Email",
                placeholder: "spicegarden@example.com"
            },
            agent: {
                title: "💡 Demo Delivery Agent Login",
                email: "marcus@example.com",
                pass: "password",
                label: "Courier Email Address",
                placeholder: "marcus@example.com"
            },
            admin: {
                title: "💡 Demo Administrator Login",
                email: "admin@example.com",
                pass: "admin123",
                label: "Administrator Email",
                placeholder: "admin@example.com"
            }
        };

        function switchPortal(portal) {
            document.querySelectorAll('.portal-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-portal="${portal}"]`).classList.add('active');

            document.getElementById('selectedPortal').value = portal;
            document.getElementById('emailLabel').textContent = demoCreds[portal].label;
            document.getElementById('emailInput').placeholder = demoCreds[portal].placeholder;
        }

        function showRegisterForm() {
            document.getElementById('loginFormContainer').style.display = 'none';
            document.getElementById('registerFormContainer').style.display = 'block';
            document.querySelector('.portal-tabs').style.display = 'none';
            document.getElementById('formTitle').textContent = 'Account Registration';
            document.getElementById('formSubtitle').textContent = 'Create a customer account to order food.';
        }

        function showLoginForm() {
            document.getElementById('loginFormContainer').style.display = 'block';
            document.getElementById('registerFormContainer').style.display = 'none';
            document.querySelector('.portal-tabs').style.display = 'grid';
            document.getElementById('formTitle').textContent = 'Account Sign In';
            document.getElementById('formSubtitle').textContent = 'Select your workspace portal to log in.';
        }
    </script>
</body>
</html>
