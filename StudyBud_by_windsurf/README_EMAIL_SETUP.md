# StudyBud Email Functionality Setup

This document provides instructions on how to set up email functionality for the StudyBud application, specifically for the password reset feature.

## Prerequisites

1. PHP 7.2 or higher
2. Composer (dependency manager for PHP)
3. SMTP server details (can be from Gmail, Outlook, or any other email provider)

## Installation Steps

### 1. Install PHPMailer using Composer

Open a terminal/command prompt in your project directory and run:

```bash
composer require phpmailer/phpmailer
```

This will create a `vendor` directory with the PHPMailer library.

### 2. Configure Email Settings

Open the file `php/mailer.php` and update the following settings with your SMTP server details:

```php
// Default email configuration
// These should be set in a configuration file in a real application
$this->host = 'smtp.example.com';         // Change to your SMTP server (e.g., smtp.gmail.com)
$this->username = 'your_email@example.com'; // Change to your email address
$this->password = 'your_password';        // Change to your email password or app password
$this->port = 587;                        // Change if your SMTP server uses a different port
$this->from_email = 'noreply@studybud.com'; // Change to your preferred "from" email
$this->from_name = 'StudyBud';            // Change if needed
```

### 3. Gmail-Specific Setup

If you're using Gmail as your SMTP server:

1. Set `$this->host` to `smtp.gmail.com`
2. Set `$this->port` to `587`
3. Use your Gmail address as `$this->username`
4. For `$this->password`, you'll need to create an "App Password":
   - Go to your Google Account → Security
   - Enable 2-Step Verification if not already enabled
   - Go to "App passwords" (under "Signing in to Google")
   - Create a new app password for "Mail" and "Other (Custom name)"
   - Use the generated 16-character password as your SMTP password

### 4. Testing the Email Functionality

After configuring the email settings:

1. Try the "Forgot Password" feature
2. Enter a valid email address
3. You should receive a password reset email
4. If you don't receive the email, check your spam folder

## Troubleshooting

If you encounter issues with sending emails:

1. **Connection errors**: Verify your SMTP server, username, and password
2. **Authentication errors**: Make sure you're using the correct credentials
3. **Gmail issues**: Ensure you're using an App Password and have enabled less secure apps
4. **Firewall issues**: Make sure your server allows outgoing connections on the SMTP port

## Security Considerations

1. Never store email passwords directly in your code in a production environment
2. Consider using environment variables or a secure configuration file
3. Regularly update your email password
4. Monitor for unauthorized use of your email account

For additional help, refer to the [PHPMailer documentation](https://github.com/PHPMailer/PHPMailer/blob/master/README.md).
