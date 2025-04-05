<?php
session_start();
require_once 'php/db_connect.php';
require_once 'php/csrf_token.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: login.php");
    exit;
}

// Get user data from database
$user_id = $_SESSION['user_id'];
$conn = db_connect();

// Check if bio column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
$bio_exists = $result->num_rows > 0;

// Check if profile_picture column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
$profile_picture_exists = $result->num_rows > 0;

// Add bio column if it doesn't exist
if (!$bio_exists) {
    $conn->query("ALTER TABLE users ADD COLUMN bio TEXT NULL");
}

// Add profile_picture column if it doesn't exist
if (!$profile_picture_exists) {
    $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL");
}

// Get user data
$stmt = $conn->prepare("SELECT name, email" . ($bio_exists ? ", bio" : "") . ($profile_picture_exists ? ", profile_picture" : "") . " FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc();
    
    // Set default values for missing columns
    if (!isset($user_data['bio'])) {
        $user_data['bio'] = '';
    }
    
    if (!isset($user_data['profile_picture'])) {
        $user_data['profile_picture'] = '';
    }
} else {
    // User not found, redirect to login
    header("Location: logout.php");
    exit;
}

$stmt->close();
$conn->close();

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Your profile and settings">
    <title>StudyBud - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="js/modern-script.js" defer></script>
    <style>
        /* Profile-specific styles - Dark Theme */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background-color: #1a1a2e;
            color: #e6e6e6;
            background: #111827;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: #16213e;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            height: 40px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #e6e6e6;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            color: #764ba2;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            background: #16213e;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 30px;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .profile-details h1 {
            color: #e6e6e6;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .profile-details p {
            color: #b3b3b3;
            margin-bottom: 20px;
        }
        
        .profile-stats {
            display: flex;
            gap: 30px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #764ba2;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .edit-profile {
            padding: 8px 16px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 14px;
        }
        
        .edit-profile:hover {
            background: #667eea;
        }
        
        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .profile-sidebar {
            background: #16213e;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar-menu li:last-child {
            border-bottom: none;
        }
        
        .sidebar-menu a {
            text-decoration: none;
            color: #e6e6e6;
            display: flex;
            align-items: center;
            transition: color 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: #0f3460;
            color: #764ba2;
        }
        
        .sidebar-menu .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .profile-main {
            background: #16213e;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        .profile-picture-upload {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .profile-preview {
            margin-right: 20px;
        }
        
        .profile-preview img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .upload-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #764ba2;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .upload-btn:hover {
            background: #667eea;
        }
        
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .course-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
        }
        
        .course-info {
            padding: 15px;
        }
        
        .progress-bar {
            height: 8px;
            background: #eee;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .progress-text {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .btn-view {
            display: inline-block;
            padding: 8px 16px;
            background: #764ba2;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .btn-view:hover {
            background: #667eea;
        }
        
        /* Toggle Switch */
        .toggle-label {
            display: block;
            margin-bottom: 10px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
        }
        
        input:checked + .slider {
            background-color: #764ba2;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .slider.round {
            border-radius: 24px;
        }
        
        .slider.round:before {
            border-radius: 50%;
        }
        
        /* Billing Styles */
        .subscription-info, .payment-methods {
            margin-bottom: 30px;
        }
        
        .plan-details {
            background: #f5f7fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .plan-name {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .plan-price {
            color: #764ba2;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .plan-status {
            display: inline-block;
            background: #e6f7e6;
            color: #2e7d32;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .btn-upgrade, .btn-add-payment {
            padding: 8px 16px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .btn-upgrade:hover, .btn-add-payment:hover {
            background: #667eea;
        }
        
        .payment-card {
            display: flex;
            align-items: center;
            background: #f5f7fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .card-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        
        .card-details {
            flex: 1;
        }
        
        .card-name {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .card-expiry {
            font-size: 12px;
            color: #666;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit, .btn-remove {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .btn-edit {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-remove {
            background: #ffebee;
            color: #c62828;
        }
        
        .section-title {
            color: #e6e6e6;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e6e6e6;
            font-weight: 500;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #0f3460;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
            background-color: #0f3460;
            color: #e6e6e6;
        }
        
        .form-group input:focus, .form-group textarea:focus {
            border-color: #764ba2;
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .save-changes {
            padding: 10px 20px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .save-changes:hover {
            background: #667eea;
        }
        
        .achievement-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .achievement-card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .achievement-card:hover {
            transform: translateY(-5px);
        }
        
        .achievement-icon {
            font-size: 30px;
            color: #764ba2;
            margin-bottom: 10px;
        }
        
        .achievement-title {
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .achievement-date {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation Backdrop -->
    <div class="backdrop"></div>
    
    <!-- Main Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="dashboard.html">
                <img src="images/logo.png" alt="StudyBud">
            </a>
        </div>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navigation Links -->
        <div class="navbar-collapse">
            <!-- No close button needed -->
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="dashboard.html" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="courses.html" class="nav-link">
                        <i class="fas fa-graduation-cap"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="library.html" class="nav-link">
                        <i class="fas fa-book"></i> Library
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link active" aria-current="page">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- User Dropdown -->
        <div class="user-dropdown">
            <div class="user-dropdown-toggle" tabindex="0" aria-haspopup="true" aria-expanded="false">
                <img src="images/default-profile.png" alt="Profile" class="profile-pic" id="userProfilePic">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-dropdown-menu" aria-label="User menu">
                <a href="profile.php" class="dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="profile.html#settings" class="dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="profile-header">
            <img src="<?php echo !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'https://via.placeholder.com/120'; ?>" alt="Profile" class="profile-image" id="headerProfilePic">
            <div class="profile-details">
                <h1 id="headerUserName">John Doe</h1>
                <p>Web Developer | Learning Enthusiast</p>
                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Courses</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">12.5</div>
                        <div class="stat-label">Hours</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">2</div>
                        <div class="stat-label">Certificates</div>
                    </div>
                </div>
            </div>
            <button class="edit-profile" id="editProfileBtn">Edit Profile</button>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <ul class="sidebar-menu">
                    <li><a href="#account" class="active" data-section="account-section"><i class="fas fa-user"></i> Account Information</a></li>
                    <li><a href="#courses" data-section="courses-section"><i class="fas fa-graduation-cap"></i> My Courses</a></li>
                    <li><a href="#certificates" data-section="certificates-section"><i class="fas fa-certificate"></i> Certificates</a></li>
                    <li><a href="#settings" data-section="settings-section"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#billing" data-section="billing-section"><span class="icon">💰</span> Billing & Payments</a></li>
                    <li><a href="#" id="logout-btn"><span class="icon">🚪</span> Logout</a></li>
                </ul>
            </div>

            <div class="profile-main">
                <!-- Account Information Section -->
                <div id="account-section" class="profile-section active">
                <h2 class="section-title">Account Information</h2>
                <div class="profile-picture-upload">
                    <div class="profile-preview">
                        <img src="<?php echo !empty($user_data['profile_picture']) ? $user_data['profile_picture'] : 'https://via.placeholder.com/120'; ?>" alt="Profile" id="profilePreviewImg">
                    </div>
                </div>
                <form id="profileForm" class="account-form" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" disabled required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="4" disabled><?php echo htmlspecialchars($user_data['bio'] ?? 'Web developer and lifelong learner passionate about creating intuitive user experiences.'); ?></textarea>
                    </div>
                    <div class="form-group" id="profilePictureGroup" style="display: none;">
                        <label for="profile_picture">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                        <small>Max size: 5MB. Allowed formats: JPG, PNG, GIF</small>
                    </div>
                    <!-- Hidden CSRF token field -->
                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="button-group">
                        <button type="button" class="edit-profile" id="editProfileBtn">Edit Profile</button>
                        <button type="submit" class="save-profile" id="saveProfileBtn" style="display: none;">Save Changes</button>
                        <button type="button" class="cancel-edit" id="cancelEditBtn" style="display: none;">Cancel</button>
                    </div>
                </form>
            </div>
            
            <h2 class="section-title" style="margin-top: 40px;">Achievements</h2>
            <div class="achievement-grid">
                <!-- ... existing achievements HTML ... -->
            </div>
        </div>
        
        <!-- ... existing settings section HTML ... -->
    </div>
</div>

<!-- ... existing JavaScript ... -->

<script>
    // Handle profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('profilePreviewImg').src = e.target.result;
            }
            
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Handle edit profile button
    document.getElementById('editProfileBtn').addEventListener('click', function() {
        // Enable form fields
        document.querySelectorAll('.account-form input:not([type="hidden"]), .account-form textarea').forEach(field => {
            field.disabled = false;
        });
        
        // Show profile picture upload field
        document.getElementById('profilePictureGroup').style.display = 'block';
        
        // Hide edit button, show save and cancel buttons
        this.style.display = 'none';
        document.getElementById('saveProfileBtn').style.display = 'inline-block';
        document.getElementById('cancelEditBtn').style.display = 'inline-block';
        
        // CSRF token is already set server-side, no need to fetch it
    });
    
    // Handle cancel button
    document.getElementById('cancelEditBtn').addEventListener('click', function() {
        // Disable form fields
        document.querySelectorAll('.account-form input:not([type="hidden"]), .account-form textarea').forEach(field => {
            field.disabled = true;
        });
        
        // Reset form to original values
        document.getElementById('profileForm').reset();
        
        // Hide profile picture upload field
        document.getElementById('profilePictureGroup').style.display = 'none';
        
        // Show edit button, hide save and cancel buttons
        document.getElementById('editProfileBtn').style.display = 'inline-block';
        document.getElementById('saveProfileBtn').style.display = 'none';
        this.style.display = 'none';
    });
    
    // Handle form submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Create FormData object
        const formData = new FormData(this);
        
        // Show loading indicator
        const saveBtn = document.getElementById('saveProfileBtn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;
        
        // Send AJAX request
        fetch('php/update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
            
            if (data.success) {
                // Show success message
                alert(data.message);
                
                // Update UI with new data
                if (data.data.name) {
                    document.getElementById('name').value = data.data.name;
                    document.getElementById('headerUserName').textContent = data.data.name;
                }
                
                if (data.data.email) {
                    document.getElementById('email').value = data.data.email;
                }
                
                if (data.data.bio) {
                    document.getElementById('bio').value = data.data.bio;
                }
                
                if (data.data.profile_picture) {
                    // Update profile preview image
                    document.getElementById('profilePreviewImg').src = data.data.profile_picture;
                    
                    // Update header profile picture
                    const headerProfilePic = document.getElementById('headerProfilePic');
                    if (headerProfilePic) {
                        headerProfilePic.src = data.data.profile_picture;
                    }
                    
                    // Update navbar profile picture if it exists
                    const navbarProfilePic = document.querySelector('.profile-pic');
                    if (navbarProfilePic) {
                        navbarProfilePic.src = data.data.profile_picture;
                    }
                }
                
                // Disable form fields
                document.querySelectorAll('.account-form input:not([type="hidden"]), .account-form textarea').forEach(field => {
                    field.disabled = true;
                });
                
                // Hide profile picture upload field
                document.getElementById('profilePictureGroup').style.display = 'none';
                
                // Show edit button, hide save and cancel buttons
                document.getElementById('editProfileBtn').style.display = 'inline-block';
                document.getElementById('saveProfileBtn').style.display = 'none';
                document.getElementById('cancelEditBtn').style.display = 'none';
            } else {
                // Show error message
                alert('Error: ' + data.message);
                console.error('Profile update error:', data);
            }
        })
        .catch(error => {
            console.error('Error updating profile:', error);
            alert('An error occurred while updating your profile. Please try again.');
        });
    });
    </script>
</body>
</html>
