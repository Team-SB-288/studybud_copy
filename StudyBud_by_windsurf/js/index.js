document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');

    searchButton.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        if (searchTerm) {
            window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
        }
    });

    // Continue watching section
    const continueWatchingGrid = document.querySelector('.continue-watching .video-grid');

    // Fetch continue watching videos from server
    fetchContinueWatchingVideos();

    function fetchContinueWatchingVideos() {
        fetch('php/get_continue_watching.php')
            .then(response => response.json())
            .then(videos => {
                continueWatchingGrid.innerHTML = videos.map(video => `
                    <div class="video-card">
                        <img src="${video.thumbnail}" alt="${video.title}">
                        <div class="video-info">
                            <h3>${video.title}</h3>
                            <p>${video.duration} mins</p>
                            <a href="video.php?id=${video.id}" class="btn-continue">Continue Watching</a>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => console.error('Error fetching continue watching videos:', error));
    }

    // Smooth scroll for navigation links
    document.querySelectorAll('nav a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    // Add hover effect to course cards
    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });
});
