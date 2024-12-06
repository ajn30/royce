<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Get user data from session
$userName = htmlspecialchars($_SESSION['Name']);
$userType = htmlspecialchars($_SESSION['UserType']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($userType); ?> Dashboard</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar .navbar-brand, .navbar .nav-link {
            color: white;
        }
        .navbar .nav-link:hover {
            background-color: #495057;
            color: white;
        }
        .card {
            margin-top: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .logout-btn {
            color: white;
            background-color: #dc3545;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><?php echo ucfirst($userType); ?> Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo $userName; ?></span>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">User Type: <?php echo ucfirst($userType); ?></span>
                    </li>
                    <?php
                    // Dynamically generate top nav links based on user type
                    if ($userType == 'admin') {
                        echo '<li class="nav-item"><a class="nav-link" href="add_resources.php">Add Resources</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="add_book.php">Add Book</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="generate_reports.php">Manage Users</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="reports.php">Generate Reports</a></li>';
                    } elseif ($userType == 'student') {
                        echo '<li class="nav-item"><a class="nav-link" href="view_books.php">View Books</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="borrow_book.php">Borrow Book</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="return_book.php">My Borrowed Books</a></li>';
                    } elseif ($userType == 'staff') {
                        echo '<li class="nav-item"><a class="nav-link" href="manage_books.php">Manage Books</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="issue_book.php">Issue Book</a></li>';
                    } elseif ($userType == 'faculty') {
                        echo '<li class="nav-item"><a class="nav-link" href="recommend_books.php">Recommend Books</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="borrow_book.php">Borrow Book</a></li>';
                        echo '<li class="nav-item"><a class="nav-link" href="return_book.php">My Borrowed Books</a></li>';
                    }
                    ?>
                    <li class="nav-item">
                        <a class="nav-link logout-btn" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="container mt-5">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo ucfirst($userType); ?> Overview</h5>
                <p class="card-text">
                    <?php
                    // Display a message based on user type
                    if ($userType == 'admin') {
                        echo 'Manage users, system settings, and view reports.';
                    } elseif ($userType == 'student') {
                        echo 'Browse available books and manage your borrowed items.';
                    } elseif ($userType == 'staff') {
                        echo 'Add and manage books, and assist with book issuance.';
                    } elseif ($userType == 'faculty') {
                        echo 'Recommend books and manage your borrowing activities.';
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap JS for optional interactivity -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
