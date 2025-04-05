<?php
/**
 * Simple mailer class for StudyBud application
 * Uses PHPMailer for sending emails
 */
class Mailer {
    private $host;
    private $username;
    private $password;
    private $port;
    private $from_email;
    private $from_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Default email configuration
        // These should be set in a configuration file in a real application
        $this->host = 'smtp.example.com';
        $this->username = 'your_email@example.com';
        $this->password = 'your_password';
        $this->port = 587;
        $this->from_email = 'noreply@studybud.com';
        $this->from_name = 'StudyBud';
    }
    
    /**
     * Send a password reset email
     * 
     * @param string $to_email Recipient email
     * @param string $to_name Recipient name
     * @param string $reset_link Password reset link
     * @return array Status and message
     */
    public function sendPasswordResetEmail($to_email, $to_name, $reset_link) {
        // Check if PHPMailer is installed
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // PHPMailer not installed, return instructions
            return [
                'status' => 'error',
                'message' => 'PHPMailer is not installed. To enable email functionality, please install PHPMailer using Composer.'
            ];
        }
        
        try {
            // Include PHPMailer
            require 'vendor/autoload.php';
            
            // Create a new PHPMailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->username;
            $mail->Password = $this->password;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $this->port;
            
            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to_email, $to_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - StudyBud';
            
            // Email body
            $mail->Body = '
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #764ba2; color: white; padding: 20px; text-align: center; }
                    .content { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                    .button { display: inline-block; padding: 10px 20px; background-color: #764ba2; color: white; text-decoration: none; border-radius: 4px; }
                    .footer { margin-top: 20px; font-size: 12px; color: #777; text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>StudyBud Password Reset</h1>
                    </div>
                    <div class="content">
                        <p>Hello ' . htmlspecialchars($to_name) . ',</p>
                        <p>We received a request to reset your password for your StudyBud account. Click the button below to reset your password:</p>
                        <p style="text-align: center;">
                            <a href="' . $reset_link . '" class="button">Reset Password</a>
                        </p>
                        <p>If you did not request a password reset, please ignore this email or contact support if you have concerns.</p>
                        <p>This link will expire in 1 hour for security reasons.</p>
                    </div>
                    <div class="footer">
                        <p>&copy; ' . date('Y') . ' StudyBud. All rights reserved.</p>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            // Plain text version for non-HTML mail clients
            $mail->AltBody = "Hello " . $to_name . ",\n\n" .
                            "We received a request to reset your password for your StudyBud account. " .
                            "Please click the link below to reset your password:\n\n" .
                            $reset_link . "\n\n" .
                            "If you did not request a password reset, please ignore this email or contact support if you have concerns.\n\n" .
                            "This link will expire in 1 hour for security reasons.\n\n" .
                            "StudyBud Team";
            
            // Send the email
            $mail->send();
            
            return [
                'status' => 'success',
                'message' => 'Password reset email sent successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Email could not be sent. Error: ' . $mail->ErrorInfo
            ];
        }
    }
    
    /**
     * Check if email functionality is configured
     * 
     * @return bool True if configured, false otherwise
     */
    public function isConfigured() {
        // Check if the email settings are properly configured
        return ($this->host != 'smtp.example.com' && 
                $this->username != 'your_email@example.com' && 
                $this->password != 'your_password');
    }
}
?>
