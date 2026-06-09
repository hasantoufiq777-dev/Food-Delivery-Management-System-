<?php
/**
 * Auto-detect base directory for localhost or subfolder deployments
 */
if (!defined('BASE_URL')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($script_dir === '/' || $script_dir === '\\' || $script_dir === '') {
        define('BASE_URL', '/');
    } else {
        $parts = explode('/', trim($script_dir, '/'));
        $last = end($parts);
        if (in_array($last, ['admin', 'restaurant', 'customer', 'agent', 'includes', 'config', 'assets'])) {
            array_pop($parts);
        }
        $base = implode('/', $parts);
        define('BASE_URL', $base ? '/' . $base . '/' : '/');
    }
}
/**
 * Helper Functions
 * Food Delivery Management System
 */

/**
 * Format price with currency symbol
 */
function format_price($price) {
    return '$' . number_format((float)$price, 2);
}

/**
 * Format date for display
 */
function format_date($datetime, $format = 'M d, Y') {
    return date($format, strtotime($datetime));
}

/**
 * Format date with time
 */
function format_datetime($datetime) {
    return date('M d, Y \a\t h:i A', strtotime($datetime));
}

/**
 * Format relative time (e.g., "2 hours ago")
 */
function time_ago($datetime) {
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return format_date($datetime);
}

/**
 * Generate status badge HTML with color coding
 */
