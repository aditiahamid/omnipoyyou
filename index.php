<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

/** * Data Fetching Logic 
 */
$total_customers = fetchOne("SELECT COUNT(*) as total FROM customers")['total'];
$total_orders = fetchOne("SELECT COUNT(*) as total FROM orders")['total'];
$total_products = fetchOne("SELECT COUNT(*) as total FROM products")['total'];
$total_messages = fetchOne("SELECT COUNT(*) as total FROM messages")['total'];

$recent_orders = fetchAll("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");

$recent_messages = fetchAll("
    SELECT m.*, c.name as customer_name, ch.name as channel_name, ch.type as channel_type
    FROM messages m 
    LEFT JOIN customers c ON m.customer_id = c.id 
    LEFT JOIN channels ch ON m.channel_id = ch.id 
    ORDER BY m.created_at DESC 
    LIMIT 5
");

$channels = fetchAll("SELECT * FROM channels WHERE status = 'active'");
?>

<style>
    /* Dashboard Specific Styles */
    :root {
        --primary-soft: rgba(67, 97, 238, 0.1);
        --success-soft: rgba(46, 196, 182, 0.1);
        --warning-soft: rgba(255, 159, 28, 0.1);
        --danger-soft: rgba(231, 29, 54, 0.1);
        --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.07);
    }

    .dashboard-container { padding: 1.5rem; background: #f9fafb; min-height: 100vh; }
    
    .page-header { margin-bottom: 2rem; }
    .page-title { font-size: 1.5rem; font-weight: 700; color: #111827; margin-bottom: 0.25rem; }
    .breadcrumb { color: #6b7280; font-size: 0.875rem; }

    /* Stats Cards */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .stat-card { 
        background: #fff; padding: 1.5rem; border-radius: 16px; border: 1px solid #f3f4f6; 
        box-shadow: var(--card-shadow); display: flex; align-items: center; transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon { width: 56px; height: 56px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-right: 1rem; }
    
    .stat-icon.primary { background: var(--primary-soft); color: #4361ee; }
    .stat-icon.success { background: var(--success-soft); color: #2ec4b6; }
    .stat-icon.warning { background: var(--warning-soft); color: #ff9f1c; }
    .stat-icon.danger { background: var(--danger-soft); color: #e71d36; }

    .stat-details h3 { font-size: 1.5rem; font-weight: 700; margin: 0; color: #111827; }
    .stat-details p { color: #6b7280; margin: 0; font-size: 0.875rem; }

    /* Cards & Layout */
    .card { background: #fff; border-radius: 16px; border: 1px solid #f3f4f6; box-shadow: var(--card-shadow); margin-bottom: 1.5rem; }
    .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
    .card-title { font-size: 1.125rem; font-weight: 600; color: #111827; margin: 0; }

    /* Channels */
    .channels-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; padding: 1.5rem; }
    .channel-item { 
        padding: 1.25rem; background: #fbfcfe; border-radius: 12px; text-align: center; border: 1px solid #f1f5f9;
        transition: all 0.2s;
    }
    .channel-item:hover { border-color: #4361ee; background: #fff; }

    /* Tables */
    .table-container { overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { background: #f9fafb; padding: 0.75rem 1.5rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #6b7280; }
    .table td { padding: 1rem 1.5rem; border-top: 1px solid #f3f4f6; font-size: 0.875rem; color: #374151; }
    
    .btn-sm { padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.875rem; text-decoration: none; }
    .btn-primary { background: #4361ee; color: #fff; }
</style>

<div class="dashboard-container">
    <div class="page-header">
        <h1 class="page-title">Dashboard Overview</h1>
        <div class="breadcrumb">Home / Dashboard</div>
    </div>

    <div class="stats-grid">
        <?php
        $stats = [
            ['title' => 'Customers', 'val' => $total_customers, 'icon' => 'fa-users', 'class' => 'primary'],
            ['title' => 'Orders', 'val' => $total_orders, 'icon' => 'fa-shopping-cart', 'class' => 'success'],
            ['title' => 'Products', 'val' => $total_products, 'icon' => 'fa-box', 'class' => 'warning'],
            ['title' => 'Messages', 'val' => $total_messages, 'icon' => 'fa-envelope', 'class' => 'danger'],
        ];
        foreach ($stats as $s): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $s['class'] ?>"><i class="fas <?= $s['icon'] ?>"></i></div>
            <div class="stat-details">
                <h3><?= number_format($s['val']) ?></h3>
                <p><?= $s['title'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Active Channels</h2>
            <a href="channels/list.php" class="btn-sm btn-primary"><i class="fas fa-cog"></i> Manage</a>
        </div>
        <div class="channels-grid">
            <?php foreach ($channels as $channel): ?>
            <div class="channel-item">
                <div style="font-size: 2rem; margin-bottom: 0.5rem; color: #4361ee;">
                    <?= getChannelIcon($channel['type']) ?>
                </div>
                <h4 style="margin:0; font-size: 1rem;"><?= $channel['name'] ?></h4>
                <span class="badge badge-success" style="font-size: 0.7rem; opacity: 0.8;">Active</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Orders</h2>
            <a href="orders/list.php" class="btn-sm btn-primary">View All</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['order_number'] ?></strong></td>
                        <td><?= $order['customer_name'] ?? 'N/A' ?></td>
                        <td><?= formatCurrency($order['total_amount']) ?></td>
                        <td><?= getStatusBadge($order['status']) ?></td>
                        <td>
                            <a href="orders/edit.php?id=<?= $order['id'] ?>" class="btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>