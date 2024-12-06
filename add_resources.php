<?php
session_start();

include 'db.php';
include 'navigation.php';

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    header('Location: login.php');
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Handle Add Resource
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_resource'])) {
    $title = $_POST['title'];
    $accessionNumber = $_POST['accession_number'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $availableQuantity = $_POST['available_quantity'];
    $resourceId = $_POST['resource_id']; // Get the resourceId from the form
    $createdAt = date("Y-m-d H:i:s"); // Get the current timestamp

    // Check for duplicate ResourceID
    $stmt = $conn->prepare("SELECT * FROM libraryresources WHERE ResourceID = ?");
    $stmt->bind_param("s", $resourceId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='error-message'>Error: A resource with this Resource ID already exists.</div>";
    } else {
        // Prepare SQL statement
        $stmt = $conn->prepare("INSERT INTO libraryresources (ResourceID, Title, AccessionNumber, Category, Quantity, AvailableQuantity, CreatedAt) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters to the prepared statement
        if ($stmt) {
            $stmt->bind_param("sssssss", $resourceId, $title, $accessionNumber, $category, $quantity, $availableQuantity, $createdAt);

            // Execute the statement
            if ($stmt->execute()) {
                $message = "<div class='success-message'>Resource added successfully!</div>";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $message = "<div class='error-message'>Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='error-message'>Error preparing statement: " . $conn->error . "</div>";
        }
    }
    $stmt->close();
}

// Handle Edit Resource
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_resource'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $accessionNumber = $_POST['accession_number'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $availableQuantity = $_POST['available_quantity'];
    $resourceId = $_POST['resource_id']; // Get the resourceId from the form

    $sql = "UPDATE libraryresources 
            SET Title='$title', AccessionNumber='$accessionNumber', Category='$category', Quantity='$quantity', AvailableQuantity='$availableQuantity', ResourceID='$resourceId' 
            WHERE ResourceID='$id'";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success-message'>Resource updated successfully!</div>";
    } else {
        $message = "<div class='error-message'>Error: " . $conn->error . "</div>";
    }
}

// Handle Delete Resource
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM libraryresources WHERE ResourceID='$id'";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success-message'>Resource deleted successfully!</div>";
    } else {
        $message = "<div class='error-message'>Error: " . $conn->error . "</div>";
    }
}

// Fetch Resources
$result = $conn->query("SELECT * FROM libraryresources");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management</title>
    <style>
       /* General Styles */
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

        /* Edit and Delete Buttons */
        .edit-btn, .delete-btn {
            background-color: #28a745;
            margin-right: 8px;
        }

        .edit-btn:hover, .delete-btn:hover {
            background-color: #218838;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
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
        <h2>All Resources</h2>
        <?php if (isset($message)) echo $message; ?>
        <button id="addResourceBtn">Add Resource</button>
        <table>
            <tr>
                <th>ResourceID</th>
                <th>Title</th>
                <th>Accession Number</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Available Quantity</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr id="resource-<?php echo $row['ResourceID']; ?>">
                        <td><?php echo $row['ResourceID']; ?></td>
                        <td><?php echo $row['Title']; ?></td>
                        <td><?php echo $row['AccessionNumber']; ?></td>
                        <td><?php echo $row['Category']; ?></td>
                        <td><?php echo $row['Quantity']; ?></td>
                        <td><?php echo $row['AvailableQuantity']; ?></td>
                        <td><?php echo $row['CreatedAt']; ?></td>
                        <td>
                            <button class="edit-btn" onclick="editResource('<?php echo $row['ResourceID']; ?>')">Edit</button>
                            <a href="?delete=<?php echo $row['ResourceID']; ?>" onclick="return confirm('Are you sure you want to delete this resource?')">
                                <button class="delete-btn">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No resources found.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Add Resource Modal -->
        <div id="addResourceModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Add Resource</h3>
                <form action="" method="POST">
                    <label for="resource_id">ResourceID</label>
                    <input type="text" id="resource_id" name="resource_id" required>

                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required>

                    <label for="accession_number">Accession Number</label>
                    <input type="text" id="accession_number" name="accession_number" required>

                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" required>

                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" required>

                    <label for="available_quantity">Available Quantity</label>
                    <input type="number" id="available_quantity" name="available_quantity" required>

                    <input type="submit" name="add_resource" value="Add Resource">
                </form>
            </div>
        </div>

        <!-- Edit Resource Modal -->
        <div id="editResourceModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h3>Edit Resource</h3>
                <form id="editResourceForm" action="" method="POST">
                    <input type="hidden" id="editId" name="id">
                    
                    <label for="editTitle">Title</label>
                    <input type="text" id="editTitle" name="title" required>

                    <label for="editAccessionNumber">Accession Number</label>
                    <input type="text" id="editAccessionNumber" name="accession_number" required>

                    <label for="editCategory">Category</label>
                    <input type="text" id="editCategory" name="category" required>

                    <label for="editQuantity">Quantity</label>
                    <input type="number" id="editQuantity" name="quantity" required>

                    <label for="editAvailableQuantity">Available Quantity</label>
                    <input type="number" id="editAvailableQuantity" name="available_quantity" required>

                    <label for="editResourceId">Resource ID</label>
                    <input type="text" id="editResourceId" name="resource_id" required>

                    <input type="submit" name="edit_resource" value="Save Changes">
                </form>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        var modal = document.getElementById('addResourceModal');
        var editModal = document.getElementById('editResourceModal');

        document.getElementById('addResourceBtn').onclick = function() {
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
            editModal.style.display = "none";
        }

        function editResource(id) {
            // Fetch resource data using the Resource ID
            var row = document.getElementById('resource-' + id);
            var title = row.cells[1].textContent;
            var accessionNumber = row.cells[2].textContent;
            var category = row.cells[3].textContent;
            var quantity = row.cells[4].textContent;
            var availableQuantity = row.cells[5].textContent;
            var resourceId = row.cells[0].textContent;

            // Fill the edit form with data
            document.getElementById('editId').value = id;
            document.getElementById('editTitle').value = title;
            document.getElementById('editAccessionNumber').value = accessionNumber;
            document.getElementById('editCategory').value = category;
            document.getElementById('editQuantity').value = quantity;
            document.getElementById('editAvailableQuantity').value = availableQuantity;
            document.getElementById('editResourceId').value = resourceId;

            // Show edit modal
            editModal.style.display = "block";
        }
    </script>
</body>
</html>
