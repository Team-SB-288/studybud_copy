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
        // Add new course
        if ($_POST['action'] == 'add') {
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $category_id = $conn->real_escape_string($_POST['category_id']);
            
            // Handle thumbnail upload
            $thumbnail = "images/default-course.jpg";
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/courses')) {
                        mkdir('../uploads/courses', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/courses/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/courses/' . $new_filename;
                    }
                }
            }
            
            $sql = "INSERT INTO courses (title, description, thumbnail, category_id) 
                    VALUES ('$title', '$description', '$thumbnail', '$category_id')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Course added successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Update course
        else if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            $title = $conn->real_escape_string($_POST['title']);
            $description = $conn->real_escape_string($_POST['description']);
            $category_id = $conn->real_escape_string($_POST['category_id']);
            
            // Get current thumbnail
            $result = $conn->query("SELECT thumbnail FROM courses WHERE id = $id");
            $course = $result->fetch_assoc();
            $thumbnail = $course['thumbnail'];
            
            // Handle thumbnail upload
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/courses')) {
                        mkdir('../uploads/courses', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/courses/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/courses/' . $new_filename;
                    }
                }
            }
            
            $sql = "UPDATE courses SET 
                    title = '$title', 
                    description = '$description', 
                    thumbnail = '$thumbnail',
                    category_id = '$category_id'
                    WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Course updated successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Delete course
        else if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            
            // First check if there are videos associated with this course
            $result = $conn->query("SELECT COUNT(*) as count FROM videos WHERE course_id = $id");
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                $error_message = "Cannot delete course because it has videos associated with it. Delete the videos first.";
            } else {
                $sql = "DELETE FROM courses WHERE id = $id";
                
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Course deleted successfully!";
                } else {
                    $error_message = "Error: " . $conn->error;
                }
            }
        }
    }
}

// Get all courses
$sql = "SELECT c.*, cat.name as category_name 
        FROM courses c 
        LEFT JOIN categories cat ON c.category_id = cat.id 
        ORDER BY c.created_at DESC";
$courses_result = $conn->query($sql);

// Get all categories for dropdown
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses Management - StudyBud Admin</title>
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
        .course-thumbnail {
            width: 100px;
            height: 60px;
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
                    <a class="nav-link active" href="courses.php">
                        <i class="fas fa-fw fa-book"></i>
                        <span>Courses</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="videos.php">
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
                <h1 class="h3 mb-4 text-gray-800">Courses Management</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Add Course Button -->
                <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addCourseModal">
                    <i class="fas fa-plus"></i> Add New Course
                </button>
                
                <!-- Courses Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Courses</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Thumbnail</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($courses_result->num_rows > 0): ?>
                                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $course['id']; ?></td>
                                                <td>
                                                    <img src="../<?php echo $course['thumbnail']; ?>" alt="<?php echo $course['title']; ?>" class="course-thumbnail">
                                                </td>
                                                <td><?php echo $course['title']; ?></td>
                                                <td><?php echo $course['category_name'] ?? 'Uncategorized'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-course" 
                                                            data-id="<?php echo $course['id']; ?>"
                                                            data-title="<?php echo $course['title']; ?>"
                                                            data-description="<?php echo $course['description']; ?>"
                                                            data-category="<?php echo $course['category_id']; ?>"
                                                            data-toggle="modal" data-target="#editCourseModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-course"
                                                            data-id="<?php echo $course['id']; ?>"
                                                            data-title="<?php echo $course['title']; ?>"
                                                            data-toggle="modal" data-target="#deleteCourseModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No courses found</td>
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
    
    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" role="dialog" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="title">Course Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php if ($categories_result->num_rows > 0): ?>
                                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="thumbnail">Thumbnail</label>
                            <input type="file" class="form-control-file" id="thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Recommended size: 1280x720 pixels</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1" role="dialog" aria-labelledby="editCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label for="edit_title">Course Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_category_id">Category</label>
                            <select class="form-control" id="edit_category_id" name="category_id">
                                <option value="">-- Select Category --</option>
                                <?php 
                                $categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
                                if ($categories_result->num_rows > 0): 
                                    while ($category = $categories_result->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php 
                                    endwhile; 
                                endif; 
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_thumbnail">Thumbnail</label>
                            <input type="file" class="form-control-file" id="edit_thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Leave empty to keep current thumbnail</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Course Modal -->
    <div class="modal fade" id="deleteCourseModal" tabindex="-1" role="dialog" aria-labelledby="deleteCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCourseModalLabel">Delete Course</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the course: <strong id="delete_title"></strong>?</p>
                        <p class="text-danger">This action cannot be undone. All associated data will be lost.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Course</button>
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
            // Edit course modal
            $('.edit-course').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var description = $(this).data('description');
                var category = $(this).data('category');
                
                $('#edit_id').val(id);
                $('#edit_title').val(title);
                $('#edit_description').val(description);
                $('#edit_category_id').val(category);
            });
            
            // Delete course modal
            $('.delete-course').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                $('#delete_id').val(id);
                $('#delete_title').text(title);
            });
        });
    </script>
</body>
</html>
