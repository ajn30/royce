<?php
session_start();
include 'db.php';
include 'navigation.php';

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Handle the return book functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['returnBook'])) {
    $borrowID = $_POST['borrowID'];
    $returnDate = date('Y-m-d'); // Set the return date to today's date

    // Fetch the borrowing record
    $borrowQuery = "SELECT BookID, DueDate FROM borrowings WHERE BorrowID = ? AND UserID = ? AND Status = 'borrowed'";
    $stmt = $conn->prepare($borrowQuery);
    $stmt->bind_param("ii", $borrowID, $_SESSION['UserID']);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowData = $result->fetch_assoc();

    if ($borrowData) {
        $bookID = $borrowData['BookID'];
        $dueDate = $borrowData['DueDate'];

        // Calculate the fine if the book is returned late
        $fine = 0;
        if (strtotime($returnDate) > strtotime($dueDate)) {
            $daysLate = (strtotime($returnDate) - strtotime($dueDate)) / 86400; // Calculate days late
            $fine = ceil($daysLate) * 10; // Assume a fine of $10 per day
        }

        // Update the borrowing record to mark the return
        $updateBorrowQuery = "UPDATE borrowings SET Status = 'returned', ReturnDate = ?, Fine = ? WHERE BorrowID = ?";
        $stmt = $conn->prepare($updateBorrowQuery);
        $stmt->bind_param("sii", $returnDate, $fine, $borrowID);
        $stmt->execute();

        // Update the book's availability
        $updateBookQuery = "UPDATE books SET AvailableQuantity = AvailableQuantity + 1 WHERE BookID = ?";
        $stmt = $conn->prepare($updateBookQuery);
        $stmt->bind_param("i", $bookID);
        $stmt->execute();

        echo "<script>alert('Book returned successfully! Fine: $$fine'); window.location.href = 'return_book.php';</script>";
    } else {
        echo "<script>alert('Invalid return request. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Books</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Table Styles */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            text-align: left;
        }

        .table thead {
            background-color: #007bff;
            color: #fff;
        }

        .table th, .table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        /* Button Styles */
        .btn {
            display: inline-block;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        /* Form Input Styles */
        input[type="hidden"] {
            display: none;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        input[type="date"]:focus {
            border-color: #007bff;
            outline: none;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                width: 95%;
            }

            .table thead {
                display: none;
            }

            .table tr {
                display: block;
                margin-bottom: 10px;
            }

            .table td {
                display: block;
                text-align: right;
                padding: 10px;
                position: relative;
            }

            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                text-align: left;
                font-weight: bold;
            }
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Return Books</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $borrowedBooksQuery = "SELECT b.BorrowID, bk.ResourceId, bk.Author, bk.Publisher, b.BorrowDate, b.DueDate
                                       FROM borrowings b
                                       INNER JOIN books bk ON b.BookID = bk.BookID
                                       WHERE b.UserID = ? AND b.Status = 'borrowed'";
                $stmt = $conn->prepare($borrowedBooksQuery);
                $stmt->bind_param("i", $_SESSION['UserID']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['ResourceId']}</td>
                                <td>{$row['Author']}</td>
                                <td>{$row['Publisher']}</td>
                                <td>{$row['BorrowDate']}</td>
                                <td>{$row['DueDate']}</td>
                                <td>
                                    <form method='POST' action='return_book.php'>
                                        <input type='hidden' name='borrowID' value='{$row['BorrowID']}'>
                                        <button type='submit' name='returnBook' class='btn btn-success'>Return</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No books to return.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Include Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybR3k3Wg0jU5xuBqzZ52+P1iU0pQ3rT4Hg/jqLkXZqla6Rh2Vf" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0b8f49VHX69bb6qSoXtzJp6Us6URFJ7kx7hoMOpYwtGJG0A4T" crossorigin="anonymous"></script>
</body>
</html>
