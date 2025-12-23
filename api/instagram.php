<?php
require_once '../config/database.php';
require_once '../config/api.php';

// Instagram API Handler
class InstagramAPI {
    
    // Webhook Verification
    public function verifyWebhook() {
        $mode = $_GET['hub_mode'] ?? '';
        $token = $_GET['hub_verify_token'] ?? '';
        $challenge = $_GET['hub_challenge'] ?? '';
        
        // Use same verify token as WhatsApp or set specific one
        if ($mode === 'subscribe' && $token === WHATSAPP_VERIFY_TOKEN) {
            echo $challenge;
            http_response_code(200);
            exit;
        } else {
            http_response_code(403);
            exit;
        }
    }
    
    // Handle Incoming Webhook
    public function handleWebhook() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        // Log webhook
        file_put_contents('../logs/instagram_' . date('Y-m-d') . '.log', 
            date('Y-m-d H:i:s') . " - " . $input . "\n", 
            FILE_APPEND
        );
        
        if (isset($data['entry'][0]['messaging'][0])) {
            $messaging = $data['entry'][0]['messaging'][0];
            
            if (isset($messaging['message'])) {
                $this->processMessage($messaging);
            }
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
    }
    
    // Process Instagram Message
    private function processMessage($messaging) {
        $senderId = $messaging['sender']['id'];
        $messageText = $messaging['message']['text'] ?? '';
        $messageId = $messaging['message']['mid'];
        
        // Get sender info
        $senderInfo = $this->getUserInfo($senderId);
        
        // Find or create customer
        $customer = fetchOne("SELECT * FROM customers WHERE phone = :phone", ['phone' => $senderId]);
        
        if (!$customer) {
            $customerId = insert('customers', [
                'name' => $senderInfo['name'] ?? 'IG User ' . substr($senderId, -4),
                'phone' => $senderId,
                'notes' => 'Instagram User'
            ]);
        } else {
            $customerId = $customer['id'];
        }
        
        // Get Instagram channel
        $channel = fetchOne("SELECT * FROM channels WHERE type = 'instagram' AND status = 'active'");
        
        // Save message
        insert('messages', [
            'channel_id' => $channel['id'] ?? null,
            'customer_id' => $customerId,
            'sender_type' => 'customer',
            'message' => $messageText,
            'external_id' => $messageId,
            'status' => 'delivered'
        ]);
    }
    
    // Get User Info
    private function getUserInfo($userId) {
        $url = INSTAGRAM_API_URL . $userId . "?fields=name,username&access_token=" . INSTAGRAM_ACCESS_TOKEN;
        $result = apiRequest($url, 'GET');
        return $result['response'] ?? [];
    }
    
    // Send Message
    public function sendMessage($recipientId, $message) {
        $url = INSTAGRAM_API_URL . "me/messages?access_token=" . INSTAGRAM_ACCESS_TOKEN;
        
        $data = [
            'recipient' => [
                'id' => $recipientId
            ],
            'message' => [
                'text' => $message
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
        $result = apiRequest($url, 'POST', $data, $headers);
        
        // Log API call
        $channel = fetchOne("SELECT id FROM channels WHERE type = 'instagram' LIMIT 1");
        logApiCall($channel['id'] ?? null, $url, 'POST', $data, $result['response'], $result['status']);
        
        return $result;
    }
    
    // Send Image
    public function sendImage($recipientId, $imageUrl) {
        $url = INSTAGRAM_API_URL . "me/messages?access_token=" . INSTAGRAM_ACCESS_TOKEN;
        
        $data = [
            'recipient' => [
                'id' => $recipientId
            ],
            'message' => [
                'attachment' => [
                    'type' => 'image',
                    'payload' => [
                        'url' => $imageUrl
                    ]
                ]
            ]
        ];
        
        $headers = ['Content-Type: application/json'];
        return apiRequest($url, 'POST', $data, $headers);
    }
    
    // Get Conversations
    public function getConversations() {
        $url = INSTAGRAM_API_URL . "me/conversations?access_token=" . INSTAGRAM_ACCESS_TOKEN;
        $result = apiRequest($url, 'GET');
        return $result['response']['data'] ?? [];
    }
}

// Handle Request
$instagram = new InstagramAPI();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $instagram->verifyWebhook();
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instagram->handleWebhook();
}
?>