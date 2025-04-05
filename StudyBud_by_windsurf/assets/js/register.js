document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const profilePictureInput = document.getElementById('profilePicture');

    // Add input validation
    nameInput.addEventListener('input', validateName);
    emailInput.addEventListener('input', validateEmail);
    phoneInput.addEventListener('input', validatePhone);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    profilePictureInput.addEventListener('change', validateProfilePicture);

    // Form submission
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        if (!validateName() || !validateEmail() || !validatePhone() ||
            !validatePassword() || !validateConfirmPassword() || !validateProfilePicture()) {
            return;
        }

        // Show loading state
        const submitButton = registerForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Creating account...';

        // Create form data for AJAX submission
        const formData = new FormData(registerForm);
        
        // Send AJAX request
        fetch('register_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Store user data in localStorage for client-side access
                localStorage.setItem('userName', nameInput.value.trim());
                localStorage.setItem('userPicture', data.user?.profile_picture || 'images/default-avatar.png');
                
                // Redirect to dashboard
                window.location.href = data.redirect;
            } else {
                // Show error message
                alert(data.message || 'Registration failed. Please try again.');
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });

    function validateName() {
        const name = nameInput.value.trim();
        
        if (!name) {
            showError(nameInput, 'Name is required');
            return false;
        } else if (name.length < 3) {
            showError(nameInput, 'Name must be at least 3 characters long');
            return false;
        } else {
            clearError(nameInput);
            return true;
        }
    }

    function validateEmail() {
        const email = emailInput.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!email) {
            showError(emailInput, 'Email is required');
            return false;
        } else if (!emailRegex.test(email)) {
            showError(emailInput, 'Please enter a valid email address');
            return false;
        } else {
            clearError(emailInput);
            return true;
        }
    }

    function validatePhone() {
        const phone = phoneInput.value.trim();
        const phoneRegex = /^\d{10}$/;
        
        if (!phone) {
            showError(phoneInput, 'Phone number is required');
            return false;
        } else if (!phoneRegex.test(phone)) {
            showError(phoneInput, 'Please enter a valid 10-digit phone number');
            return false;
        } else {
            clearError(phoneInput);
            return true;
        }
    }

    function validatePassword() {
        const password = passwordInput.value.trim();
        
        if (!password) {
            showError(passwordInput, 'Password is required');
            return false;
        } else if (password.length < 6) {
            showError(passwordInput, 'Password must be at least 6 characters long');
            return false;
        } else {
            clearError(passwordInput);
            return true;
        }
    }

    function validateConfirmPassword() {
        const password = passwordInput.value.trim();
        const confirmPassword = confirmPasswordInput.value.trim();
        
        if (!confirmPassword) {
            showError(confirmPasswordInput, 'Please confirm your password');
            return false;
        } else if (password !== confirmPassword) {
            showError(confirmPasswordInput, 'Passwords do not match');
            return false;
        } else {
            clearError(confirmPasswordInput);
            return true;
        }
    }

    function validateProfilePicture() {
        const file = profilePictureInput.files[0];
        
        if (!file) {
            showError(profilePictureInput, 'Please select a profile picture');
            return false;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showError(profilePictureInput, 'Only JPG, PNG, and GIF files are allowed');
            return false;
        }

        if (file.size > 5 * 1024 * 1024) { // 5MB limit
            showError(profilePictureInput, 'File size must be less than 5MB');
            return false;
        }

        clearError(profilePictureInput);
        return true;
    }

    function showError(input, message) {
        const errorDiv = input.parentElement.querySelector('.error');
        if (!errorDiv) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = message;
    }

    function clearError(input) {
        const errorDiv = input.parentElement.querySelector('.error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Global function for form handling
    window.handleRegistration = function(e) {
        e.preventDefault();
        console.log('Registration form submitted');
        
        // Get form elements
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const profilePictureInput = document.getElementById('profilePicture');
        
        // Validate all fields
        let isValid = true;
        
        if (!nameInput.value.trim()) {
            console.log('Name validation failed');
            isValid = false;
        }
        
        if (!emailInput.value.trim()) {
            console.log('Email validation failed');
            isValid = false;
        }
        
        if (!passwordInput.value.trim()) {
            console.log('Password validation failed');
            isValid = false;
        }
        
        if (passwordInput.value !== confirmPasswordInput.value) {
            console.log('Password confirmation failed');
            isValid = false;
        }
        
        if (!isValid) {
            console.log('Form validation failed');
            return;
        }
        
        // Show loading state
        const submitButton = document.querySelector('.btn-register');
        submitButton.disabled = true;
        submitButton.textContent = 'Creating account...';
        
        // Create form data for AJAX submission
        const formData = new FormData();
        formData.append('name', nameInput.value.trim());
        formData.append('email', emailInput.value.trim());
        formData.append('phone', phoneInput.value.trim());
        formData.append('password', passwordInput.value);
        
        // Handle profile picture
        const file = profilePictureInput.files[0];
        if (file) {
            formData.append('profile_picture', file);
        }
        
        // Send AJAX request
        fetch('register_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Store minimal user data in localStorage for client-side access
                localStorage.setItem('userName', nameInput.value.trim());
                
                // If profile picture URL is returned, store it
                if (data.user && data.user.profile_picture) {
                    localStorage.setItem('userPicture', data.user.profile_picture);
                } else {
                    localStorage.setItem('userPicture', 'images/default-avatar.png');
                }
                
                console.log('Registration successful, redirecting...');
                
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = data.redirect || 'dashboard.html';
                }, 1000);
            } else {
                // Show error message
                alert(data.message || 'Registration failed. Please try again.');
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = 'Register';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = 'Register';
        });
    }
});
