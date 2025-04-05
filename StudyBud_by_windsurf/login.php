<?php
session_start();
require_once 'php/csrf_token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Login to your account">
    <title>StudyBud - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <script src="assets/js/login.js" defer></script>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/images/logo.png" alt="StudyBud">
        </div>
        <div class="login-box">
            <h2>Welcome Back</h2>
            <?php
            if(isset($_SESSION['login_error'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            ?>
            <form id="loginForm" action="login_handler.php" method="POST">
                <?php echo csrf_token_field(); ?>
                <div class="input-group">
                    <input type="email" id="email" name="email" required autocomplete="email">
                    <label for="email">Email</label>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" required autocomplete="current-password" minlength="6">
                    <label for="password">Password</label>
                </div>
                <div class="remember-forgot">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn-login">Login</button>
                <div class="register-link">
                    Don't have an account? <a href="register.php">Register now</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
