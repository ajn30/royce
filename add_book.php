<?php
session_start();
include 'db.php';
include 'navigation.php';

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Initialize message variables
$success_message = '';
$error_message = '';

// Handle form submission for adding a new book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
    $resourceID = $_POST['ResourceID'];
    $author = $_POST['Author'];
    $isbn = $_POST['ISBN'];
    $publisher = $_POST['Publisher'];
    $edition = $_POST['Edition'];
    $publicationDate = $_POST['PublicationDate'];
    $quantity = $_POST['Quantity']; // Capture Quantity

    // Set AvailableQuantity to the same value as Quantity
    $availableQuantity = $quantity; 

    // Check for duplicate ISBN
    $checkIsbnQuery = "SELECT * FROM books WHERE ISBN = ?";
    if ($stmt = $conn->prepare($checkIsbnQuery)) {
        $stmt->bind_param("s", $isbn);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->close();
            $error_message = "Error: The ISBN already exists in the system.";
        } else {
            // Insert the new book into the database if no duplicates
            $insertQuery = "INSERT INTO books (ResourceID, Author, ISBN, Publisher, Edition, PublicationDate, Quantity, AvailableQuantity)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $conn->prepare($insertQuery)) {
                $stmt->bind_param("issssssi", $resourceID, $author, $isbn, $publisher, $edition, $publicationDate, $quantity, $availableQuantity);
                if ($stmt->execute()) {
                    $success_message = "New book added successfully!";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}


// Handle delete book
if (isset($_GET['delete'])) {
    $bookID = $_GET['delete'];
    $deleteQuery = "DELETE FROM books WHERE BookID = ?";
    if ($stmt = $conn->prepare($deleteQuery)) {
        $stmt->bind_param("i", $bookID);
        $stmt->execute();
        header("Location: add_book.php");
        exit;
    }
}

// Fetch all books
$booksQuery = "SELECT * FROM books";
$booksResult = $conn->query($booksQuery);

// Fetch all resources
$resourcesQuery = "SELECT ResourceID, Title FROM libraryresources";
$resourcesResult = $conn->query($resourcesQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        /* Container for Content */
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            margin-top: 22px; 
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Heading Styles */
        h2 {
            text-align: center;
            color: #333;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #4caf50;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Buttons */
        button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Fixed position */
            z-index: 1; /* Stay on top */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Semi-transparent background */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal .close:hover,
        .modal .close:focus {
            color: #000;
            text-decoration: none;
        }



        /* Form Fields */
        input[type="text"], input[type="number"], input[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .delete-link {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .delete-link:hover {
            background-color: #0056b3;
        }

        /* Success and Error Messages */
        .success-message {
            color: #28a745;
            background-color: #e8f8e8;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #28a745;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dc3545;
            border-radius: 5px;
            text-align: center;
        }

        /* Media Query for Responsive Design */
        @media screen and (max-width: 768px) {
            .container {
                width: 90%;
            }

            table {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Books List</h2>
        
        <!-- Display Success or Error Message -->
        <?php if (!empty($success_message)): ?>
            <div class="success-message" id="success-message"><?php echo $success_message; ?></div>
        <?php elseif (!empty($error_message)): ?>
            <div class="error-message" id="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <button id="addBookBtn">Add New Book</button>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Publisher</th>
                    <th>Edition</th>
                    <th>Publication Date</th>
                    <th>Quantity</th> <!-- Added Quantity Column -->
                    <th>Available Quantity</th> <!-- Added Available Quantity Column -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while ($row = $booksResult->fetch_assoc()):
                    $resourceQuery = "SELECT Title FROM libraryresources WHERE ResourceID = ?";
                    if ($stmt = $conn->prepare($resourceQuery)) {
                        $stmt->bind_param("i", $row['ResourceID']);
                        $stmt->execute();
                        $stmt->bind_result($resourceTitle);
                        $stmt->fetch();
                        $stmt->close();
                    }
                ?>
                    <tr id="book-row-<?php echo $row['BookID']; ?>">
                        <td><?php echo $resourceTitle; ?></td>
                        <td><?php echo $row['Author']; ?></td>
                        <td><?php echo $row['ISBN']; ?></td>
                        <td><?php echo $row['Publisher']; ?></td>
                        <td><?php echo $row['Edition']; ?></td>
                        <td><?php echo $row['PublicationDate']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td> <!-- Display Quantity -->
                        <td><?php echo $row['AvailableQuantity']; ?></td> <!-- Display AvailableQuantity -->
                        <td>
                            <a href="add_book.php?delete=<?php echo $row['BookID']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                            <button onclick="editBook(<?php echo $row['BookID']; ?>)">Edit</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>

    <!-- Modal for Add Book -->
    <div id="addBookModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addBookModal')">&times;</span>
            <h2>Add New Book</h2>
            <form method="POST" action="add_book.php" id="addBookForm">
                <input type="hidden" name="bookID" id="bookID">
                <div class="form-group">
                    <label for="ResourceID">Resource:</label>
                    <select name="ResourceID" id="ResourceID" required>
                        <option value="" disabled selected>Select a Resource</option>
                        <?php 
                        $resourcesResult->data_seek(0); // Reset the pointer to start from the beginning
                        while ($resource = $resourcesResult->fetch_assoc()): ?>
                            <option value="<?php echo $resource['ResourceID']; ?>"><?php echo $resource['Title']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="Author">Author:</label>
                    <input type="text" name="Author" id="Author" required>
                </div>
                <div class="form-group">
                    <label for="ISBN">ISBN:</label>
                    <input type="text" name="ISBN" id="ISBN" required>
                </div>
                <div class="form-group">
                    <label for="Publisher">Publisher:</label>
                    <input type="text" name="Publisher" id="Publisher" required>
                </div>
                <div class="form-group">
                    <label for="Edition">Edition:</label>
                    <input type="text" name="Edition" id="Edition" required>
                </div>
                <div class="form-group">
                    <label for="PublicationDate">Publication Date:</label>
                    <input type="date" name="PublicationDate" id="PublicationDate" required>
                </div>
                <div class="form-group">
                    <label for="Quantity">Quantity:</label>
                    <input type="number" name="Quantity" id="Quantity" required min="1">
                </div>
                <button type="submit" name="add_book">Save Book</button>
            </form>
        </div>
    </div>

    <!-- Modal for Edit Book -->
    <div id="editBookModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editBookModal')">&times;</span>
            <h2>Edit Book</h2>
            <form method="POST" action="add_book.php" id="editBookForm">
                <input type="hidden" name="bookID" id="editBookID">
                <div class="form-group">
                    <label for="editResourceID">Resource:</label>
                    <select name="ResourceID" id="editResourceID" required>
                        <option value="" disabled selected>Select a Resource</option>
                        <?php 
                        // Populate resource options
                        $resourcesResult->data_seek(0); // Reset the pointer to start from the beginning
                        while ($resource = $resourcesResult->fetch_assoc()): ?>
                            <option value="<?php echo $resource['ResourceID']; ?>"><?php echo $resource['Title']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editAuthor">Author:</label>
                    <input type="text" name="Author" id="editAuthor" required>
                </div>
                <div class="form-group">
                    <label for="editISBN">ISBN:</label>
                    <input type="text" name="ISBN" id="editISBN" required>
                </div>
                <div class="form-group">
                    <label for="editPublisher">Publisher:</label>
                    <input type="text" name="Publisher" id="editPublisher" required>
                </div>
                <div class="form-group">
                    <label for="editEdition">Edition:</label>
                    <input type="text" name="Edition" id="editEdition" required>
                </div>
                <div class="form-group">
                    <label for="editPublicationDate">Publication Date:</label>
                    <input type="date" name="PublicationDate" id="editPublicationDate" required>
                </div>
                <div class="form-group">
                    <label for="Quantity">Quantity:</label>
                    <input type="number" name="Quantity" id="Quantity" required min="1">
                </div>
                <button type="submit" name="edit_book">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        var addModal = document.getElementById("addBookModal");
        var editModal = document.getElementById("editBookModal");

        var addBookBtn = document.getElementById("addBookBtn");
        addBookBtn.onclick = function() {
            addModal.style.display = "block";
        }

        function editBook(bookId) {
            var row = document.getElementById('book-row-' + bookId);

            // Set the hidden BookID input value
            document.getElementById('editBookID').value = bookId;

            // Fill the other fields with data from the table row
            document.getElementById('editAuthor').value = row.cells[1].textContent;
            document.getElementById('editISBN').value = row.cells[2].textContent;
            document.getElementById('editPublisher').value = row.cells[3].textContent;
            document.getElementById('editEdition').value = row.cells[4].textContent;
            document.getElementById('editPublicationDate').value = row.cells[5].textContent;
            document.getElementById('Quantity').value = row.cells[6].textContent;

            // Set the ResourceID dropdown
            var resourceID = row.cells[0].textContent; // Assuming the first cell is the resource title
            var resourceSelect = document.getElementById('editResourceID');
            for (var i = 0; i < resourceSelect.options.length; i++) {
                if (resourceSelect.options[i].text === resourceID) {
                    resourceSelect.selectedIndex = i;
                    break;
                }
            }

            // Open the Edit Modal
            editModal.style.display = "block";
        }

        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == addModal || event.target == editModal) {
                addModal.style.display = "none";
                editModal.style.display = "none";
            }
        }

        window.onload = function() {
            setTimeout(function() {
                var successMessage = document.getElementById('success-message');
                var errorMessage = document.getElementById('error-message');
                
                if (successMessage) {
                    successMessage.style.display = 'none';
                }
                if (errorMessage) {
                    errorMessage.style.display = 'none';
                }
            }, 5000); // 5000 ms = 5 seconds
        }

    </script>
</body>
</html>
