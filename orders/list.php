<?php
$page_title = 'Orders Management';
require_once '../includes/header.php';

// --- LOGIKA DATA ---
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($status_filter) {
    $where .= " AND o.status = :status";
    $params['status'] = $status_filter;
}

if ($search) {
    $where .= " AND (o.order_number LIKE :search OR c.name LIKE :search)";
    $params['search'] = "%$search%";
}

$orders = fetchAll("
    SELECT o.*, c.name as customer_name, c.phone, ch.name as channel_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN channels ch ON o.channel_id = ch.id
    $where
    ORDER BY o.created_at DESC
    LIMIT 50
", $params);

// Ambil Statistik
$stats_data = [
    'pending'    => ['count' => fetchOne("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'")['total'], 'icon' => 'fa-clock', 'color' => 'warning'],
    'processing' => ['count' => fetchOne("SELECT COUNT(*) as total FROM orders WHERE status = 'processing'")['total'], 'icon' => 'fa-spinner', 'color' => 'info'],
    'completed'  => ['count' => fetchOne("SELECT COUNT(*) as total FROM orders WHERE status = 'completed'")['total'], 'icon' => 'fa-check-circle', 'color' => 'success'],
    'cancelled'  => ['count' => fetchOne("SELECT COUNT(*) as total FROM orders WHERE status = 'cancelled'")['total'], 'icon' => 'fa-times-circle', 'color' => 'danger']
];
?>

<style>
    /* Dashboard & Order Styling */
    :root {
        --primary: #4361ee;
        --success: #2ec4b6;
        --warning: #ff9f1c;
        --danger: #e71d36;
        --info: #0dcaf0;
        --bg-body: #f8fafc;
        --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .order-page-container { padding: 20px; background: var(--bg-body); min-height: 100vh; font-family: 'Inter', sans-serif; }
    
    /* Header & Breadcrumb */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .page-title { font-size: 1.6rem; font-weight: 700; color: #1e293b; margin: 0; }
    .breadcrumb { color: #64748b; font-size: 0.9rem; }

    /* Stats Grid */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { 
        background: #fff; padding: 20px; border-radius: 16px; box-shadow: var(--card-shadow); 
        display: flex; align-items: center; transition: 0.3s; border: 1px solid #f1f5f9;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-right: 15px; }
    
    .stat-icon.warning { background: rgba(255, 159, 28, 0.1); color: var(--warning); }
    .stat-icon.info { background: rgba(13, 202, 240, 0.1); color: var(--info); }
    .stat-icon.success { background: rgba(46, 196, 182, 0.1); color: var(--success); }
    .stat-icon.danger { background: rgba(231, 29, 54, 0.1); color: var(--danger); }

    .stat-details h3 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #1e293b; }
    .stat-details p { margin: 0; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Main Table Card */
    .card { background: #fff; border-radius: 16px; box-shadow: var(--card-shadow); border: 1px solid #f1f5f9; overflow: hidden; }
    .card-header { padding: 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .card-title { font-size: 1.1rem; font-weight: 600; color: #1e293b; margin: 0; }

    /* Filters & Inputs */
    .filter-group { display: flex; gap: 10px; align-items: center; }
    .form-control { padding: 0.6rem 1rem; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; transition: 0.2s; }
    .form-control:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1); }
    
    /* Buttons */
    .btn { padding: 0.6rem 1.2rem; border-radius: 10px; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; border: none; transition: 0.2s; text-decoration: none; }
    .btn-primary { background: var(--primary); color: white; }
    .btn-success { background: var(--success); color: white; }
    .btn-danger { background: rgba(231, 29, 54, 0.1); color: var(--danger); }
    .btn-danger:hover { background: var(--danger); color: white; }
    .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }

    /* Table Styling */
    .table-container { width: 100%; overflow-x: auto; }
    .table { width: 100%; border-collapse: collapse; }
    .table th { background: #f8fafc; padding: 12px 20px; text-align: left; font-size: 0.75rem; text-transform: uppercase; color: #64748b; font-weight: 600; }
    .table td { padding: 15px 20px; border-top: 1px solid #f1f5f9; font-size: 0.9rem; color: #334155; vertical-align: middle; }
    .table tr:hover { background: #fcfdfe; }

    /* Badges */
    .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-processing { background: #e0f2fe; color: #075985; }
    .badge-completed { background: #dcfce7; color: #166534; }
    .badge-cancelled { background: #fee2e2; color: #991b1b; }
    .badge-paid { background: #dcfce7; color: #166534; }
    .badge-unpaid { background: #f1f5f9; color: #475569; }

    .customer-info div { line-height: 1.4; }
    .customer-phone { font-size: 0.8rem; color: #94a3b8; }
</style>

<div class="order-page-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Orders Management</h1>
            <div class="breadcrumb">Home / Orders / List</div>
        </div>
        <a href="add.php" class="btn btn-success">
            <i class="fas fa-plus"></i> New Order
        </a>
    </div>

    <div class="stats-grid">
        <?php foreach ($stats_data as $label => $data): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $data['color'] ?>">
                <i class="fas <?= $data['icon'] ?>"></i>
            </div>
            <div class="stat-details">
                <h3><?= number_format($data['count']) ?></h3>
                <p><?= $label ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">All Orders</h2>
            
            <form method="GET" class="filter-group">
                <input type="text" name="search" class="form-control" placeholder="Search Order # or Customer..." value="<?= htmlspecialchars($search) ?>" style="width: 250px;">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="completed" <?= $status_filter == 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($search || $status_filter): ?>
                    <a href="list.php" class="btn btn-danger btn-sm" title="Clear Filter">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Channel</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 50px;">
                            <i class="fas fa-box-open" style="font-size: 3rem; color: #e2e8f0; margin-bottom: 15px; display: block;"></i>
                            <span style="color: #94a3b8;">No order found matching your criteria.</span>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><span style="font-weight: 600; color: var(--primary);">#<?= $order['order_number'] ?></span></td>
                            <td class="customer-info">
                                <div><?= htmlspecialchars($order['customer_name'] ?? 'Guest') ?></div>
                                <div class="customer-phone"><?= $order['phone'] ?? '-' ?></div>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: #64748b;">
                                    <i class="fas fa-bullseye" style="font-size: 0.7rem;"></i> <?= $order['channel_name'] ?? 'Direct' ?>
                                </span>
                            </td>
                            <td><strong style="color: #1e293b;"><?= formatCurrency($order['total_amount']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= strtolower($order['status']) ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $order['payment_status'] == 'paid' ? 'badge-completed' : 'badge-unpaid' ?>">
                                    <i class="fas <?= $order['payment_status'] == 'paid' ? 'fa-check' : 'fa-clock' ?>" style="font-size: 0.7rem;"></i> 
                                    <?= ucfirst($order['payment_status'] ?? 'Unpaid') ?>
                                </span>
                            </td>
                            <td style="color: #64748b; font-size: 0.85rem;">
                                <?= date('d M Y', strtotime($order['created_at'])) ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 5px; justify-content: flex-end;">
                                    <a href="edit.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary" title="View/Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($order['status'] !== 'cancelled'): ?>
                                    <a href="delete.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')" title="Cancel Order">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>