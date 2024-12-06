<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $membershipID = $_POST['membership_id'];
    $password = $_POST['password'];

    // SQL query to fetch the user
    $sql = "SELECT * FROM Users WHERE MembershipID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $membershipID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Validate the password
        if (password_verify($password, $row['Password'])) {
            // Set session variables
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Name'] = $row['Name'];
            $_SESSION['UserType'] = $row['UserType'];
            header('Location: dashboard.php'); // Redirect to dashboard
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "User not found. Please check your Membership ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        .form-container input {
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
        <h2>Login</h2>
        <?php if (isset($error)) { echo "<div class='error-message'>$error</div>"; } ?>
        <form method="POST">
            <label for="membership_id">Membership ID:</label>
            <input type="text" name="membership_id" id="membership_id" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
