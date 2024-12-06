<?php
session_start();

include 'db.php';
include 'navigation.php';

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission for generating reports
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['reportType'];
    $userID = $_POST['userID'] ?? ''; // For borrowing history report
    $startDate = $_POST['startDate'] ?? ''; // For date-based reports
    $endDate = $_POST['endDate'] ?? '';

    // Query for Borrowing History Report (Individual User)
    if ($reportType == 'borrowing_history' && $userID != '') {
        $query = "SELECT b.BookID, b.Title, br.BorrowDate, br.ReturnDate, br.Fine 
                  FROM borrowings br
                  JOIN books b ON br.BookID = b.BookID
                  WHERE br.UserID = '$userID'";
        if ($startDate && $endDate) {
            $query .= " AND br.BorrowDate BETWEEN '$startDate' AND '$endDate'";
        }
        $result = $conn->query($query);
    }

    // Query for Popular Books Report (Based on Borrowing Frequency)
    elseif ($reportType == 'popular_books') {
        $query = "SELECT b.BookID, b.Title, COUNT(br.BookID) AS BorrowCount
                  FROM borrowings br
                  JOIN books b ON br.BookID = b.BookID
                  GROUP BY b.BookID
                  ORDER BY BorrowCount DESC";
        $result = $conn->query($query);
    }

    // Query for Overdue Books and Fines
    elseif ($reportType == 'overdue_books') {
        $query = "SELECT br.UserID, b.Title, br.BorrowDate, br.ReturnDate, br.Fine 
                  FROM borrowings br
                  JOIN books b ON br.BookID = b.BookID
                  WHERE br.ReturnDate < CURDATE() AND br.Status = 'borrowed'";
        $result = $conn->query($query);
    }

    // Query for Inventory Summaries (Books by Category)
    elseif ($reportType == 'inventory_summary') {
        $query = "SELECT r.Category, COUNT(b.BookID) AS TotalBooks, SUM(b.AvailableQuantity) AS AvailableBooks
                  FROM books b
                  JOIN libraryresources r ON b.ResourceID = r.ResourceID
                  GROUP BY r.Category";
        $result = $conn->query($query);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Generate Reports</h2>

        <form method="POST" action="generate_reports.php">
            <div class="form-group">
                <label for="reportType">Select Report Type</label>
                <select id="reportType" name="reportType" required>
                    <option value="borrowing_history">Borrowing History</option>
                    <option value="popular_books">Popular Books</option>
                    <option value="overdue_books">Overdue Books</option>
                    <option value="inventory_summary">Inventory Summary</option>
                </select>
            </div>

            <!-- User Selection for Borrowing History -->
            <div class="form-group" id="userSection">
                <label for="userID">Select User (For Borrowing History)</label>
                <select id="userID" name="userID">
                    <!-- Fetch users dynamically from the database -->
                    <?php
                    $users = $conn->query("SELECT UserID, Name FROM users");
                    while ($row = $users->fetch_assoc()) {
                        echo "<option value='{$row['UserID']}'>{$row['Name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Date Range Selection -->
            <div class="form-group">
                <label for="startDate">Start Date (Optional)</label>
                <input type="date" id="startDate" name="startDate">
            </div>

            <div class="form-group">
                <label for="endDate">End Date (Optional)</label>
                <input type="date" id="endDate" name="endDate">
            </div>

            <button type="submit" class="btn">Generate Report</button>
        </form>

        <?php if (isset($result)): ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <?php if ($reportType == 'borrowing_history'): ?>
                            <th>Book ID</th>
                            <th>Title</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Fine</th>
                        <?php elseif ($reportType == 'popular_books'): ?>
                            <th>Book ID</th>
                            <th>Title</th>
                            <th>Borrow Count</th>
                        <?php elseif ($reportType == 'overdue_books'): ?>
                            <th>User ID</th>
                            <th>Title</th>
                            <th>Borrow Date</th>
                            <th>Return Date</th>
                            <th>Fine</th>
                        <?php elseif ($reportType == 'inventory_summary'): ?>
                            <th>Category</th>
                            <th>Total Books</th>
                            <th>Available Books</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php if ($reportType == 'borrowing_history'): ?>
                                <td><?php echo $row['BookID']; ?></td>
                                <td><?php echo $row['Title']; ?></td>
                                <td><?php echo $row['BorrowDate']; ?></td>
                                <td><?php echo $row['ReturnDate']; ?></td>
                                <td><?php echo $row['Fine']; ?></td>
                            <?php elseif ($reportType == 'popular_books'): ?>
                                <td><?php echo $row['BookID']; ?></td>
                                <td><?php echo $row['Title']; ?></td>
                                <td><?php echo $row['BorrowCount']; ?></td>
                            <?php elseif ($reportType == 'overdue_books'): ?>
                                <td><?php echo $row['UserID']; ?></td>
                                <td><?php echo $row['Title']; ?></td>
                                <td><?php echo $row['BorrowDate']; ?></td>
                                <td><?php echo $row['ReturnDate']; ?></td>
                                <td><?php echo $row['Fine']; ?></td>
                            <?php elseif ($reportType == 'inventory_summary'): ?>
                                <td><?php echo $row['Category']; ?></td>
                                <td><?php echo $row['TotalBooks']; ?></td>
                                <td><?php echo $row['AvailableBooks']; ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        // Show or hide user section based on selected report type
        document.getElementById('reportType').addEventListener('change', function() {
            const userSection = document.getElementById('userSection');
            if (this.value === 'borrowing_history') {
                userSection.style.display = 'block';
            } else {
                userSection.style.display = 'none';
            }
        });

        // Trigger the change event to set the correct form display
        document.getElementById('reportType').dispatchEvent(new Event('change'));
    </script>
</body>
</html>
