<?php
$page_title = 'Customers';
require_once '../includes/header.php';

$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Build query
$where = '';
$params = [];
if ($search) {
    $where = "WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search";
    $params['search'] = "%$search%";
}

// Get total count
$total_sql = "SELECT COUNT(*) as total FROM customers $where";
$total = fetchOne($total_sql, $params)['total'];

// Get customers
$sql = "SELECT * FROM customers $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$customers = fetchAll($sql, $params);

$pagination = paginate($total, $per_page, $page);
?>

<div class="page-header">
    <h1 class="page-title">Customers</h1>
    <div class="breadcrumb">
        <span>Home</span> / <span>Customers</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Customer List</h2>
        <div style="display: flex; gap: 1rem;">
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?= $search ?>" style="width: 300px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Customer
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?= $customer['id'] ?></td>
                    <td><strong><?= $customer['name'] ?></strong></td>
                    <td><?= $customer['email'] ?? '-' ?></td>
                    <td><?= $customer['phone'] ?? '-' ?></td>
                    <td><?= $customer['city'] ?? '-' ?></td>
                    <td><?= formatDate($customer['created_at']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?= $customer['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-inbox" style="font-size: 3rem; color: var(--text-light);"></i>
                        <p style="margin-top: 1rem; color: var(--text-light);">No customers found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($pagination['total_pages'] > 1): ?>
    <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; padding: 1.5rem;">
        <?php if ($pagination['has_prev']): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= $search ?>" class="btn btn-sm btn-secondary">Previous</a>
        <?php endif; ?>
        
        <span>Page <?= $page ?> of <?= $pagination['total_pages'] ?></span>
        
        <?php if ($pagination['has_next']): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= $search ?>" class="btn btn-sm btn-secondary">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>