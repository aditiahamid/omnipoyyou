<?php
require_once '../config/database.php';
require_once '../config/api.php';

// Email Handler using PHP mail() or SMTP
class EmailAPI {
    
    // Send Email using PHP mail()
    public function sendEmail($to, $subject, $message, $attachments = []) {
        $headers = "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Reply-To: " . SMTP_FROM_EMAIL . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $htmlMessage = $this->getEmailTemplate($message);
        
        $result = mail($to, $subject, $htmlMessage, $headers);
        
        // Log email
        $channel = fetchOne("SELECT id FROM channels WHERE type = 'email' LIMIT 1");
        logApiCall(
            $channel['id'] ?? null,
            'mail()',
            'SEND',
            ['to' => $to, 'subject' => $subject],
            ['sent' => $result],
            $result ? 200 : 500
        );
        
        return $result;
    }
    
    // Send Email using SMTP (PHPMailer alternative)
    public function sendEmailSMTP($to, $subject, $message, $attachments = []) {
        // This is a basic implementation
        // For production, use PHPMailer library
        
        $socket = fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 30);
        
        if (!$socket) {
            return false;
        }
        
        // SMTP commands
        $commands = [
            "EHLO " . SMTP_HOST,
            "AUTH LOGIN",
            base64_encode(SMTP_USERNAME),
            base64_encode(SMTP_PASSWORD),
            "MAIL FROM: <" . SMTP_FROM_EMAIL . ">",
            "RCPT TO: <$to>",
            "DATA",
            "Subject: $subject\r\n",
            "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n",
            "To: $to\r\n",
            "Content-Type: text/html; charset=UTF-8\r\n",
            "\r\n",
            $this->getEmailTemplate($message),
            "\r\n.",
            "QUIT"
        ];
        
        foreach ($commands as $command) {
            fputs($socket, $command . "\r\n");
            $response = fgets($socket, 512);
        }
        
        fclose($socket);
        return true;
    }
    
    // Email Template
    private function getEmailTemplate($content) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 600px;
                    margin: 0 auto;
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    text-align: center;
                }
                .content {
                    padding: 30px;
                    background: #f9fafb;
                }
                .footer {
                    background: #1f2937;
                    color: white;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    background: #4F46E5;
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    margin: 10px 0;
                }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>OmniChannel</h1>
            </div>
            <div class='content'>
                $content
            </div>
            <div class='footer'>
                <p>&copy; 2025 OmniChannel App. All rights reserved.</p>
                <p>This is an automated message, please do not reply.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    // Send Welcome Email
    public function sendWelcomeEmail($to, $name) {
        $subject = "Welcome to OmniChannel!";
        $message = "
            <h2>Hello $name!</h2>
            <p>Welcome to our OmniChannel platform. We're excited to have you on board.</p>
            <p>You can now manage all your customer communications from one place.</p>
            <a href='" . BASE_URL . "' class='button'>Go to Dashboard</a>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    // Send Order Confirmation
    public function sendOrderConfirmation($to, $orderNumber, $amount) {
        $subject = "Order Confirmation - $orderNumber";
        $message = "
            <h2>Order Confirmed!</h2>
            <p>Thank you for your order. Here are the details:</p>
            <ul>
                <li><strong>Order Number:</strong> $orderNumber</li>
                <li><strong>Total Amount:</strong> " . formatCurrency($amount) . "</li>
                <li><strong>Status:</strong> Processing</li>
            </ul>
            <p>We'll notify you once your order is shipped.</p>
            <a href='" . BASE_URL . "orders/track.php?order=$orderNumber' class='button'>Track Order</a>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    // Send Password Reset
    public function sendPasswordReset($to, $token) {
        $subject = "Password Reset Request";
        $resetLink = BASE_URL . "auth/reset.php?token=$token";
        $message = "
            <h2>Password Reset</h2>
            <p>You requested a password reset. Click the button below to reset your password:</p>
            <a href='$resetLink' class='button'>Reset Password</a>
            <p>If you didn't request this, please ignore this email.</p>
            <p>This link will expire in 1 hour.</p>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    // Send Notification
    public function sendNotification($to, $title, $content) {
        $subject = $title;
        $message = "
            <h2>$title</h2>
            <p>$content</p>
        ";
        
        return $this->sendEmail($to, $subject, $message);
    }
    
    // Process Incoming Email (IMAP)
    public function processIncomingEmail() {
        // This requires IMAP extension
        // Example implementation for receiving emails
        
        if (!function_exists('imap_open')) {
            return ['success' => false, 'message' => 'IMAP extension not installed'];
        }
        
        // Connect to mailbox
        $mailbox = "{" . SMTP_HOST . ":993/imap/ssl}INBOX";
        $username = SMTP_USERNAME;
        $password = SMTP_PASSWORD;
        
        $connection = @imap_open($mailbox, $username, $password);
        
        if (!$connection) {
            return ['success' => false, 'message' => 'Could not connect to mailbox'];
        }
        
        // Get unread emails
        $emails = imap_search($connection, 'UNSEEN');
        
        if ($emails) {
            foreach ($emails as $emailId) {
                $header = imap_headerinfo($connection, $emailId);
                $body = imap_body($connection, $emailId);
                
                // Save to database
                $this->saveIncomingEmail($header, $body);
            }
        }
        
        imap_close($connection);
        
        return ['success' => true, 'processed' => count($emails ?? [])];
    }
    
    // Save incoming email to database
    private function saveIncomingEmail($header, $body) {
        $from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        $subject = $header->subject;
        
        // Find or create customer
        $customer = fetchOne("SELECT * FROM customers WHERE email = :email", ['email' => $from]);
        
        if (!$customer) {
            $name = $header->from[0]->personal ?? 'Email User';
            $customerId = insert('customers', [
                'name' => $name,
                'email' => $from
            ]);
        } else {
            $customerId = $customer['id'];
        }
        
        // Get email channel
        $channel = fetchOne("SELECT * FROM channels WHERE type = 'email' AND status = 'active'");
        
        // Save message
        insert('messages', [
            'channel_id' => $channel['id'] ?? null,
            'customer_id' => $customerId,
            'sender_type' => 'customer',
            'message' => strip_tags($body),
            'status' => 'delivered'
        ]);
    }
}

// API Endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = new EmailAPI();
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'send':
            $to = $_POST['to'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message = $_POST['message'] ?? '';
            
            $result = $email->sendEmail($to, $subject, $message);
            echo json_encode(['success' => $result]);
            break;
            
        case 'process_incoming':
            $result = $email->processIncomingEmail();
            echo json_encode($result);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}
?>