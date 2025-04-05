<?php
session_start();
require_once 'php/csrf_token.php';

// Check if token is provided
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    $_SESSION['login_error'] = "Invalid or expired password reset link.";
    header("Location: login.php");
    exit;
}

// Store token in session for the form submission
$_SESSION['reset_token'] = $token;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Set a new password">
    <title>StudyBud - Reset Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/modern-style.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .password-strength {
            margin-top: 5px;
            font-size: 0.8rem;
        }
        
        .password-strength.weak {
            color: #dc3545;
        }
        
        .password-strength.medium {
            color: #ffc107;
        }
        
        .password-strength.strong {
            color: #28a745;
        }
        
        .password-requirements {
            margin-top: 10px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .password-requirements ul {
            padding-left: 20px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <a href="dashboard.html">
                <img src="images/logo.png" alt="StudyBud">
            </a>
        </div>
        <div class="login-box">
            <h2>Set New Password</h2>
            <p>Please enter your new password below.</p>
            
            <?php
            if(isset($_SESSION['reset_error'])) {
                echo '<div class="error-message">'.htmlspecialchars($_SESSION['reset_error']).'</div>';
                unset($_SESSION['reset_error']);
            }
            ?>
            
            <form id="newPasswordForm" action="php/update_password_handler.php" method="POST">
                <?php echo csrf_token_field(); ?>
                <div class="input-group">
                    <input type="password" id="password" name="password" required minlength="8">
                    <label for="password">New Password</label>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>
                <div class="password-requirements">
                    Password requirements:
                    <ul>
                        <li>At least 8 characters long</li>
                        <li>Include at least one uppercase letter</li>
                        <li>Include at least one number</li>
                        <li>Include at least one special character</li>
                    </ul>
                </div>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8">
                    <label for="confirmPassword">Confirm New Password</label>
                </div>
                <button type="submit" class="btn-login">Reset Password</button>
            </form>
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthElement = document.getElementById('passwordStrength');
            
            // Check password strength
            let strength = 0;
            
            // Length check
            if (password.length >= 8) {
                strength += 1;
            }
            
            // Uppercase check
            if (/[A-Z]/.test(password)) {
                strength += 1;
            }
            
            // Number check
            if (/[0-9]/.test(password)) {
                strength += 1;
            }
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) {
                strength += 1;
            }
            
            // Update strength indicator
            if (password.length === 0) {
                strengthElement.textContent = '';
                strengthElement.className = 'password-strength';
            } else if (strength < 2) {
                strengthElement.textContent = 'Weak password';
                strengthElement.className = 'password-strength weak';
            } else if (strength < 4) {
                strengthElement.textContent = 'Medium strength password';
                strengthElement.className = 'password-strength medium';
            } else {
                strengthElement.textContent = 'Strong password';
                strengthElement.className = 'password-strength strong';
            }
        });
        
        // Form validation
        document.getElementById('newPasswordForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Check if passwords match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }
            
            // Check password strength
            let isValid = true;
            const errorMessages = [];
            
            if (password.length < 8) {
                isValid = false;
                errorMessages.push('Password must be at least 8 characters long');
            }
            
            if (!/[A-Z]/.test(password)) {
                isValid = false;
                errorMessages.push('Password must include at least one uppercase letter');
            }
            
            if (!/[0-9]/.test(password)) {
                isValid = false;
                errorMessages.push('Password must include at least one number');
            }
            
            if (!/[^A-Za-z0-9]/.test(password)) {
                isValid = false;
                errorMessages.push('Password must include at least one special character');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following issues:\n' + errorMessages.join('\n'));
                return false;
            }
        });
    </script>
</body>
</html>
