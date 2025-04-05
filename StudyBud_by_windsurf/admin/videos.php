<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        // Add new video
        if ($_POST['action'] == 'add') {
            $course_id = $conn->real_escape_string($_POST['course_id']);
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $video_url = $conn->real_escape_string($_POST['video_url']);
            $duration = $conn->real_escape_string($_POST['duration']);
            
            // Handle thumbnail upload
            $thumbnail = "images/default-video.jpg";
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/videos')) {
                        mkdir('../uploads/videos', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/videos/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/videos/' . $new_filename;
                    }
                }
            }
            
            $sql = "INSERT INTO videos (course_id, title, description, video_url, thumbnail, duration) 
                    VALUES ('$course_id', '$title', '$description', '$video_url', '$thumbnail', '$duration')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Video added successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Update video
        else if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            $course_id = $conn->real_escape_string($_POST['course_id']);
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $video_url = $conn->real_escape_string($_POST['video_url']);
            $duration = $conn->real_escape_string($_POST['duration']);
            
            // Get current thumbnail
            $result = $conn->query("SELECT thumbnail FROM videos WHERE id = $id");
            $video = $result->fetch_assoc();
            $thumbnail = $video['thumbnail'];
            
            // Handle thumbnail upload
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/videos')) {
                        mkdir('../uploads/videos', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/videos/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/videos/' . $new_filename;
                    }
                }
            }
            
            $sql = "UPDATE videos SET 
                    course_id = '$course_id',
                    title = '$title', 
                    description = '$description', 
                    video_url = '$video_url',
                    thumbnail = '$thumbnail',
                    duration = '$duration'
                    WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Video updated successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Delete video
        else if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            
            // First delete any progress records for this video
            $conn->query("DELETE FROM user_video_progress WHERE video_id = $id");
            
            // Then delete the video
            $sql = "DELETE FROM videos WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Video deleted successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}

// Get all videos with course information
$sql = "SELECT v.*, c.title as course_title 
        FROM videos v 
        LEFT JOIN courses c ON v.course_id = c.id 
        ORDER BY v.created_at DESC";
$videos_result = $conn->query($sql);

