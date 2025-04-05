<?php
// Include session check which handles authentication and timeout
require_once 'session_check.php';

require_once '../config.php';

// Get all counts in a single query for better performance
$counts_query = $conn->query("SELECT 
    (SELECT COUNT(*) FROM users) as users_count,
    (SELECT COUNT(*) FROM courses) as courses_count,
    (SELECT COUNT(*) FROM videos) as videos_count,
    (SELECT COUNT(*) FROM books) as books_count");

$counts = $counts_query->fetch_assoc();
$users_count = $counts['users_count'];
$courses_count = $counts['courses_count'];
$videos_count = $counts['videos_count'];
$books_count = $counts['books_count'];

// Get recent users with prepared statement
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_users = $stmt->get_result();

// Get recent courses with prepared statement
$stmt = $conn->prepare("SELECT id, title, description, created_at FROM courses ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_courses = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyBud Admin Dashboard</title>
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
        .card-counter {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px 10px;
            background-color: #fff;
            height: 100px;
            border-radius: 5px;
            transition: 0.3s;
        }
        .card-counter:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .card-counter i {
            font-size: 5em;
            opacity: 0.2;
        }
        .card-counter .count-numbers {
            position: absolute;
            right: 35px;
            top: 20px;
            font-size: 32px;
            display: block;
        }
        .card-counter .count-name {
            position: absolute;
            right: 35px;
            top: 65px;
            font-style: italic;
            text-transform: capitalize;
            opacity: 0.5;
            display: block;
            font-size: 18px;
        }
        .card-counter.primary {
            background-color: #4e73df;
            color: #FFF;
        }
        .card-counter.danger {
            background-color: #e74a3b;
            color: #FFF;
        }
        .card-counter.success {
            background-color: #1cc88a;
            color: #FFF;
        }
        .card-counter.info {
            background-color: #36b9cc;
            color: #FFF;
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
                    <a class="nav-link active" href="index.php">
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
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Users Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card-counter primary">
                            <i class="fa fa-users"></i>
                            <span class="count-numbers"><?php echo $users_count; ?></span>
                            <span class="count-name">Users</span>
                        </div>
                    </div>

                    <!-- Courses Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card-counter success">
                            <i class="fa fa-book"></i>
                            <span class="count-numbers"><?php echo $courses_count; ?></span>
                            <span class="count-name">Courses</span>
                        </div>
                    </div>

                    <!-- Videos Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card-counter info">
                            <i class="fa fa-video"></i>
                            <span class="count-numbers"><?php echo $videos_count; ?></span>
                            <span class="count-name">Videos</span>
                        </div>
                    </div>

                    <!-- Books Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card-counter danger">
                            <i class="fa fa-book-open"></i>
                            <span class="count-numbers"><?php echo $books_count; ?></span>
                            <span class="count-name">Books</span>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Recent Users -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Users</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Joined</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $recent_users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $user['name']; ?></td>
                                                <td><?php echo $user['email']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="users.php" class="btn btn-primary btn-sm mt-3">View All Users</a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Courses -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Courses</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Title</th>
                                                <th>Instructor</th>
                                                <th>Added</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($course = $recent_courses->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $course['title']; ?></td>
                                                <td><?php echo $course['instructor']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="courses.php" class="btn btn-primary btn-sm mt-3">View All Courses</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