function status_badge($status) {
    $classes = [
        'placed'           => 'badge-placed',
        'confirmed'        => 'badge-confirmed',
        'preparing'        => 'badge-preparing',
        'ready'            => 'badge-ready',
        'out_for_delivery'  => 'badge-transit',
        'delivered'        => 'badge-delivered',
        'cancelled'        => 'badge-cancelled',
    ];
    
    $labels = [
        'placed'           => 'Placed',
        'confirmed'        => 'Confirmed',
        'preparing'        => 'Preparing',
        'ready'            => 'Ready',
        'out_for_delivery'  => 'Out for Delivery',
        'delivered'        => 'Delivered',
        'cancelled'        => 'Cancelled',
    ];
    
    $class = $classes[$status] ?? 'badge-default';
    $label = $labels[$status] ?? ucfirst($status);
    
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Agent status badge
 */
function agent_status_badge($status) {
    $class = $status === 'available' ? 'badge-delivered' : 'badge-preparing';
    $label = ucfirst($status);
    return '<span class="badge ' . $class . '">' . $label . '</span>';
}

/**
 * Generate star rating HTML
 */
function star_rating($rating) {
    $html = '<span class="star-rating">';
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = 5 - $full - $half;
    
    for ($i = 0; $i < $full; $i++) {
        $html .= '<span class="star filled">★</span>';
    }
    if ($half) {
        $html .= '<span class="star half">★</span>';
    }
    for ($i = 0; $i < $empty; $i++) {
        $html .= '<span class="star empty">☆</span>';
    }
    $html .= ' <span class="rating-number">' . number_format($rating, 1) . '</span>';
    $html .= '</span>';
    return $html;
}

/**
 * Find item by ID in an array
 */
function find_by_id($array, $id) {
    foreach ($array as $item) {
        if ($item['id'] == $id) {
            return $item;
        }
    }
    return null;
}

/**
 * Filter array by key value
 */
function filter_by($array, $key, $value) {
    return array_filter($array, function($item) use ($key, $value) {
        return isset($item[$key]) && $item[$key] == $value;
    });
}

/**
 * Search array by multiple keys
 */
function search_array($array, $query, $keys) {
    if (empty($query)) return $array;
    $query = strtolower(trim($query));
    return array_filter($array, function($item) use ($query, $keys) {
        foreach ($keys as $key) {
            if (isset($item[$key]) && strpos(strtolower((string)$item[$key]), $query) !== false) {
                return true;
            }
        }
        return false;
    });
}

/**
 * Paginate an array
 */
function paginate($array, $page = 1, $per_page = 8) {
    $total = count($array);
    $total_pages = max(1, ceil($total / $per_page));
    $page = max(1, min($page, $total_pages));
    $offset = ($page - 1) * $per_page;
    
    return [
        'data' => array_slice(array_values($array), $offset, $per_page),
        'current_page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages,
    ];
}

/**
 * Generate pagination HTML
 */
function pagination_html($pagination, $base_url = '?') {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($pagination['has_prev']) {
        $html .= '<a href="' . $base_url . 'page=' . ($pagination['current_page'] - 1) . '" class="page-btn">';
        $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>';
        $html .= '</a>';
    } else {
        $html .= '<span class="page-btn disabled">';
        $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>';
        $html .= '</span>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = $i === $pagination['current_page'] ? ' active' : '';
        $html .= '<a href="' . $base_url . 'page=' . $i . '" class="page-btn' . $active . '">' . $i . '</a>';
    }
    
    // Next button
    if ($pagination['has_next']) {
        $html .= '<a href="' . $base_url . 'page=' . ($pagination['current_page'] + 1) . '" class="page-btn">';
        $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        $html .= '</a>';
    } else {
        $html .= '<span class="page-btn disabled">';
        $html .= '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>';
        $html .= '</span>';
    }
    
    $html .= '<span class="page-info">Page ' . $pagination['current_page'] . ' of ' . $pagination['total_pages'] . '</span>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Set flash message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function flash_html() {
    $flash = get_flash();
    if (!$flash) return '';
    
    $icon = $flash['type'] === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    
    return '<div class="flash-message flash-' . $flash['type'] . '" id="flashMessage">'
         . $icon . '<span>' . htmlspecialchars($flash['message']) . '</span>'
         . '<button class="flash-close" onclick="this.parentElement.remove()">×</button>'
         . '</div>';
}

/**
 * Get current role from session
 */
function get_current_role() {
    return $_SESSION['role'] ?? 'customer';
}

/**
 * Get current user ID based on role
 */
function get_current_user_id() {
    $role = get_current_role();
    switch ($role) {
        case 'admin': return 0;
        case 'restaurant': return $_SESSION['restaurant_id'] ?? 1;
        case 'customer': return $_SESSION['customer_id'] ?? 1;
        case 'agent': return $_SESSION['agent_id'] ?? 1;
        default: return 0;
    }
}

/**
 * Get base URL based on role
 */
function get_role_base_url() {
    $role = get_current_role();
    switch ($role) {
        case 'admin': return BASE_URL . 'admin/';
        case 'restaurant': return BASE_URL . 'restaurant/';
        case 'customer': return BASE_URL . 'customer/';
        case 'agent': return BASE_URL . 'agent/';
        default: return BASE_URL;
    }
}

/**
 * Safely output HTML-escaped text
 */
function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

/**
 * Get count of orders by status
 */
function count_by_status($orders, $status) {
    return count(array_filter($orders, function($o) use ($status) {
        return $o['status'] === $status;
    }));
}

/**
 * Calculate total revenue from orders
 */
function total_revenue($orders) {
    return array_sum(array_map(function($o) {
        return ($o['status'] !== 'cancelled') ? $o['total'] : 0;
    }, $orders));
}

/**
 * Get orders for a specific restaurant
 */
function get_restaurant_orders($orders, $restaurant_id) {
    return array_filter($orders, function($o) use ($restaurant_id) {
        return $o['restaurant_id'] == $restaurant_id;
    });
}

/**
 * Get orders for a specific customer
 */
function get_customer_orders($orders, $customer_id) {
    return array_filter($orders, function($o) use ($customer_id) {
        return $o['customer_id'] == $customer_id;
    });
}

/**
 * Get orders for a specific agent
 */
function get_agent_orders($orders, $agent_id) {
    return array_filter($orders, function($o) use ($agent_id) {
        return $o['agent_id'] == $agent_id;
    });
}

/**
 * Get top restaurants by order count
 */
function get_top_restaurants($orders, $restaurants, $limit = 5) {
    $counts = [];
    foreach ($orders as $order) {
        if ($order['status'] !== 'cancelled') {
            $rid = $order['restaurant_id'];
            $counts[$rid] = ($counts[$rid] ?? 0) + 1;
        }
    }
    arsort($counts);
    
    $result = [];
    $i = 0;
    foreach ($counts as $rid => $count) {
        if ($i >= $limit) break;
        $rest = find_by_id($restaurants, $rid);
        if ($rest) {
            $rest['order_count'] = $count;
            $result[] = $rest;
        }
        $i++;
    }
    return $result;
}

/**
 * Generate order timeline HTML
 */
function order_timeline($current_status) {
    $steps = [
        'placed'           => ['label' => 'Order Placed',       'icon' => '📋'],
        'confirmed'        => ['label' => 'Confirmed',          'icon' => '✅'],
        'preparing'        => ['label' => 'Preparing',          'icon' => '👨‍🍳'],
        'ready'            => ['label' => 'Ready for Pickup',   'icon' => '📦'],
        'out_for_delivery'  => ['label' => 'Out for Delivery',  'icon' => '🚗'],
        'delivered'        => ['label' => 'Delivered',           'icon' => '🎉'],
    ];
    
    if ($current_status === 'cancelled') {
        return '<div class="order-timeline"><div class="timeline-step cancelled"><span class="timeline-icon">❌</span><span class="timeline-label">Order Cancelled</span></div></div>';
    }
    
    $html = '<div class="order-timeline">';
    $reached = true;
    foreach ($steps as $status => $info) {
        $class = 'timeline-step';
        if ($status === $current_status) {
            $class .= ' active';
        } elseif ($reached) {
            $class .= ' completed';
        } else {
            $class .= ' pending';
        }
        
        if ($status === $current_status) {
            $reached = false;
        }
        
        $html .= '<div class="' . $class . '">';
        $html .= '<span class="timeline-icon">' . $info['icon'] . '</span>';
        $html .= '<span class="timeline-label">' . $info['label'] . '</span>';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * Get SVG icons used throughout the app
 */
function icon($name, $size = 20) {
    $icons = [
        'dashboard' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        'orders' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>',
        'restaurant' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8h1a4 4 0 0 1 0 8h-1"></path><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path><line x1="6" y1="1" x2="6" y2="4"></line><line x1="10" y1="1" x2="10" y2="4"></line><line x1="14" y1="1" x2="14" y2="4"></line></svg>',
        'customers' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'agents' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>',
        'menu' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>',
        'cart' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>',
        'browse' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
        'profile' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'checkout' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
        'revenue' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>',
        'trending' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>',
        'truck' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>',
        'edit' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        'delete' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>',
        'plus' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
        'eye' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>',
        'logout' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
        'phone' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>',
        'location' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>',
        'clock' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'filter' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>',
        'close' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>',
        'hamburger' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
        'update' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>',
        'email' => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
    ];
    
    return $icons[$name] ?? '';
}

/**
 * Build query string preserving existing params
 */
function build_query($params = [], $exclude = []) {
    $current = $_GET;
    foreach ($exclude as $key) {
        unset($current[$key]);
    }
    return '?' . http_build_query(array_merge($current, $params));
}

/**
 * Get order items total count
 */
function order_items_count($order) {
    $count = 0;
    foreach ($order['items'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}