// Get all courses for dropdown
$courses_result = $conn->query("SELECT * FROM courses ORDER BY title");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos Management - StudyBud Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
        }
        .sidebar-brand {
            height: 4.375rem;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            letter-spacing: 0.05rem;
            z-index: 1;
        }
        .sidebar-brand-text {
            color: white;
        }
        .nav-item {
            position: relative;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .nav-link:hover {
            color: white !important;
        }
        .nav-link i {
            margin-right: 0.25rem;
        }
        .active {
            color: white !important;
            font-weight: 700;
        }
        .content-wrapper {
            min-height: 100vh;
        }
        .topbar {
            height: 4.375rem;
        }
        .dropdown-menu {
            position: absolute;
            right: 0;
            left: auto;
        }
        .video-thumbnail {
            width: 120px;
            height: 68px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar col-md-2 col-lg-2 d-md-block">
            <div class="sidebar-brand d-flex align-items-center justify-content-center">
                <div class="sidebar-brand-text">StudyBud Admin</div>
            </div>
            <hr class="sidebar-divider my-0">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="courses.php">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Courses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="videos.php">
                        <i class="fas fa-fw fa-video"></i>
                        <span>Videos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-fw fa-book-open"></i>
                        <span>Books</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-fw fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <hr class="sidebar-divider d-none d-md-block">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-fw fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper col-md-10 col-lg-10 px-4">
            <!-- Topbar -->
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['admin_username']; ?></span>
                            <img class="img-profile rounded-circle" src="../images/default-avatar.png" width="32" height="32">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                            aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Begin Page Content -->
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Videos Management</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Add Video Button -->
                <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addVideoModal">
                    <i class="fas fa-plus"></i> Add New Video
                </button>
                
                <!-- Videos Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Videos</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Course</th>
                                        <th>Duration</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($videos_result->num_rows > 0): ?>
                                        <?php while ($video = $videos_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $video['id']; ?></td>
                                                <td>
                                                    <img src="../<?php echo $video['thumbnail']; ?>" alt="<?php echo $video['title']; ?>" class="video-thumbnail">
                                                </td>
                                                <td><?php echo $video['title']; ?></td>
                                                <td><?php echo $video['course_title']; ?></td>
                                                <td><?php echo $video['duration'] ? gmdate("H:i:s", $video['duration']) : 'N/A'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($video['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-video" 
                                                            data-id="<?php echo $video['id']; ?>"
                                                            data-title="<?php echo $video['title']; ?>"
                                                            data-description="<?php echo $video['description']; ?>"
                                                            data-course="<?php echo $video['course_id']; ?>"
                                                            data-url="<?php echo $video['video_url']; ?>"
                                                            data-duration="<?php echo $video['duration']; ?>"
                                                            data-toggle="modal" data-target="#editVideoModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-video"
                                                            data-id="<?php echo $video['id']; ?>"
                                                            data-title="<?php echo $video['title']; ?>"
                                                            data-toggle="modal" data-target="#deleteVideoModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No videos found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Video Modal -->
    <div class="modal fade" id="addVideoModal" tabindex="-1" role="dialog" aria-labelledby="addVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVideoModalLabel">Add New Video</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                <?php if ($courses_result->num_rows > 0): ?>
                                    <?php while ($course = $courses_result->fetch_assoc()): ?>
                                        <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Video Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="video_url">Video URL</label>
                            <input type="text" class="form-control" id="video_url" name="video_url" required>
                            <small class="form-text text-muted">YouTube, Vimeo, or direct video file URL</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Duration (seconds)</label>
                            <input type="number" class="form-control" id="duration" name="duration" min="0">
                            <small class="form-text text-muted">Duration in seconds (optional)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" class="form-control-file" id="thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Recommended size: 1280x720 pixels</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Video Modal -->
    <div class="modal fade" id="editVideoModal" tabindex="-1" role="dialog" aria-labelledby="editVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editVideoModalLabel">Edit Video</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label for="edit_course_id">Course</label>
                            <select class="form-control" id="edit_course_id" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                <?php 
                                $courses_result = $conn->query("SELECT * FROM courses ORDER BY title");
                                if ($courses_result->num_rows > 0): 
                                    while ($course = $courses_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $course['id']; ?>"><?php echo $course['title']; ?></option>
                                <?php 
                                    endwhile; 
                                endif; 
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_title">Video Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_video_url">Video URL</label>
                            <input type="text" class="form-control" id="edit_video_url" name="video_url" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_duration">Duration (seconds)</label>
                            <input type="number" class="form-control" id="edit_duration" name="duration" min="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_thumbnail">Thumbnail</label>
                            <input type="file" class="form-control-file" id="edit_thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Leave empty to keep current thumbnail</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Video Modal -->
    <div class="modal fade" id="deleteVideoModal" tabindex="-1" role="dialog" aria-labelledby="deleteVideoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteVideoModalLabel">Delete Video</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the video: <strong id="delete_title"></strong>?</p>
                        <p class="text-danger">This action cannot be undone. All associated data will be lost.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Video</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Edit video modal
            $('.edit-video').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                var course = $(this).data('course');
                var url = $(this).data('url');
                var duration = $(this).data('duration');
                
                $('#edit_id').val(id);
                $('#edit_title').val(title);
                $('#edit_description').val(description);
                $('#edit_course_id').val(course);
                $('#edit_video_url').val(url);
                $('#edit_duration').val(duration);
            });
            
            // Delete video modal
            $('.delete-video').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                $('#delete_id').val(id);
                $('#delete_title').text(title);
            });
        });
    </script>
</body>
</html>
