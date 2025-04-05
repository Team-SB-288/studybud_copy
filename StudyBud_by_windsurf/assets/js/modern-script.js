/**
 * StudyBud Modern Script
 * Handles responsive navigation, dropdowns, and other interactive elements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Ensure navigation links work correctly
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    if (navLinks.length) {
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                if (href) {
                    window.location.href = href;
                }
            });
        });
    }
    // Mobile Navigation Toggle
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const backdrop = document.querySelector('.backdrop');
    const navbarClose = document.querySelector('.navbar-close');
    
    if (navbarToggler) {
        navbarToggler.addEventListener('click', function() {
            navbarCollapse.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
        });
    }
    
    if (navbarClose) {
        navbarClose.addEventListener('click', closeNavbar);
    }
    
    if (backdrop) {
        backdrop.addEventListener('click', closeNavbar);
    }
    
    function closeNavbar() {
        navbarCollapse.classList.remove('show');
        backdrop.classList.remove('show');
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    // User Dropdown Toggle
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        userDropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
    
    // Accessibility: Add keyboard navigation for dropdown
    if (userDropdownToggle) {
        userDropdownToggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                userDropdownMenu.classList.toggle('show');
                
                if (userDropdownMenu.classList.contains('show')) {
                    const firstItem = userDropdownMenu.querySelector('a');
                    if (firstItem) firstItem.focus();
                }
            }
        });
    }
    
    // Add keyboard navigation within dropdown menu
    const dropdownItems = document.querySelectorAll('.user-dropdown-item');
    if (dropdownItems.length) {
        dropdownItems.forEach((item, index) => {
            item.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const nextItem = dropdownItems[index + 1] || dropdownItems[0];
                    nextItem.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prevItem = dropdownItems[index - 1] || dropdownItems[dropdownItems.length - 1];
                    prevItem.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    userDropdownMenu.classList.remove('show');
                    userDropdownToggle.focus();
                }
            });
        });
    }
    
    // Course Progress Animation
    const progressBars = document.querySelectorAll('.progress-bar');
    if (progressBars.length) {
        progressBars.forEach(bar => {
            const width = bar.getAttribute('data-width') || '0';
            setTimeout(() => {
                bar.style.width = width + '%';
            }, 100);
        });
    }
    
    // Tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    if (tooltips.length) {
        tooltips.forEach(tooltip => {
            tooltip.setAttribute('role', 'button');
            tooltip.setAttribute('tabindex', '0');
            tooltip.setAttribute('aria-label', tooltip.getAttribute('data-tooltip'));
        });
    }
    
    // Dark Mode Toggle
    const darkModeToggle = document.querySelector('.dark-mode-toggle');
    if (darkModeToggle) {
        // Check for saved theme preference or use system preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark-theme');
            darkModeToggle.setAttribute('aria-pressed', 'true');
        }
        
        darkModeToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-theme');
            
            // Save preference
            if (document.documentElement.classList.contains('dark-theme')) {
                localStorage.setItem('theme', 'dark');
                darkModeToggle.setAttribute('aria-pressed', 'true');
            } else {
                localStorage.setItem('theme', 'light');
                darkModeToggle.setAttribute('aria-pressed', 'false');
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    
                    // Set focus to the target for accessibility
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                    target.removeAttribute('tabindex');
                }
            }
        });
    });
    
    // Handle form validation
    const forms = document.querySelectorAll('.needs-validation');
    if (forms.length) {
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    }
    
    // Initialize API data loading
    loadUserData();
    loadUserStats();
    loadInProgressCourses();
    loadRecommendedCourses();
});

// Function to load user data
function loadUserData() {
    // Try to load user data from server
    fetch('php/get_user_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store user data in localStorage for persistence across pages
                localStorage.setItem('userData', JSON.stringify({
                    name: data.name,
                    profile_picture: data.profile_picture
                }));
                
                // Update user interface with the data
                updateUserInterface(data.name, data.profile_picture);
            } else {
                console.error('Failed to load user data:', data.message);
                // Try to use cached data from localStorage
                tryLoadFromLocalStorage();
            }
        })
        .catch(error => {
            console.error('Error loading user data:', error);
            // Try to use cached data from localStorage
            tryLoadFromLocalStorage();
        });
}

// Helper function to update all user interface elements with user data
function updateUserInterface(name, profilePicture) {
    // Update user name in navbar and welcome message (if present)
    const userNameElements = document.querySelectorAll('.user-name');
    userNameElements.forEach(el => {
        el.textContent = name;
    });
    
    // Update welcome message with first name (if present)
    const welcomeNameEl = document.getElementById('welcomeName');
    if (welcomeNameEl) {
        welcomeNameEl.textContent = name.split(' ')[0];
    }
    
    // Update header username in profile page (if present)
    const headerUserNameEl = document.getElementById('headerUserName');
    if (headerUserNameEl) {
        headerUserNameEl.textContent = name;
    }
    
    // Update ALL profile pictures across the site
    const profilePicElements = document.querySelectorAll('.profile-pic');
    profilePicElements.forEach(el => {
        if (profilePicture) {
            el.src = profilePicture;
            el.alt = name;
        }
    });
    
    // Also update the header profile pic in profile page (if present)
    const headerProfilePicEl = document.getElementById('headerProfilePic');
    if (headerProfilePicEl && profilePicture) {
        headerProfilePicEl.src = profilePicture;
        headerProfilePicEl.alt = name;
    }
}

// Try to load user data from localStorage if server fetch fails
function tryLoadFromLocalStorage() {
    const cachedData = localStorage.getItem('userData');
    if (cachedData) {
        try {
            const userData = JSON.parse(cachedData);
            updateUserInterface(userData.name, userData.profile_picture);
        } catch (e) {
            console.error('Error parsing cached user data:', e);
        }
    }
}

// Function to load user stats
function loadUserStats() {
    fetch('php/get_user_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats cards
                const statsElements = {
                    'courses-enrolled': data.courses_enrolled || '0',
                    'hours-learned': data.hours_learned || '0',
                    'certificates': data.certificates || '0',
                    'completion-rate': (data.completion_rate || '0') + '%'
                };
                
                for (const [id, value] of Object.entries(statsElements)) {
                    const el = document.getElementById(id);
                    if (el) el.textContent = value;
                }
            } else {
                console.error('Failed to load user stats:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading user stats:', error);
        });
}

// Function to load in-progress courses
function loadInProgressCourses() {
    const container = document.getElementById('continue-watching-container');
    if (!container) return;
    
    // Add loading indicator
    const loadingEl = document.createElement('div');
    loadingEl.className = 'loading-indicator';
    loadingEl.innerHTML = '<div class="spinner"></div><p>Loading your courses...</p>';
    container.appendChild(loadingEl);
    
    fetch('php/get_in_progress_courses.php')
        .then(response => response.json())
        .then(data => {
            // Remove loading indicator
            container.removeChild(loadingEl);
            
            if (data.success && data.courses && data.courses.length > 0) {
                // Hide no courses message if it exists
                const noCoursesMessage = document.getElementById('no-courses-message');
                if (noCoursesMessage) {
                    noCoursesMessage.style.display = 'none';
                }
                
                // Create course cards
                data.courses.forEach(course => {
                    const courseCard = createCourseCard(course, true);
                    container.appendChild(courseCard);
                });
            } else {
                // Show no courses message
                const noCoursesMessage = document.getElementById('no-courses-message');
                if (!noCoursesMessage) {
                    const messageEl = document.createElement('div');
                    messageEl.id = 'no-courses-message';
                    messageEl.className = 'no-content-message';
                    messageEl.innerHTML = `
                        <p>You haven't started any courses yet. Explore our course library to get started!</p>
                        <a href="courses.html" class="btn btn-primary mt-3">Browse Courses</a>
                    `;
                    container.appendChild(messageEl);
                } else {
                    noCoursesMessage.style.display = 'block';
                }
            }
        })
        .catch(error => {
            // Remove loading indicator
            container.removeChild(loadingEl);
            
            // Show error message
            const errorEl = document.createElement('div');
            errorEl.className = 'alert alert-danger';
            errorEl.textContent = 'Failed to load courses. Please try again later.';
            container.appendChild(errorEl);
            
            console.error('Error loading in-progress courses:', error);
        });
}

// Function to load recommended courses
function loadRecommendedCourses() {
    const container = document.getElementById('recommended-courses-container');
    if (!container) return;
    
    // Add loading indicator
    const loadingEl = document.createElement('div');
    loadingEl.className = 'loading-indicator';
    loadingEl.innerHTML = '<div class="spinner"></div><p>Loading recommendations...</p>';
    container.appendChild(loadingEl);
    
    fetch('php/get_recommended_courses.php')
        .then(response => response.json())
        .then(data => {
            // Remove loading indicator
            container.removeChild(loadingEl);
            
            if (data.success && data.courses && data.courses.length > 0) {
                // Create course cards
                data.courses.forEach(course => {
                    const courseCard = createCourseCard(course, false);
                    container.appendChild(courseCard);
                });
            } else {
                // Show no recommendations message
                const messageEl = document.createElement('div');
                messageEl.className = 'no-content-message';
                messageEl.innerHTML = `
                    <p>No recommended courses available at the moment. Check back later!</p>
                `;
                container.appendChild(messageEl);
            }
        })
        .catch(error => {
            // Remove loading indicator
            container.removeChild(loadingEl);
            
            // Show error message
            const errorEl = document.createElement('div');
            errorEl.className = 'alert alert-danger';
            errorEl.textContent = 'Failed to load recommendations. Please try again later.';
            container.appendChild(errorEl);
            
            console.error('Error loading recommended courses:', error);
        });
}

// Helper function to create course cards
function createCourseCard(course, isInProgress) {
    const card = document.createElement('div');
    card.className = 'course-card';
    
    // Calculate progress percentage for in-progress courses
    const progressPercentage = isInProgress ? (course.progress || 0) : 0;
    const buttonText = isInProgress ? 'Continue' : 'View Course';
    
    card.innerHTML = `
        <img src="${course.image || 'images/course-default.jpg'}" alt="${course.title}" class="course-img">
        <div class="course-content">
            <h3 class="course-title">${course.title}</h3>
            <p class="course-description">${course.description}</p>
            <div class="course-meta">
                <span><i class="fas fa-video"></i> ${course.video_count || 0} videos</span>
                <span><i class="fas fa-clock"></i> ${course.duration || '0h'}</span>
            </div>
            ${isInProgress ? `
            <div class="course-progress">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" data-width="${progressPercentage}" 
                         style="width: 0%" aria-valuenow="${progressPercentage}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small>${progressPercentage}% complete</small>
                    <small>${course.completed_videos || 0}/${course.video_count || 0} videos</small>
                </div>
            </div>
            ` : ''}
            <a href="course.html?id=${course.id}" class="btn btn-primary">${buttonText}</a>
        </div>
    `;
    
    return card;
}
