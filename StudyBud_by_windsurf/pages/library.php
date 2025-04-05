<?php
/**
 * StudyBud Main Page
 * Converted from HTML to PHP
 */

// Include configuration
require_once __DIR__ . '/../../config/config.php';
require_once UTILS_PATH . '/session.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ' . get_url('pages/auth/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Your personal learning library">
    <title>StudyBud - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/modern-style.css">
    <script src="<?php echo JS_URL; ?>/modern-script.js" defer></script>
    <style>
        /* Library-specific styles */
        .search-bar {
            display: flex;
            align-items: center;
            max-width: 400px;
            margin-left: auto;
        }
        
        .search-bar input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius-sm) 0 0 var(--border-radius-sm);
            font-size: 0.875rem;
            width: 100%;
        }
        
        .search-bar button {
            padding: 0.75rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0 var(--border-radius-sm) var(--border-radius-sm) 0;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .search-bar button:hover {
            background: var(--primary-dark);
        }
        
        .tabs {
            display: flex;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }
        
        .tab {
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            transition: var(--transition);
            color: var(--text-muted);
        }
        
        .tab.active {
            color: var(--primary-color);
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
        }
        
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .video-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
        }
        
        .video-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }
        
        .thumbnail {
            position: relative;
            width: 100%;
            height: 160px;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 3.125rem;
            height: 3.125rem;
            background: rgba(var(--primary-color-rgb), 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            transition: var(--transition);
        }
        
        .video-card:hover .play-button {
            background: rgba(var(--primary-color-rgb), 1);
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .duration {
            position: absolute;
            bottom: 0.625rem;
            right: 0.625rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: var(--border-radius-sm);
            font-size: 0.75rem;
        }
        
        .video-info {
            padding: 1.25rem;
        }
        
        .video-info h3 {
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            font-size: 1rem;
            font-weight: 600;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: 0.8125rem;
            margin-bottom: 0.75rem;
        }
        
        .progress-container {
            height: 0.25rem;
            background: var(--border-color);
            border-radius: var(--border-radius-sm);
            margin-top: 0.625rem;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--primary-color);
            border-radius: var(--border-radius-sm);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #666;
        }
        
        .empty-state h2 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .empty-state p {
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-browse {
            display: inline-block;
            padding: 0.625rem 1.25rem;
            background: var(--primary-color);
            color: white;
            border-radius: var(--border-radius-sm);
            text-decoration: none;
            font-weight: 500;
            margin-top: 1.875rem;
            transition: var(--transition);
        }
        
        .btn-browse:hover {
            background: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Mobile Navigation Backdrop -->
    <div class="backdrop"></div>
    
    <!-- Main Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="StudyBud">
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
                    <a href="<?php echo get_url('pages/main/dashboard.php'); ?>" class="nav-link">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="nav-link">
                        <i class="fas fa-graduation-cap"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo get_url('pages/main/library.php'); ?>" class="nav-link active" aria-current="page">
                        <i class="fas fa-book"></i> Library
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.html" class="nav-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- User Dropdown -->
        <div class="user-dropdown">
            <div class="user-dropdown-toggle" tabindex="0" aria-haspopup="true" aria-expanded="false">
                <img src="<?php echo IMAGES_URL; ?>/default-profile.png" alt="Profile" class="profile-pic" id="userProfilePic">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-dropdown-menu" aria-label="User menu">
                <a href="profile.html" class="user-dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="settings.html" class="user-dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="logout.php" class="user-dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb Navigation -->
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">StudyBud</a></li>
                <li class="breadcrumb-item active" aria-current="page">Library</li>
            </ol>
        </nav>
    </div>

    <main class="container">
        <div class="header">
            <h1>My Library</h1>
            <div class="search-bar">
                <input type="text" placeholder="Search in library...">
                <button>Search</button>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active">Saved Videos</div>
            <div class="tab">Continue Watching</div>
            <div class="tab">Watch History</div>
            <div class="tab">Completed</div>
        </div>

        <div class="tab-content">
            <!-- Saved Videos Tab -->
            <div class="video-grid">
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">12:34</div>
                    </div>
                    <div class="video-info">
                        <h3>Introduction to HTML and CSS</h3>
                        <div class="video-meta">
                            <span>Web Development</span>
                            <span>Saved 2 days ago</span>
                        </div>
                    </div>
                </div>
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">18:22</div>
                    </div>
                    <div class="video-info">
                        <h3>Python Data Structures</h3>
                        <div class="video-meta">
                            <span>Python Programming</span>
                            <span>Saved 1 week ago</span>
                        </div>
                    </div>
                </div>
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">15:45</div>
                    </div>
                    <div class="video-info">
                        <h3>Data Visualization with Matplotlib</h3>
                        <div class="video-meta">
                            <span>Data Science</span>
                            <span>Saved 3 days ago</span>
                        </div>
                    </div>
                </div>
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">20:18</div>
                    </div>
                    <div class="video-info">
                        <h3>UI Design Principles</h3>
                        <div class="video-meta">
                            <span>UI/UX Design</span>
                            <span>Saved yesterday</span>
                        </div>
                    </div>
                </div>
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">14:56</div>
                    </div>
                    <div class="video-info">
                        <h3>React Components and Props</h3>
                        <div class="video-meta">
                            <span>JavaScript Frameworks</span>
                            <span>Saved 5 days ago</span>
                        </div>
                    </div>
                </div>
                <div class="video-card">
                    <div class="thumbnail">
                        <img src="https://via.placeholder.com/300x160" alt="Video">
                        <div class="play-button">▶</div>
                        <div class="duration">16:30</div>
                    </div>
                    <div class="video-info">
                        <h3>Cloud Deployment Strategies</h3>
                        <div class="video-meta">
                            <span>Cloud Computing</span>
                            <span>Saved 1 week ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State (hidden by default) -->
            <div class="empty-state" style="display: none;">
                <h2>Your library is empty</h2>
                <p>Save videos to your library to watch them later or keep track of your favorite content.</p>
                <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="btn-browse">Browse Courses</a>
            </div>
        </div>
    </main>

    <script>
        // Simple tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // For demo purposes, we'll show the empty state when clicking on "Completed" tab
                const emptyState = document.querySelector('.empty-state');
                const videoGrid = document.querySelector('.video-grid');
                
                if (this.textContent === 'Completed') {
                    emptyState.style.display = 'block';
                    videoGrid.style.display = 'none';
                } else {
                    emptyState.style.display = 'none';
                    videoGrid.style.display = 'grid';
                }
            });
        });
    </script>
    <script>
        // Load user data from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const userName = localStorage.getItem('userName');
            const userPicture = localStorage.getItem('userPicture');
            
            // Update user name if available
            if (userName) {
                document.getElementById('userName').textContent = userName;
            }
            
            // Update profile picture if available
            if (userPicture) {
                document.getElementById('userProfilePic').src = userPicture;
            }
        });
    </script>
</body>
</html>
