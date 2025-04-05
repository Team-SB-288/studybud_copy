document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // Add input validation
    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', validatePassword);

    // Display user data on page load
    displayUserData();

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateEmail() || !validatePassword()) {
            return;
        }

        // Show loading state
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Logging in...';

        // Create form data for AJAX submission
        const formData = new FormData();
        formData.append('email', emailInput.value.trim());
        formData.append('password', passwordInput.value);
        
        // Send AJAX request
        fetch('login_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Store user data in localStorage for client-side access
                if (data.user) {
                    localStorage.setItem('userName', data.user.name || '');
                    localStorage.setItem('userPicture', data.user.profile_picture || 'images/default-avatar.png');
                }
                
                // Redirect to dashboard
                window.location.href = data.redirect || 'dashboard.html';
            } else {
                // Show error message
                showError(emailInput, data.message || 'Login failed. Please check your credentials.');
                
                // Reset button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError(emailInput, 'An error occurred. Please try again.');
            
            // Reset button state
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        });
    });

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

    // Update to store user data after login
    function storeUserData(name, picture) {
        localStorage.setItem('userName', name);
        localStorage.setItem('userPicture', picture);
    }

    // Update to retrieve and display user data
    function displayUserData() {
        const name = localStorage.getItem('userName');
        const picture = localStorage.getItem('userPicture');
        if (name && picture) {
            document.querySelector('.user-name').textContent = name;
            document.querySelector('.user-picture').src = picture;
        }
    }

    // Global function for form handling
    window.handleLogin = function(e) {
        // Do not prevent default - let the form submit normally
        // e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        // Validate email and password
        if (!validateEmail() || !validatePassword()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        const submitButton = document.querySelector('.btn-login');
        submitButton.disabled = true;
        submitButton.textContent = 'Logging in...';
        
        // Let the form submit normally to login_handler.php
        return true;
    };
});
