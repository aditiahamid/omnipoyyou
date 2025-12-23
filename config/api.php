<?php
// API Configuration untuk berbagai channel

// Base URL aplikasi
define('BASE_URL', 'http://localhost/omnipoyyou/');
define('API_BASE_URL', BASE_URL . 'api/');

// WhatsApp Business API Configuration
define('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0/');
define('WHATSAPP_TOKEN', 'YOUR_WHATSAPP_TOKEN_HERE');
define('WHATSAPP_PHONE_ID', 'YOUR_PHONE_NUMBER_ID');
define('WHATSAPP_VERIFY_TOKEN', 'YOUR_VERIFY_TOKEN');

// Instagram API Configuration
define('INSTAGRAM_APP_ID', 'YOUR_INSTAGRAM_APP_ID');
define('INSTAGRAM_APP_SECRET', 'YOUR_INSTAGRAM_APP_SECRET');
define('INSTAGRAM_ACCESS_TOKEN', 'YOUR_INSTAGRAM_ACCESS_TOKEN');
define('INSTAGRAM_API_URL', 'https://graph.instagram.com/');

// Facebook Messenger Configuration
define('FB_PAGE_ACCESS_TOKEN', 'YOUR_FB_PAGE_ACCESS_TOKEN');
define('FB_VERIFY_TOKEN', 'YOUR_FB_VERIFY_TOKEN');
define('FB_API_URL', 'https://graph.facebook.com/v18.0/');

// Telegram Bot Configuration
define('TELEGRAM_BOT_TOKEN', 'YOUR_TELEGRAM_BOT_TOKEN');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/');

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@omnichannel.com');
define('SMTP_FROM_NAME', 'Omnichannel Support');

// API Rate Limiting
define('API_RATE_LIMIT', 100); // requests per minute
define('API_CACHE_DURATION', 300); // 5 minutes

// Webhook URLs
define('WEBHOOK_WHATSAPP', BASE_URL . 'api/whatsapp.php');
define('WEBHOOK_INSTAGRAM', BASE_URL . 'api/instagram.php');
define('WEBHOOK_FACEBOOK', BASE_URL . 'api/facebook.php');
define('WEBHOOK_TELEGRAM', BASE_URL . 'api/telegram.php');

// Function untuk log API calls
function logApiCall($channel_id, $endpoint, $method, $request, $response, $status) {
    $data = [
        'channel_id' => $channel_id,
        'endpoint' => $endpoint,
        'method' => $method,
        'request_data' => json_encode($request),
        'response_data' => json_encode($response),
        'status_code' => $status
    ];
    insert('api_logs', $data);
}

// Function untuk cURL request
function apiRequest($url, $method = 'GET', $data = [], $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'response' => json_decode($response, true),
        'status' => $httpCode
    ];
}
?>