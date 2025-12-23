<?php
require_once '../config/database.php';
require_once '../config/api.php';

// WhatsApp Business API Handler
class WhatsAppAPI {
    
    // Webhook Verification (GET request)
    public function verifyWebhook() {
        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';
        
        if ($mode === 'subscribe' && $token === WHATSAPP_VERIFY_TOKEN) {
            echo $challenge;
            http_response_code(200);
            exit;
        } else {
            http_response_code(403);
            exit;
        }
    }
    
    // Webhook Handler (POST request)
    public function handleWebhook() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Log incoming webhook
        file_put_contents('../logs/whatsapp_' . date('Y-m-d') . '.log', 
            date('Y-m-d H:i:s') . " - " . $input . "\n", 
            FILE_APPEND
        );
        
        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];
            $this->processIncomingMessage($message);
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }
    
    // Process Incoming Message
    private function processIncomingMessage($message) {
        $from = $message['from'];
        $messageId = $message['id'];
        $messageType = $message['type'];
        $messageText = $message['text']['body'] ?? '';
        
        // Find or create customer
        $customer = fetchOne("SELECT * FROM customers WHERE phone = :phone", ['phone' => $from]);
        
        if (!$customer) {
            $customerId = insert('customers', [
                'name' => 'WhatsApp User ' . substr($from, -4),
                'phone' => $from
            ]);
        } else {
            $customerId = $customer['id'];
        }
        
        // Get WhatsApp channel
        $channel = fetchOne("SELECT * FROM channels WHERE type = 'whatsapp' AND status = 'active'");
        
        // Save message
        insert('messages', [
            'channel_id' => $channel['id'] ?? null,
            'customer_id' => $customerId,
            'sender_type' => 'customer',
            'message' => $messageText,
            'message_type' => $messageType,
            'external_id' => $messageId,
            'status' => 'delivered'
        ]);
        
        // Mark as read
        $this->markAsRead($messageId);
    }
    
    // Send Text Message
    public function sendMessage($to, $message) {
        $url = WHATSAPP_API_URL . WHATSAPP_PHONE_ID . "/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ];
        
        $result = apiRequest($url, 'POST', $data, $headers);
        
        // Log API call
        $channel = fetchOne("SELECT id FROM channels WHERE type = 'whatsapp' LIMIT 1");
        logApiCall($channel['id'] ?? null, $url, 'POST', $data, $result['response'], $result['status']);
        
        return $result;
    }
    
    // Send Template Message
    public function sendTemplate($to, $template, $params = []) {
        $url = WHATSAPP_API_URL . WHATSAPP_PHONE_ID . "/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $template,
                'language' => ['code' => 'id'],
                'components' => $params
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ];
        
        return apiRequest($url, 'POST', $data, $headers);
    }
    
    // Send Media Message
    public function sendMedia($to, $mediaUrl, $type = 'image', $caption = '') {
        $url = WHATSAPP_API_URL . WHATSAPP_PHONE_ID . "/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $type,
            $type => [
                'link' => $mediaUrl
            ]
        ];
        
        if ($caption && in_array($type, ['image', 'video', 'document'])) {
            $data[$type]['caption'] = $caption;
        }
        
        $headers = [
            'Authorization: Bearer ' . WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ];
        
        return apiRequest($url, 'POST', $data, $headers);
    }
    
    // Mark Message as Read
    private function markAsRead($messageId) {
        $url = WHATSAPP_API_URL . WHATSAPP_PHONE_ID . "/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];
        
        $headers = [
            'Authorization: Bearer ' . WHATSAPP_TOKEN,
            'Content-Type: application/json'
        ];
        
        return apiRequest($url, 'POST', $data, $headers);
    }
}

// Handle Request
$whatsapp = new WhatsAppAPI();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $whatsapp->verifyWebhook();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp->handleWebhook();
}
?>