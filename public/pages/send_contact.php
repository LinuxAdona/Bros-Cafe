<?php
// Start output buffering to catch any unexpected output
ob_start();

// Set content type header
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Get the base directory (go up 2 levels from public/pages/)
$baseDir = dirname(dirname(__DIR__));

// Load PHPMailer manually (no Composer required)
require_once $baseDir . '/lib/PHPMailer/src/Exception.php';
require_once $baseDir . '/lib/PHPMailer/src/PHPMailer.php';
require_once $baseDir . '/lib/PHPMailer/src/SMTP.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

try {
    // Load email configuration
    $emailConfig = require $baseDir . '/config/email.php';

    // Clear any output from config file
    ob_clean();

    // Check if form was submitted
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new \Exception('Invalid request method');
    }

    // Get and validate form data
    $firstName = isset($_POST['first-name']) ? trim($_POST['first-name']) : '';
    $lastName = isset($_POST['last-name']) ? trim($_POST['last-name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($subject) || empty($message)) {
        throw new \Exception('Please fill in all required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \Exception('Please enter a valid email address');
    }

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    // Enable debug output if configured
    if ($emailConfig['enable_debug']) {
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    }

    // Server settings
    $mail->isSMTP();
    $mail->Host       = $emailConfig['smtp_host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $emailConfig['smtp_username'];
    $mail->Password   = $emailConfig['smtp_password'];
    $mail->SMTPSecure = $emailConfig['smtp_secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $emailConfig['smtp_port'];

    // Recipients
    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress($emailConfig['to_email'], $emailConfig['to_name']);
    $mail->addReplyTo($email, $firstName . ' ' . $lastName);

    // Subject mapping
    $subjectMap = [
        'general' => 'General Inquiry',
        'feedback' => 'Customer Feedback',
        'complaint' => 'Customer Complaint',
        'catering' => 'Catering Services Inquiry',
        'partnership' => 'Partnership Opportunity',
        'other' => 'Other Inquiry'
    ];

    $emailSubject = isset($subjectMap[$subject]) ? $subjectMap[$subject] : 'Contact Form Submission';

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Bro\'s Cafe - ' . $emailSubject;

    // HTML email body
    $htmlBody = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
            .field { margin-bottom: 20px; }
            .label { font-weight: bold; color: #6b7280; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }
            .value { background: white; padding: 10px; border-left: 3px solid #f59e0b; margin-top: 5px; }
            .footer { background: #1f2937; color: #9ca3af; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1 style="margin: 0;">New Contact Form Submission</h1>
                <p style="margin: 5px 0 0 0;">Bro\'s Cafe</p>
            </div>
            <div class="content">
                <div class="field">
                    <div class="label">From</div>
                    <div class="value">' . htmlspecialchars($firstName . ' ' . $lastName) . '</div>
                </div>
                
                <div class="field">
                    <div class="label">Email Address</div>
                    <div class="value"><a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a></div>
                </div>
                
                <div class="field">
                    <div class="label">Phone Number</div>
                    <div class="value">' . (!empty($phone) ? htmlspecialchars($phone) : 'Not provided') . '</div>
                </div>
                
                <div class="field">
                    <div class="label">Subject</div>
                    <div class="value">' . htmlspecialchars($emailSubject) . '</div>
                </div>
                
                <div class="field">
                    <div class="label">Message</div>
                    <div class="value">' . nl2br(htmlspecialchars($message)) . '</div>
                </div>
                
                <div class="field">
                    <div class="label">Submitted On</div>
                    <div class="value">' . date('F d, Y h:i A') . '</div>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0;">This email was sent from the Bro\'s Cafe contact form</p>
                <p style="margin: 5px 0 0 0;">Please reply directly to the customer\'s email address</p>
            </div>
        </div>
    </body>
    </html>
    ';

    // Plain text alternative
    $textBody = "New Contact Form Submission\n\n";
    $textBody .= "From: $firstName $lastName\n";
    $textBody .= "Email: $email\n";
    $textBody .= "Phone: " . (!empty($phone) ? $phone : 'Not provided') . "\n";
    $textBody .= "Subject: $emailSubject\n\n";
    $textBody .= "Message:\n$message\n\n";
    $textBody .= "Submitted: " . date('F d, Y h:i A') . "\n";

    $mail->Body    = $htmlBody;
    $mail->AltBody = $textBody;

    // Send email
    $mail->send();

    // Clear any output buffer
    ob_clean();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for contacting us! We\'ll get back to you within 24 hours.'
    ]);
} catch (\Exception $e) {
    // Clear any output buffer
    ob_clean();

    // Error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (\Throwable $e) {
    // Catch any other errors (PHP 7+)
    ob_clean();

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
}

// End output buffering and send
ob_end_flush();
