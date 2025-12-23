<?php
$page_title = 'Products';
require_once '../includes/header.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (name LIKE :search OR sku LIKE :search)";
    $params['search'] = "%$search%";
}

if ($category) {
    $where .= " AND category = :category";
    $params['category'] = $category;
}

$products = fetchAll("SELECT * FROM products $where ORDER BY created_at DESC", $params);
$categories = fetchAll("SELECT DISTINCT category FROM products WHERE category IS NOT NULL");
?>

<div class="page-header">
    <h1 class="page-title">Products</h1>
    <div class="breadcrumb">
        <span>Home</span> / <span>Products</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Product List</h2>
        <div style="display: flex; gap: 1rem;">
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= $search ?>" style="width: 250px;">
                <select name="category" class="form-control form-select" style="width: 150px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category'] ?>" <?= $category == $cat['category'] ? 'selected' : '' ?>>
                            <?= $cat['category'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?php if ($product['image']): ?>
                            <img src="../assets/uploads/<?= $product['image'] ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                        <?php else: ?>
                            <div style="width: 50px; height: 50px; background: var(--light); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-image" style="color: var(--text-light);"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><?= $product['sku'] ?></td>
                    <td><strong><?= $product['name'] ?></strong></td>
                    <td><?= $product['category'] ?? '-' ?></td>
                    <td><?= formatCurrency($product['price']) ?></td>
                    <td>
                        <span class="badge <?= $product['stock'] > 10 ? 'badge-success' : ($product['stock'] > 0 ? 'badge-warning' : 'badge-danger') ?>">
                            <?= $product['stock'] ?>
                        </span>
                    </td>
                    <td><?= getStatusBadge($product['status']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="delete.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 2rem;">
                        <i class="fas fa-box-open" style="font-size: 3rem; color: var(--text-light);"></i>
                        <p style="margin-top: 1rem; color: var(--text-light);">No products found</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>