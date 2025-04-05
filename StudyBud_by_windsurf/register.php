<?php
session_start();
require_once 'php/csrf_token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Create your account">
    <title>StudyBud - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/modern-style.css">
    <link rel="stylesheet" href="css/register.css">
    <script src="js/register.js" defer></script>
    <style>
        /* File upload styling */
        .file-upload-container {
            margin-bottom: 1.5rem;
        }
        
        .file-upload-container h3 {
            font-size: 1rem;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }
        
        .file-upload-area {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1rem;
            border: 1px dashed var(--border-color);
            border-radius: var(--border-radius);
            background-color: var(--light-bg);
        }
        
        .file-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            background-color: white;
            box-shadow: var(--shadow-sm);
        }
        
        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .file-input-wrapper {
            flex: 1;
        }
        
        .file-input {
            position: absolute;
            width: 0.1px;
            height: 0.1px;
            opacity: 0;
            overflow: hidden;
            z-index: -1;
        }
        
        .file-label {
            display: inline-block;
            padding: 0.625rem 1.25rem;
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-label:hover {
            background: var(--primary-dark);
        }
        
        .file-help-text {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="images/logo.png" alt="StudyBud">
        </div>
        <div class="register-box">
            <h2>Create Your Account</h2>
            <?php
            if(isset($_SESSION['register_error'])) {
                echo '<div class="error-message">'.htmlspecialchars($_SESSION['register_error']).'</div>';
                unset($_SESSION['register_error']);
            }
            ?>
            <form id="registerForm" action="register_handler.php" method="POST" enctype="multipart/form-data">
                <?php echo csrf_token_field(); ?>
                <div class="input-group">
                    <input type="text" id="name" name="name" required>
                    <label for="name">Full Name</label>
                </div>
                <div class="input-group">
                    <input type="email" id="email" name="email" required>
                    <label for="email">Email</label>
                </div>
                <div class="input-group">
                    <input type="tel" id="phone" name="phone" required>
                    <label for="phone">Phone Number</label>
                </div>
                <div class="input-group">
                    <input type="password" id="password" name="password" required>
                    <label for="password">Password</label>
                </div>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <label for="confirmPassword">Confirm Password</label>
                </div>
                <div class="file-upload-container">
                    <h3>Profile Picture</h3>
                    <div class="file-upload-area">
                        <div class="file-preview">
                            <img src="images/default-profile.png" alt="Profile Preview" id="profilePreview">
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" id="profilePicture" name="profile_picture" accept="image/jpeg,image/png,image/gif" class="file-input">
                            <label for="profilePicture" class="file-label">
                                <i class="fas fa-cloud-upload-alt"></i> Choose Image
                            </label>
                            <p class="file-help-text">Max size: 2MB. Formats: JPG, PNG, GIF</p>
                        </div>
                    </div>
                </div>
                <div class="terms">
                    <input type="checkbox" id="terms" required>
                    <label for="terms">I agree to the Terms and Conditions</label>
                </div>
                <button type="submit" class="btn-register">Register</button>
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Preview profile picture before upload
        document.getElementById('profilePicture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size exceeds 2MB limit. Please choose a smaller file.');
                    this.value = '';
                    return;
                }
                
                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    alert('Invalid file type. Please select a JPG, PNG, or GIF image.');
                    this.value = '';
                    return;
                }
                
                // Preview the image
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profilePreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
