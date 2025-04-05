<?php
// Get current user
$current_user = get_current_user();
?>

<!-- Navigation -->
<nav class="navbar">
    <div class="logo">
        <a href="index.php">
            <img src="assets/images/logo.png" alt="Study Bud">
        </a>
    </div>
    <div class="nav-links">
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Home</a>
        <a href="pages/courses.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'courses.php' ? 'active' : ''; ?>">Courses</a>
        <a href="pages/library.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'library.php' ? 'active' : ''; ?>">Library</a>
        <a href="pages/profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">Profile</a>
    </div>
    <div class="user-info">
        <?php if (is_logged_in()): ?>
            <div class="notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-count">3</span>
            </div>
            <div class="profile-dropdown">
                <img src="<?php echo htmlspecialchars($current_user['profile_picture'] ?? 'assets/images/default-profile.png'); ?>" alt="Profile" class="profile-pic">
                <span><?php echo htmlspecialchars($current_user['name']); ?></span>
                <div class="dropdown-content">
                    <a href="pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="pages/settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="pages/auth/login.php" class="login-btn">Login</a>
            <a href="pages/auth/register.php" class="register-btn">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Flash Messages -->
<?php if ($messages = get_flash_messages()): ?>
    <div class="flash-messages">
        <?php foreach ($messages as $message): ?>
            <div class="flash-message <?php echo htmlspecialchars($message['type']); ?>">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message['message']); ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
