<!-- registration page -->


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $conn;
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // Hash the password
    $mail = $_POST["mail"];
    $roleID = $_POST["role"];

    $sql = "SELECT * FROM Users WHERE Username='$username' OR Email='$mail'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // If user already exists
        echo "Username or Email already exists. Please choose a different one.";
    } else {
        // If user doesn't exist, create the user  
        $is_deleted = 0;

        $sql = "INSERT INTO Users (Username, Password, Email, RoleID,  Is_deleted) 
                VALUES ('$username', '$password', '$mail', '$roleID', '$is_deleted')";
        if ($conn->query($sql) === TRUE) {
             header("Location: loginform.php");

        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Auction Management System - Registration</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="Form.css">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="form-container">
        <div id="registrationForm" class="form active">
            <h3>Registration</h3>
            <form method="POST" action="#">
                <input type="hidden" name="role" value="1">
                <label for="mail">Mail ID</label>
                <input type="email" name="mail" required>
                <label for="username">Username</label>
                <input type="text" name="username" required>
                <label for="password">Password</label>
                <input type="password" name="password" required>
                <button type="submit">Register</button>
            </form>
        </div>
    </div>
</body>
</html>