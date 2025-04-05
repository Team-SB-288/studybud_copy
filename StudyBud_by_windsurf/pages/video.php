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
    <title>Study Bud - Video Player</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
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
            color: #333;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .breadcrumbs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .breadcrumbs a {
            color: #666;
            text-decoration: none;
        }
        
        .breadcrumbs a:hover {
            color: #764ba2;
        }
        
        .video-container {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 30px;
        }
        
        .video-main {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .video-player {
            width: 100%;
            background: #000;
            position: relative;
        }
        
        .video-player video {
            width: 100%;
            height: auto;
            display: block;
        }
        
        .video-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: opacity 0.3s;
            opacity: 0;
        }
        
        .video-player:hover .video-controls {
            opacity: 1;
        }
        
        .play-pause, .volume, .fullscreen {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .progress-container {
            flex: 1;
            height: 5px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
        }
        
        .progress-bar {
            height: 100%;
            background: #764ba2;
            width: 30%;
        }
        
        .time-display {
            color: white;
            font-size: 14px;
            min-width: 80px;
            text-align: center;
        }
        
        .video-info {
            padding: 20px;
        }
        
        .video-info h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .video-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .meta-left {
            color: #666;
            font-size: 14px;
        }
        
        .meta-right {
            display: flex;
            gap: 15px;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .action-btn:hover {
            color: #764ba2;
        }
        
        .video-description {
            line-height: 1.6;
            color: #444;
            margin-bottom: 20px;
        }
        
        .instructor-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .instructor-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .instructor-details h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .instructor-details p {
            font-size: 14px;
            color: #666;
        }
        
        .video-sidebar {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .sidebar-header {
            padding: 15px 20px;
            background: #764ba2;
            color: white;
        }
        
        .sidebar-header h2 {
            font-size: 18px;
        }
        
        .playlist {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .playlist-item {
            display: flex;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .playlist-item:hover, .playlist-item.active {
            background: #f5f7fa;
        }
        
        .playlist-item.active {
            border-left: 3px solid #764ba2;
        }
        
        .video-number {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 50%;
            margin-right: 15px;
            font-size: 14px;
            color: #333;
        }
        
        .playlist-item.active .video-number {
            background: #764ba2;
            color: white;
        }
        
        .playlist-item-info {
            flex: 1;
        }
        
        .playlist-item-title {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .playlist-item-meta {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #666;
        }
        
        .notes-section {
            margin-top: 30px;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .add-note {
            font-size: 14px;
            color: #764ba2;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .notes-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .note-card {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 15px;
            border-left: 3px solid #764ba2;
        }
        
        .note-timestamp {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .note-content {
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }
        
        .note-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }
        
        .note-btn {
            background: none;
            border: none;
            font-size: 12px;
            color: #666;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .note-btn:hover {
            color: #764ba2;
        }
        
        .note-form {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .note-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }
        
        .note-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .note-form-btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .save-note {
            background: #764ba2;
            color: white;
            border: none;
        }
        
        .cancel-note {
            background: transparent;
            color: #666;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="Study Bud">
        </div>
        <div class="nav-links">
            <a href="<?php echo get_url('pages/main/dashboard.php'); ?>">Home</a>
            <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="active">Courses</a>
            <a href="<?php echo get_url('pages/main/library.php'); ?>">Library</a>
            <a href="profile.html">Profile</a>
        </div>
        <div class="user-info">
            <img src="https://via.placeholder.com/40" alt="Profile" class="profile-pic">
            <span>John Doe</span>
        </div>
    </nav>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?php echo get_url('pages/main/dashboard.php'); ?>">Home</a> &gt;
            <a href="<?php echo get_url('pages/main/courses.php'); ?>">Courses</a> &gt;
            <a href="<?php echo get_url('pages/main/course.php'); ?>">Web Development</a> &gt;
            <span>HTML Document Structure</span>
        </div>

        <div class="video-container">
            <div class="video-main">
                <div class="video-player">
                    <video poster="https://via.placeholder.com/800x450" controls>
                        <source src="#" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-controls">
                        <button class="play-pause">▶</button>
                        <div class="progress-container">
                            <div class="progress-bar"></div>
                        </div>
                        <div class="time-display">0:00 / 22:45</div>
                        <button class="volume">🔊</button>
                        <button class="fullscreen">⛶</button>
                    </div>
                </div>
                <div class="video-info">
                    <h1>1.2 HTML Document Structure</h1>
                    <div class="video-meta">
                        <div class="meta-left">
                            <span>22:45 • March 15, 2025</span>
                        </div>
                        <div class="meta-right">
                            <button class="action-btn">👍 Like</button>
                            <button class="action-btn">💾 Save</button>
                            <button class="action-btn">📝 Take Notes</button>
                            <button class="action-btn">📤 Share</button>
                        </div>
                    </div>
                    <div class="video-description">
                        <p>In this video, we'll explore the fundamental structure of an HTML document. You'll learn about the DOCTYPE declaration, HTML, head, and body elements, and how they work together to create a properly structured web page.</p>
                        <p>We'll also cover important meta tags, the title element, and how to link external resources like CSS stylesheets to your HTML document.</p>
                    </div>
                    <div class="instructor-info">
                        <img src="https://via.placeholder.com/50" alt="Instructor" class="instructor-image">
                        <div class="instructor-details">
                            <h3>Sarah Johnson</h3>
                            <p>Web Development Instructor</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="video-sidebar">
                <div class="sidebar-header">
                    <h2>Course Content</h2>
                </div>
                <div class="playlist">
                    <div class="playlist-item">
                        <div class="video-number">1</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">1.1 What is HTML?</div>
                            <div class="playlist-item-meta">
                                <span>15:30</span>
                                <span>Completed</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item active">
                        <div class="video-number">2</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">1.2 HTML Document Structure</div>
                            <div class="playlist-item-meta">
                                <span>22:45</span>
                                <span>Currently watching</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">3</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">1.3 HTML Elements and Attributes</div>
                            <div class="playlist-item-meta">
                                <span>28:15</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">4</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">1.4 HTML Forms and Input Elements</div>
                            <div class="playlist-item-meta">
                                <span>24:20</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">5</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">2.1 Introduction to CSS</div>
                            <div class="playlist-item-meta">
                                <span>18:45</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">6</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">2.2 CSS Selectors and Properties</div>
                            <div class="playlist-item-meta">
                                <span>32:10</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">7</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">2.3 CSS Box Model and Layout</div>
                            <div class="playlist-item-meta">
                                <span>35:20</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                    <div class="playlist-item">
                        <div class="video-number">8</div>
                        <div class="playlist-item-info">
                            <div class="playlist-item-title">2.4 Responsive Design with CSS</div>
                            <div class="playlist-item-meta">
                                <span>33:45</span>
                                <span>Not started</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="notes-section">
            <div class="section-title">
                <h2>My Notes</h2>
                <div class="add-note" id="toggleNoteForm">+ Add Note</div>
            </div>
            
            <div class="note-form" id="noteForm" style="display: none;">
                <textarea placeholder="Write your note here..."></textarea>
                <div class="note-form-actions">
                    <button class="note-form-btn cancel-note" id="cancelNote">Cancel</button>
                    <button class="note-form-btn save-note" id="saveNote">Save Note</button>
                </div>
            </div>
            
            <div class="notes-container">
                <div class="note-card">
                    <div class="note-timestamp">At 5:23 - March 15, 2025</div>
                    <div class="note-content">The DOCTYPE declaration is important for telling browsers which version of HTML you're using. For HTML5, it's simply <!DOCTYPE html></div>
                    <div class="note-actions">
                        <button class="note-btn">Edit</button>
                        <button class="note-btn">Delete</button>
                    </div>
                </div>
                <div class="note-card">
                    <div class="note-timestamp">At 12:45 - March 15, 2025</div>
                    <div class="note-content">Remember to include meta viewport tag for responsive design: <meta name="viewport" content="width=device-width, initial-scale=1.0"></div>
                    <div class="note-actions">
                        <button class="note-btn">Edit</button>
                        <button class="note-btn">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Toggle note form
        document.getElementById('toggleNoteForm').addEventListener('click', function() {
            const noteForm = document.getElementById('noteForm');
            noteForm.style.display = noteForm.style.display === 'none' ? 'block' : 'none';
        });
        
        document.getElementById('cancelNote').addEventListener('click', function() {
            document.getElementById('noteForm').style.display = 'none';
        });
        
        document.getElementById('saveNote').addEventListener('click', function() {
            const noteText = document.querySelector('.note-form textarea').value;
            if (noteText.trim()) {
                // Create new note
                const notesContainer = document.querySelector('.notes-container');
                
                const noteCard = document.createElement('div');
                noteCard.className = 'note-card';
                
                const timestamp = document.createElement('div');
                timestamp.className = 'note-timestamp';
                timestamp.textContent = `At ${getCurrentTime()} - ${getCurrentDate()}`;
                
                const content = document.createElement('div');
                content.className = 'note-content';
                content.textContent = noteText;
                
                const actions = document.createElement('div');
                actions.className = 'note-actions';
                actions.innerHTML = `
                    <button class="note-btn">Edit</button>
                    <button class="note-btn">Delete</button>
                `;
                
                noteCard.appendChild(timestamp);
                noteCard.appendChild(content);
                noteCard.appendChild(actions);
                
                notesContainer.prepend(noteCard);
                
                // Clear and hide form
                document.querySelector('.note-form textarea').value = '';
                document.getElementById('noteForm').style.display = 'none';
            }
        });
        
        // Helper functions
        function getCurrentTime() {
            const video = document.querySelector('video');
            const currentTime = video.currentTime || 0;
            const minutes = Math.floor(currentTime / 60);
            const seconds = Math.floor(currentTime % 60);
            return `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
        }
        
        function getCurrentDate() {
            const date = new Date();
            const options = { month: 'long', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
        // Playlist item click
        document.querySelectorAll('.playlist-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.playlist-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
