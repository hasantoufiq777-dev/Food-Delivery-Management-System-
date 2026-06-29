<?php
/**
 * Admin Dashboard
 * Food Delivery Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/header.php';

// Prepare metrics
$total_orders_count = count($orders);
$total_revenue = total_revenue($orders);
$active_agents = count(array_filter($agents, function($a) { return $a['status'] === 'available'; }));
$top_restaurants = get_top_restaurants($orders, $restaurants, 5);

// Count of status for charts
$placed_count = count_by_status($orders, 'placed');
$confirmed_count = count_by_status($orders, 'confirmed');
$preparing_count = count_by_status($orders, 'preparing');
$ready_count = count_by_status($orders, 'ready');
$transit_count = count_by_status($orders, 'out_for_delivery');
$delivered_count = count_by_status($orders, 'delivered');
$cancelled_count = count_by_status($orders, 'cancelled');

// Daily order counts for line chart (last 7 days simulation)
$daily_orders = [
    'Mon' => 12,
    'Tue' => 15,
    'Wed' => 8,
    'Thu' => 19,
    'Fri' => 22,
    'Sat' => 30,
    'Sun' => 25
];
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Platform Analytics & Management Overview</p>
    </div>
    <div>
        <a href="?clear_orders=true" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure you want to clear all simulated orders?');">Clear Simulated Orders</a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon orange">
            <?= icon('orders', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value"><?= $total_orders_count ?></div>
            <div class="stat-change up">★ Active platform orders</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <?= icon('revenue', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value"><?= format_price($total_revenue) ?></div>
            <div class="stat-change up">▲ +12.5% this week</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon blue">
            <?= icon('agents', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Active Agents</div>
            <div class="stat-value"><?= $active_agents ?> / <?= count($agents) ?></div>
            <div class="stat-change">🟢 Available to deliver</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <?= icon('restaurant', 24) ?>
        </div>
        <div class="stat-info">
            <div class="stat-label">Restaurants</div>
            <div class="stat-value"><?= count($restaurants) ?></div>
            <div class="stat-change">✨ Operational partners</div>
        </div>
    </div>
</div>

<!-- Charts Grid -->
<div class="charts-grid">
    <div class="chart-card">
        <h3 class="section-title">Orders by Status</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="chart-card">
        <h3 class="section-title">Weekly Order Volume</h3>
        <canvas id="volumeChart"></canvas>
    </div>
</div>

<div class="two-col">
    <!-- Recent Orders Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
            <a href="<?= BASE_URL ?>admin/orders.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Restaurant</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Show last 5 orders
                    $recent_orders = array_slice($orders, 0, 5);
                    foreach ($recent_orders as $order): 
                        $cust = find_by_id($customers, $order['customer_id']);
                        $rest = find_by_id($restaurants, $order['restaurant_id']);
                    ?>
                        <tr>
                            <td class="fw-600 text-accent">#<?= $order['id'] ?></td>
                            <td><?= e($cust ? $cust['name'] : 'Unknown') ?></td>
                            <td><?= e($rest ? $rest['name'] : 'Unknown') ?></td>
                            <td class="fw-700"><?= format_price($order['total']) ?></td>
                            <td><?= status_badge($order['status']) ?></td>
                            <td class="text-muted"><?= format_date($order['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Restaurants -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Top Restaurants</h3>
        </div>
        <div class="top-list">
            <?php 
            $rank = 1;
            foreach ($top_restaurants as $rest): 
            ?>
                <div class="top-item">
                    <span class="top-rank"><?= $rank++ ?></span>
                    <div class="top-name"><?= e($rest['name']) ?></div>
                    <div class="top-count"><?= $rest['order_count'] ?> Orders</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Load Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Orders by Status Chart (Doughnut)
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Placed', 'Confirmed', 'Preparing', 'Ready', 'In Transit', 'Delivered', 'Cancelled'],
            datasets: [{
                data: [
                    <?= $placed_count ?>,
                    <?= $confirmed_count ?>,
                    <?= $preparing_count ?>,
                    <?= $ready_count ?>,
                    <?= $transit_count ?>,
                    <?= $delivered_count ?>,
                    <?= $cancelled_count ?>
                ],
                backgroundColor: [
                    '#45B7D1',
                    '#6C63FF',
                    '#FF9F43',
                    '#A29BFE',
                    '#F368E0',
                    '#26de81',
                    '#FC5C65'
                ],
                borderWidth: 0
            }]
        },
                options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#475569',
                        font: { family: 'DM Sans' }
                    }
                }
            }
        }
    });

    // Weekly Order Volume Chart (Line)
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($daily_orders)) ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode(array_values($daily_orders)) ?>,
                borderColor: '#FF6B2B',
                backgroundColor: 'rgba(255, 107, 43, 0.05)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: '#e2e8f0' },
                    ticks: { color: '#475569' }
                },
                y: {
                    grid: { color: '#e2e8f0' },
                    ticks: { color: '#475569', stepSize: 5 }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
