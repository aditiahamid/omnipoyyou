<?php
$page_title = 'Messages';
require_once '../includes/header.php';

$channel_filter = $_GET['channel'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "WHERE 1=1";
$params = [];

if ($channel_filter) {
    $where .= " AND m.channel_id = :channel";
    $params['channel'] = $channel_filter;
}

if ($status_filter) {
    $where .= " AND m.status = :status";
    $params['status'] = $status_filter;
}

$messages = fetchAll("
    SELECT m.*, c.name as customer_name, ch.name as channel_name, ch.type as channel_type
    FROM messages m
    LEFT JOIN customers c ON m.customer_id = c.id
    LEFT JOIN channels ch ON m.channel_id = ch.id
    $where
    ORDER BY m.created_at DESC
    LIMIT 50
", $params);

$channels = fetchAll("SELECT * FROM channels WHERE status = 'active'");
?>

<div class="page-header">
    <h1 class="page-title">Messages</h1>
    <div class="breadcrumb">
        <span>Home</span> / <span>Messages</span>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Message List</h2>
        <div style="display: flex; gap: 1rem;">
            <form method="GET" style="display: flex; gap: 0.5rem;">
                <select name="channel" class="form-control form-select" style="width: 180px;">
                    <option value="">All Channels</option>
                    <?php foreach ($channels as $ch): ?>
                        <option value="<?= $ch['id'] ?>" <?= $channel_filter == $ch['id'] ? 'selected' : '' ?>>
                            <?= getChannelIcon($ch['type']) ?> <?= $ch['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" class="form-control form-select" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="sent" <?= $status_filter == 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="read" <?= $status_filter == 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="failed" <?= $status_filter == 'failed' ? 'selected' : '' ?>>Failed</option>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                </button>
            </form>
            <a href="add.php" class="btn btn-success">
                <i class="fas fa-plus"></i> New Message
            </a>
        </div>
    </div>
    
    <div style="max-height: 600px; overflow-y: auto;">
        <?php foreach ($messages as $message): ?>
        <div style="padding: 1rem; border-bottom: 1px solid var(--border); transition: background 0.3s;" 
             onmouseover="this.style.background='var(--light)'" 
             onmouseout="this.style.background='white'">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <div style="font-size: 1.5rem;">
                        <?= getChannelIcon($message['channel_type']) ?>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: var(--dark);">
                            <?= $message['customer_name'] ?? 'Unknown Customer' ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-light);">
                            via <?= $message['channel_name'] ?>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 0.25rem;">
                        <?= formatDate($message['created_at']) ?>
                    </div>
                    <?= getStatusBadge($message['status']) ?>
                    <?php if ($message['sender_type'] == 'customer'): ?>
                        <span class="badge badge-info">Incoming</span>
                    <?php else: ?>
                        <span class="badge badge-primary">Outgoing</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="padding: 1rem; background: var(--light); border-radius: 8px; margin-bottom: 0.5rem;">
                <?= nl2br(htmlspecialchars($message['message'])) ?>
                
                <?php if ($message['attachment_url']): ?>
                <div style="margin-top: 0.5rem;">
                    <a href="<?= $message['attachment_url'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                        <i class="fas fa-paperclip"></i> View Attachment
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($message['sender_type'] == 'customer'): ?>
            <div style="display: flex; gap: 0.5rem;">
                <a href="reply.php?id=<?= $message['id'] ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </a>
                <a href="reply.php?id=<?= $message['id'] ?>&action=forward" class="btn btn-sm btn-secondary">
                    <i class="fas fa-share"></i> Forward
                </a>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($messages)): ?>
        <div style="text-align: center; padding: 3rem;">
            <i class="fas fa-envelope-open-text" style="font-size: 3rem; color: var(--text-light);"></i>
            <p style="margin-top: 1rem; color: var(--text-light);">No messages found</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>