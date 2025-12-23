<?php
require_once '../config/database.php';
require_once '../config/api.php';

// Telegram Bot API Handler
class TelegramAPI {
    
    // Handle Webhook
    public function handleWebhook() {
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);
        
        // Log webhook
        file_put_contents('../logs/telegram_' . date('Y-m-d') . '.log', 
            date('Y-m-d H:i:s') . " - " . $input . "\n", 
            FILE_APPEND
        );
        
        if (isset($update['message'])) {
            $this->processMessage($update['message']);
        }
        
        http_response_code(200);
        echo json_encode(['ok' => true]);
    }
    
    // Process Message
    private function processMessage($message) {
        $chatId = $message['chat']['id'];
        $from = $message['from'];
        $text = $message['text'] ?? '';
        $messageId = $message['message_id'];
        
        // Find or create customer
        $customer = fetchOne("SELECT * FROM customers WHERE phone = :phone", ['phone' => $chatId]);
        
        if (!$customer) {
            $name = trim(($from['first_name'] ?? '') . ' ' . ($from['last_name'] ?? ''));
            $customerId = insert('customers', [
                'name' => $name ?: 'Telegram User ' . substr($chatId, -4),
                'phone' => $chatId,
                'email' => $from['username'] ?? null,
                'notes' => 'Telegram User'
            ]);
        } else {
            $customerId = $customer['id'];
        }
        
        // Get Telegram channel
        $channel = fetchOne("SELECT * FROM channels WHERE type = 'telegram' AND status = 'active'");
        
        // Save message
        insert('messages', [
            'channel_id' => $channel['id'] ?? null,
            'customer_id' => $customerId,
            'sender_type' => 'customer',
            'message' => $text,
            'external_id' => $messageId,
            'status' => 'delivered'
        ]);
        
        // Auto reply (optional)
        // $this->sendMessage($chatId, "Terima kasih atas pesan Anda. Tim kami akan segera merespons.");
    }
    
    // Send Message
    public function sendMessage($chatId, $text, $replyMarkup = null) {
        $url = TELEGRAM_API_URL . "sendMessage";
        
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        if ($replyMarkup) {
            $data['reply_markup'] = json_encode($replyMarkup);
        }
        
        $result = apiRequest($url, 'POST', $data);
        
        // Log API call
        $channel = fetchOne("SELECT id FROM channels WHERE type = 'telegram' LIMIT 1");
        logApiCall($channel['id'] ?? null, $url, 'POST', $data, $result['response'], $result['status']);
        
        return $result;
    }
    
    // Send Photo
    public function sendPhoto($chatId, $photoUrl, $caption = '') {
        $url = TELEGRAM_API_URL . "sendPhoto";
        
        $data = [
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption
        ];
        
        return apiRequest($url, 'POST', $data);
    }
    
    // Send Document
    public function sendDocument($chatId, $documentUrl, $caption = '') {
        $url = TELEGRAM_API_URL . "sendDocument";
        
        $data = [
            'chat_id' => $chatId,
            'document' => $documentUrl,
            'caption' => $caption
        ];
        
        return apiRequest($url, 'POST', $data);
    }
    
    // Set Webhook
    public function setWebhook($webhookUrl) {
        $url = TELEGRAM_API_URL . "setWebhook";
        
        $data = [
            'url' => $webhookUrl
        ];
        
        return apiRequest($url, 'POST', $data);
    }
    
    // Get Updates (for testing)
    public function getUpdates() {
        $url = TELEGRAM_API_URL . "getUpdates";
        $result = apiRequest($url, 'GET');
        return $result['response']['result'] ?? [];
    }
    
    // Send Keyboard
    public function sendKeyboard($chatId, $text, $buttons) {
        $keyboard = [
            'keyboard' => $buttons,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        
        return $this->sendMessage($chatId, $text, $keyboard);
    }
    
    // Send Inline Keyboard
    public function sendInlineKeyboard($chatId, $text, $buttons) {
        $keyboard = [
            'inline_keyboard' => $buttons
        ];
        
        return $this->sendMessage($chatId, $text, $keyboard);
    }
}

// Handle Request
$telegram = new TelegramAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telegram->handleWebhook();
} else if (isset($_GET['action'])) {
    // Setup webhook
    if ($_GET['action'] === 'setwebhook') {
        $result = $telegram->setWebhook(WEBHOOK_TELEGRAM);
        echo json_encode($result);
    }
}
?>