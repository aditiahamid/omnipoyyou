<?php
$page_title = 'Add Customer';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'name' => sanitize($_POST['name']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address']),
        'city' => sanitize($_POST['city']),
        'country' => sanitize($_POST['country']),
        'notes' => sanitize($_POST['notes'])
    ];
    
    if (insert('customers', $data)) {
        setFlash('success', 'Customer added successfully');
        header('Location: list.php');
        exit;
    } else {
        setFlash('danger', 'Failed to add customer');
    }
}
?>

<div class="page-header">
    <h1 class="page-title">Add Customer</h1>
    <div class="breadcrumb">
        <span>Home</span> / <a href="list.php">Customers</a> / <span>Add</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Customer Information</h2>
        <a href="list.php" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <form method="POST" action="">
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control">
            </div>
            
            <div class="form-group">
                <label class="form-label">Country</label>
                <input type="text" name="country" class="form-control">
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="3"></textarea>
            </div>
            
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Save Customer
            </button>
            <a href="list.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>