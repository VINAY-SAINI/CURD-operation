<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/database.php");
require 'vendor/autoload.php'; // Include the Composer autoload file
use \Firebase\JWT\JWT;

session_start();

$secretKey = 'abcd'; // Use a constant key or store it securely
function generateJWT($username, $roleID, $secretKey) {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600;  // JWT is valid for 1 hour from the issued time
    $payload = array(
        'iat' => $issuedAt,
        'exp' => $expirationTime,
        'username' => $username,
        'roleID' => $roleID
    );

    // Encode the payload to create the JWT
    return JWT::encode($payload, $secretKey, 'HS256');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST["username"];
    $password = $_POST["password"]; // User-entered password

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, now check the password
        $user = $result->fetch_assoc();
        $hashedPassword = $user['Password'];
        $roleID = $user['RoleID'];

        if (password_verify($password, $hashedPassword)) {
            // Password is correct, generate JWT
            $_SESSION['username'] = $username;
            $_SESSION['roleID'] = $roleID;

            $jwt = generateJWT($username, $roleID, $secretKey);
            $_SESSION['secretKey'] = $secretKey;
            $_SESSION['jwt'] = $jwt;
            
            header("Location: dashboard.php");
            // Echo the JWT or store it in a session or send it to the client
           // echo "Login successful. Your token: " . $jwt;

            
        } else {
         
            echo "Incorrect password. Please try again.";
        }
    } else {
      
        echo "Username does not exist. Please register first.";
    }

    $stmt->close();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Auction Management System - Login</title>
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
        <div id="" class="form active">
            <h3>LOGIN</h3>
            <form method="POST" action="">
                <label for="username">Username</label>
                <input type="text" name="username" required>
                <label for="password">Password</label>
                <input type="password" name="password" required>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>