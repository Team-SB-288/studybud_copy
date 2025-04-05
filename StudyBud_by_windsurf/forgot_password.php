<?php
session_start();
require_once 'php/csrf_token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Reset your password">
    <title>StudyBud - Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/modern-style.css">
    <link rel="stylesheet" href="css/login.css">
    <style>
        .back-link {
            margin-top: 15px;
            text-align: center;
        }
        
        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .reset-link {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        
        .reset-link:hover {
            background-color: var(--primary-dark);
            text-decoration: none;
        }
        
        .demo-note {
            margin-top: 15px;
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 4px;
            color: #856404;
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
            <h2>Reset Your Password</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
            
            <?php
            if(isset($_SESSION['reset_message'])) {
                echo '<div class="success-message">'.htmlspecialchars($_SESSION['reset_message']).'</div>';
                unset($_SESSION['reset_message']);
            }
            
            if(isset($_SESSION['reset_error'])) {
                echo '<div class="error-message">'.htmlspecialchars($_SESSION['reset_error']).'</div>';
                unset($_SESSION['reset_error']);
            }
            ?>
            
            <form id="resetForm" action="php/reset_password_handler.php" method="POST">
                <?php echo csrf_token_field(); ?>
                <div class="input-group">
                    <input type="email" id="email" name="email" required autocomplete="email">
                    <label for="email">Email</label>
                </div>
                <button type="submit" class="btn-login">Send Reset Link</button>
                <div class="back-link">
                    <a href="login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Simple form validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('Please enter your email address');
                return false;
            }
            
            // Basic email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
        });
    </script>
</body>
</html>
