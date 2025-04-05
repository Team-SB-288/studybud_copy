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
        // Add new book
        if ($_POST['action'] == 'add') {
            $title = $conn->real_escape_string($_POST['title']);
            $author = $conn->real_escape_string($_POST['author']);
            $description = $conn->real_escape_string($_POST['description']);
            $category_id = $conn->real_escape_string($_POST['category_id']);
            
            // Handle thumbnail upload
            $thumbnail = "images/default-book.jpg";
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/books/covers')) {
                        mkdir('../uploads/books/covers', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/books/covers/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/books/covers/' . $new_filename;
                    }
                }
            }
            
            // Handle file upload
            $file_url = "";
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $allowed = ['pdf', 'epub', 'mobi', 'doc', 'docx'];
                $filename = $_FILES['file']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/books/files')) {
                        mkdir('../uploads/books/files', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/books/files/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                        $file_url = 'uploads/books/files/' . $new_filename;
                    }
                }
            }
            
            $sql = "INSERT INTO books (title, author, description, category_id, thumbnail, file_url) 
                    VALUES ('$title', '$author', '$description', '$category_id', '$thumbnail', '$file_url')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Book added successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Update book
        else if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            $title = $conn->real_escape_string($_POST['title']);
            $author = $conn->real_escape_string($_POST['author']);
            $description = $conn->real_escape_string($_POST['description']);
            $category_id = $conn->real_escape_string($_POST['category_id']);
            
            // Get current thumbnail and file
            $result = $conn->query("SELECT thumbnail, file_url FROM books WHERE id = $id");
            $book = $result->fetch_assoc();
            $thumbnail = $book['thumbnail'];
            $file_url = $book['file_url'];
            
            // Handle thumbnail upload
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['thumbnail']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/books/covers')) {
                        mkdir('../uploads/books/covers', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/books/covers/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $upload_path)) {
                        $thumbnail = 'uploads/books/covers/' . $new_filename;
                    }
                }
            }
            
            // Handle file upload
            if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
                $allowed = ['pdf', 'epub', 'mobi', 'doc', 'docx'];
                $filename = $_FILES['file']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    if (!file_exists('../uploads/books/files')) {
                        mkdir('../uploads/books/files', 0777, true);
                    }
                    
                    $new_filename = uniqid() . '.' . $filetype;
                    $upload_path = '../uploads/books/files/' . $new_filename;
                    
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                        $file_url = 'uploads/books/files/' . $new_filename;
                    }
                }
            }
            
            $sql = "UPDATE books SET 
                    title = '$title', 
                    author = '$author',
                    description = '$description', 
                    category_id = '$category_id',
                    thumbnail = '$thumbnail',
                    file_url = '$file_url'
                    WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Book updated successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Delete book
        else if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            
            $sql = "DELETE FROM books WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Book deleted successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}

// Get all books with category information
$sql = "SELECT b.*, c.name as category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        ORDER BY b.created_at DESC";
$books_result = $conn->query($sql);

// Get all categories for dropdown
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books Management - StudyBud Admin</title>
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
        .book-thumbnail {
            width: 80px;
            height: 120px;
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
                    <a class="nav-link" href="videos.php">
                        <i class="fas fa-fw fa-video"></i>
                        <span>Videos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="books.php">
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
                <h1 class="h3 mb-4 text-gray-800">Books Management</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Add Book Button -->
                <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addBookModal">
                    <i class="fas fa-plus"></i> Add New Book
                </button>
                
                <!-- Books Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Books</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cover</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Category</th>
                                        <th>File</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($books_result->num_rows > 0): ?>
                                        <?php while ($book = $books_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $book['id']; ?></td>
                                                <td>
                                                    <img src="../<?php echo $book['thumbnail']; ?>" alt="<?php echo $book['title']; ?>" class="book-thumbnail">
                                                </td>
                                                <td><?php echo $book['title']; ?></td>
                                                <td><?php echo $book['author']; ?></td>
                                                <td><?php echo $book['category_name'] ?? 'Uncategorized'; ?></td>
                                                <td>
                                                    <?php if ($book['file_url']): ?>
                                                        <a href="../<?php echo $book['file_url']; ?>" target="_blank" class="btn btn-sm btn-success">
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">No file</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($book['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-book" 
                                                            data-id="<?php echo $book['id']; ?>"
                                                            data-title="<?php echo $book['title']; ?>"
                                                            data-author="<?php echo $book['author']; ?>"
                                                            data-description="<?php echo $book['description']; ?>"
                                                            data-category="<?php echo $book['category_id']; ?>"
                                                            data-toggle="modal" data-target="#editBookModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-book"
                                                            data-id="<?php echo $book['id']; ?>"
                                                            data-title="<?php echo $book['title']; ?>"
                                                            data-toggle="modal" data-target="#deleteBookModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No books found</td>
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
    
    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1" role="dialog" aria-labelledby="addBookModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="title">Book Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" class="form-control" id="author" name="author">
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
                            <label for="thumbnail">Cover Image</label>
                            <input type="file" class="form-control-file" id="thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Recommended size: 400x600 pixels</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="file">Book File</label>
                            <input type="file" class="form-control-file" id="file" name="file">
                            <small class="form-text text-muted">Supported formats: PDF, EPUB, MOBI, DOC, DOCX</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Book Modal -->
    <div class="modal fade" id="editBookModal" tabindex="-1" role="dialog" aria-labelledby="editBookModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookModalLabel">Edit Book</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label for="edit_title">Book Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_author">Author</label>
                            <input type="text" class="form-control" id="edit_author" name="author">
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
                            <label for="edit_thumbnail">Cover Image</label>
                            <input type="file" class="form-control-file" id="edit_thumbnail" name="thumbnail">
                            <small class="form-text text-muted">Leave empty to keep current cover</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_file">Book File</label>
                            <input type="file" class="form-control-file" id="edit_file" name="file">
                            <small class="form-text text-muted">Leave empty to keep current file</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Book Modal -->
    <div class="modal fade" id="deleteBookModal" tabindex="-1" role="dialog" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBookModalLabel">Delete Book</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the book: <strong id="delete_title"></strong>?</p>
                        <p class="text-danger">This action cannot be undone. All associated data will be lost.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Book</button>
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
            // Edit book modal
            $('.edit-book').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var author = $(this).data('author');
                var description = $(this).data('description');
                var category = $(this).data('category');
                
                $('#edit_id').val(id);
                $('#edit_title').val(title);
                $('#edit_author').val(author);
                $('#edit_description').val(description);
                $('#edit_category_id').val(category);
            });
            
            // Delete book modal
            $('.delete-book').click(function() {
                var id = $(this).data('id');
                var title = $(this).data('title');
                
                $('#delete_id').val(id);
                $('#delete_title').text(title);
            });
        });
    </script>
</body>
</html>
