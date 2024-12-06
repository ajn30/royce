<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture and sanitize user inputs
    $name = trim($_POST['name']);
    $userType = trim($_POST['user_type']);
    $membershipID = trim($_POST['membership_id']);
    $contactDetails = trim($_POST['contact_details']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Set borrowing limits based on user type
    switch ($userType) {
        case 'faculty':
            $borrowingLimit = 5;
            break;
        case 'student':
        default:
            $borrowingLimit = 3;
            break;
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO Users (Name, UserType, MembershipID, ContactDetails, Password, BorrowingLimit) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $userType, $membershipID, $contactDetails, $password, $borrowingLimit);

    // Execute the statement and handle the response
    if ($stmt->execute()) {
        echo "<div class='success-message'>Registration successful! Redirecting to login...</div>";
        header('Refresh: 2; URL=login.php');
        exit();
    } else {
        echo "<div class='error-message'>Error: " . $stmt->error . "</div>";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }
        .form-container label {
            font-size: 14px;
            color: #555555;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px 0;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 14px;
        }
        .form-container input[type="submit"] {
            background-color: #4caf50;
            color: #ffffff;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error-message {
            color: #ff0000;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success-message {
            color: #008000;
            font-size: 14px;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Library Registration</h2>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" required>

            <label for="user_type">User Type:</label>
            <select name="user_type" id="user_type" required>
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="faculty">Faculty</option>
                <option value="student">Student</option>
            </select>

            <label for="membership_id">Membership ID:</label>
            <input type="text" name="membership_id" id="membership_id" required>

            <label for="contact_details">Contact Details:</label>
            <input type="text" name="contact_details" id="contact_details" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
