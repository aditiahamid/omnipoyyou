<?php
$page_title = 'Channels';
require_once '../includes/header.php';

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $channelId = $_POST['channel_id'];
    $currentStatus = $_POST['current_status'];
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';
    
    update('channels', ['status' => $newStatus], 'id = :id', ['id' => $channelId]);
    setFlash('success', 'Channel status updated successfully');
    header('Location: list.php');
    exit;
}

$channels = fetchAll("SELECT * FROM channels ORDER BY type");
?>

<div class="page-header">
    <h1 class="page-title">Communication Channels</h1>
    <div class="breadcrumb">
        <span>Home</span> / <span>Channels</span>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
    <?php foreach ($channels as $channel): ?>
    <div class="card">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            <div style="font-size: 3rem;">
                <?= getChannelIcon($channel['type']) ?>
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0; font-size: 1.25rem;"><?= $channel['name'] ?></h3>
                <p style="margin: 0; color: var(--text-light); font-size: 0.9rem;">
                    <?= ucfirst($channel['type']) ?>
                </p>
            </div>
            <?= getStatusBadge($channel['status']) ?>
        </div>
        
        <div style="background: var(--light); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <div style="margin-bottom: 0.75rem;">
                <strong>Webhook URL:</strong><br>
                <code style="font-size: 0.85rem; word-break: break-all;">
                    <?= $channel['webhook_url'] ?: BASE_URL . 'api/' . $channel['type'] . '.php' ?>
                </code>
                <button onclick="copyToClipboard('<?= BASE_URL . 'api/' . $channel['type'] . '.php' ?>')" 
                        class="btn btn-sm btn-secondary" style="margin-top: 0.5rem;">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            
            <?php if ($channel['api_token']): ?>
            <div>
                <strong>Status:</strong> 
                <span style="color: var(--secondary);">✓ Configured</span>
            </div>
            <?php else: ?>
            <div>
                <strong>Status:</strong> 
                <span style="color: var(--warning);">⚠ Not configured</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;">
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="channel_id" value="<?= $channel['id'] ?>">
                <input type="hidden" name="current_status" value="<?= $channel['status'] ?>">
                <button type="submit" name="toggle_status" class="btn btn-sm <?= $channel['status'] === 'active' ? 'btn-danger' : 'btn-success' ?>" style="width: 100%;">
                    <i class="fas fa-power-off"></i>
                    <?= $channel['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                </button>
            </form>
            
            <a href="add.php?type=<?= $channel['type'] ?>&id=<?= $channel['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-cog"></i> Configure
            </a>
        </div>
        
        <?php
        // Get message count for this channel
        $msgCount = fetchOne("SELECT COUNT(*) as total FROM messages WHERE channel_id = :id", 
            ['id' => $channel['id']])['total'];
        ?>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); text-align: center; color: var(--text-light); font-size: 0.9rem;">
            <i class="fas fa-envelope"></i> <?= $msgCount ?> messages received
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Integration Guide -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <h2 class="card-title">Integration Guide</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div>
            <h4 style="margin-bottom: 0.5rem;">📱 WhatsApp Business</h4>
            <ol style="margin-left: 1.5rem; line-height: 1.8;">
                <li>Create a Meta Business Account</li>
                <li>Setup WhatsApp Business API</li>
                <li>Get Phone Number ID and Access Token</li>
                <li>Configure webhook in Meta Dashboard</li>
                <li>Update config/api.php with credentials</li>
            </ol>
        </div>
        
        <div>
            <h4 style="margin-bottom: 0.5rem;">📷 Instagram</h4>
            <ol style="margin-left: 1.5rem; line-height: 1.8;">
                <li>Create Facebook App</li>
                <li>Add Instagram Messaging product</li>
                <li>Connect Instagram Business Account</li>
                <li>Setup webhook subscription</li>
                <li>Update config/api.php with credentials</li>
            </ol>
        </div>
        
        <div>
            <h4 style="margin-bottom: 0.5rem;">✈️ Telegram</h4>
            <ol style="margin-left: 1.5rem; line-height: 1.8;">
                <li>Create bot via @BotFather</li>
                <li>Get bot token</li>
                <li>Set webhook URL using setWebhook</li>
                <li>Update config/api.php with token</li>
                <li>Test bot with /start command</li>
            </ol>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>