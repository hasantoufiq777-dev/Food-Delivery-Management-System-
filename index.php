<?php
/**
 * Landing / Login Portal
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/dummy_data.php';
require_once __DIR__ . '/includes/functions.php';

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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $selected_role = $_POST['role_select'] ?? '';

    if (empty($email) || empty($password) || empty($selected_role)) {
        $error = 'Please fill in all fields.';
    } else {
        $authenticated = false;
        
        if ($selected_role === 'admin') {
            // Static admin login check
            if ($email === 'admin@example.com' && $password === 'admin123') {
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = true;
                $authenticated = true;
            }
        } elseif ($selected_role === 'customer') {
            // Search customer in dummy data
            foreach ($customers as $c) {
                if ($c['email'] === $email && $c['password'] === $password) {
                    $_SESSION['role'] = 'customer';
                    $_SESSION['customer_id'] = $c['id'];
                    $authenticated = true;
                    break;
                }
            }
        } elseif ($selected_role === 'restaurant') {
            // Search restaurant in dummy data
            foreach ($restaurants as $r) {
                if ($r['email'] === $email && $r['password'] === $password) {
                    $_SESSION['role'] = 'restaurant';
                    $_SESSION['restaurant_id'] = $r['id'];
                    $authenticated = true;
                    break;
                }
            }
        } elseif ($selected_role === 'agent') {
            // Search agent in dummy data
            foreach ($agents as $a) {
                if ($a['email'] === $email && $a['password'] === $password) {
                    $_SESSION['role'] = 'agent';
                    $_SESSION['agent_id'] = $a['id'];
                    $authenticated = true;
                    break;
                }
            }
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
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        .login-layout {
            display: flex;
            min-height: 100vh;
            background: var(--bg-primary);
        }
        .login-side-brand {
            flex: 1.2;
            background: linear-gradient(135deg, #FF6B2B, #e55a1b);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .login-side-brand::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -50px;
            left: -50px;
        }
        .login-side-brand::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            bottom: -100px;
            right: -100px;
        }
        .brand-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1rem;
            font-family: var(--font-heading);
        }
        .brand-desc {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 440px;
        }
        .login-side-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-secondary);
        }
        .login-form-card {
            width: 100%;
            max-width: 420px;
        }
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
        @media (max-width: 992px) {
            .login-layout {
                flex-direction: column;
            }
            .login-side-brand {
                padding: 3rem 2rem;
                flex: none;
            }
            .brand-title {
                font-size: 2.2rem;
            }
            .login-side-form {
                padding: 3rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-layout">
        <!-- Left Banner -->
        <div class="login-side-brand">
            <span style="font-size: 3rem; margin-bottom: 1rem;">🔥</span>
            <h1 class="brand-title">Flame<br>Route</h1>
            <p class="brand-desc">Streamlining kitchen operations, dispatch routing, and premium food delivery logistics in one clean portal.</p>
        </div>
        
        <!-- Right Login Form -->
        <div class="login-side-form">
            <div class="login-form-card">
                <h2 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">Account Sign In</h2>
                <p class="text-secondary mb-3" style="font-size: 0.9rem;">Select your workspace portal to log in.</p>
                
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

                <!-- Guided Demo Credentials Hint Box -->
                <div class="guide-box" id="credentialsGuide">
                    <div class="guide-title">💡 Demo Customer Login</div>
                    <div>Email: <strong class="text-accent">alice@example.com</strong></div>
                    <div>Password: <strong class="text-accent">password</strong></div>
                </div>

                <!-- Main Login Form -->
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

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 0.8rem; justify-content: center; margin-top: 1rem; font-size: 0.95rem;">
                        Sign In to Dashboard
                    </button>
                </form>
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
            // Update active tab button style
            document.querySelectorAll('.portal-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector(`[data-portal="${portal}"]`).classList.add('active');

            // Set hidden field value
            document.getElementById('selectedPortal').value = portal;

            // Update email input label
            document.getElementById('emailLabel').textContent = demoCreds[portal].label;
            
            // Set input placeholder
            document.getElementById('emailInput').placeholder = demoCreds[portal].placeholder;

            // Update demo credentials guide box
            const guide = demoCreds[portal];
            document.getElementById('credentialsGuide').innerHTML = `
                <div class="guide-title">${guide.title}</div>
                <div>Email: <strong class="text-accent">${guide.email}</strong></div>
                <div>Password: <strong class="text-accent">${guide.pass}</strong></div>
            `;
        }
    </script>
</body>
</html>
