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
        // Add new category
        if ($_POST['action'] == 'add') {
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            
            $sql = "INSERT INTO categories (name, description) VALUES ('$name', '$description')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Category added successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Update category
        else if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            
            $sql = "UPDATE categories SET name = '$name', description = '$description' WHERE id = $id";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Category updated successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
        
        // Delete category
        else if ($_POST['action'] == 'delete' && isset($_POST['id'])) {
            $id = $conn->real_escape_string($_POST['id']);
            
            // Check if category is being used by courses or books
            $courses_count = $conn->query("SELECT COUNT(*) as count FROM courses WHERE category_id = $id")->fetch_assoc()['count'];
            $books_count = $conn->query("SELECT COUNT(*) as count FROM books WHERE category_id = $id")->fetch_assoc()['count'];
            
            if ($courses_count > 0 || $books_count > 0) {
                $error_message = "Cannot delete category because it is being used by courses or books.";
            } else {
                $sql = "DELETE FROM categories WHERE id = $id";
                
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Category deleted successfully!";
                } else {
                    $error_message = "Error: " . $conn->error;
                }
            }
        }
    }
}

// Get all categories
$sql = "SELECT c.*, 
        (SELECT COUNT(*) FROM courses WHERE category_id = c.id) as courses_count,
        (SELECT COUNT(*) FROM books WHERE category_id = c.id) as books_count
        FROM categories c
        ORDER BY c.name";
$categories_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - StudyBud Admin</title>
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
                    <a class="nav-link" href="books.php">
                        <i class="fas fa-fw fa-book-open"></i>
                        <span>Books</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="categories.php">
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
                <h1 class="h3 mb-4 text-gray-800">Categories Management</h1>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Add Category Button -->
                <button class="btn btn-primary mb-4" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
                
                <!-- Categories Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">All Categories</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Courses</th>
                                        <th>Books</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($categories_result->num_rows > 0): ?>
                                        <?php while ($category = $categories_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td><?php echo $category['name']; ?></td>
                                                <td><?php echo $category['description']; ?></td>
                                                <td><?php echo $category['courses_count']; ?></td>
                                                <td><?php echo $category['books_count']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-category" 
                                                            data-id="<?php echo $category['id']; ?>"
                                                            data-name="<?php echo $category['name']; ?>"
                                                            data-description="<?php echo $category['description']; ?>"
                                                            data-toggle="modal" data-target="#editCategoryModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-category"
                                                            data-id="<?php echo $category['id']; ?>"
                                                            data-name="<?php echo $category['name']; ?>"
                                                            data-toggle="modal" data-target="#deleteCategoryModal">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No categories found</td>
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
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="form-group">
                            <label for="edit_name">Category Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_description">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Category Modal -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Delete Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <p>Are you sure you want to delete the category: <strong id="delete_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone. You can only delete categories that are not being used by any courses or books.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Category</button>
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
            // Edit category modal
            $('.edit-category').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var description = $(this).data('description');
                
                $('#edit_id').val(id);
                $('#edit_name').val(name);
                $('#edit_description').val(description);
            });
            
            // Delete category modal
            $('.delete-category').click(function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                
                $('#delete_id').val(id);
                $('#delete_name').text(name);
            });
        });
    </script>
</body>
</html>
