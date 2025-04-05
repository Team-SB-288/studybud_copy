// Add smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Handle profile dropdown
document.querySelector('.profile-dropdown').addEventListener('click', function(e) {
    e.stopPropagation();
    this.classList.toggle('active');
});

document.addEventListener('click', function() {
    const dropdown = document.querySelector('.profile-dropdown');
    if (dropdown && dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
    }
});

// Add animation to course cards when they come into view
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
        }
    });
}, {
    threshold: 0.1
});

document.querySelectorAll('.course-card').forEach(card => {
    observer.observe(card);
});

// Handle notifications
document.querySelector('.notifications').addEventListener('click', function() {
    // Add your notification handling logic here
    console.log('Notifications clicked');
});

// Add loading animation for course cards
function addLoadingAnimation() {
    const courseGrid = document.querySelector('.course-grid');
    if (courseGrid) {
        const loadingHTML = `
            <div class="loading-card">
                <div class="loading-image"></div>
                <div class="loading-info">
                    <div class="loading-title"></div>
                    <div class="loading-stats"></div>
                </div>
            </div>
        `;
        
        // Add loading cards while content loads
        for (let i = 0; i < 3; i++) {
            courseGrid.innerHTML += loadingHTML;
        }
    }
}

// Initialize loading animation
addLoadingAnimation();

// Remove loading animation when content is loaded
window.addEventListener('load', function() {
    const loadingCards = document.querySelectorAll('.loading-card');
    loadingCards.forEach(card => card.remove());
});

// Add scroll to top button
const scrollButton = document.createElement('button');
scrollButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
scrollButton.className = 'scroll-top';
scrollButton.style.cssText = `
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: #3498db;
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    cursor: pointer;
    display: none;
    z-index: 1000;
    transition: opacity 0.3s ease;
`;

document.body.appendChild(scrollButton);

// Show/hide scroll button
window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollButton.style.display = 'block';
    } else {
        scrollButton.style.display = 'none';
    }
});

// Scroll to top functionality
scrollButton.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});

// Add smooth fade-in animation for sections
const sections = document.querySelectorAll('section');
sections.forEach(section => {
    section.style.opacity = '0';
    section.style.transform = 'translateY(20px)';
    section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
});

// Animate sections when they come into view
const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, {
    threshold: 0.1
});

sections.forEach(section => sectionObserver.observe(section));
