 <?php
 ///include("config/database.php");

// if (isset($_GET['submit'])) {
//     $username = $_GET['username'];
//     $password = $_GET['password'];
//     $date = date("Y-m-d H:i:s");
// //   // insert Query
//   $sql = "INSERT INTO users (username, password, created_at) VALUES ('$username', '$password', '$date')";
   
   
//     if ($conn->query($sql) === TRUE) {
//         $message = "New record created successfully";
//     } else {
//         $message = "Error: " . $sql . "<br>" . $conn->error;
//     }
    
//     $conn->close();
    
//}
// // Update
//  $sql = "update users set username='Wasim' where id=7";


// // // Delete Query
//  $sql = "DELETE FROM users WHERE id = 10";
//  $res = $conn->query($sql);
// // Read
// $sql = "Select * from users";


// $result = $conn->query($sql);
// if ($result->num_rows > 0)
// {
//     while($row = $result->fetch_assoc()) {
//         echo "<pre>"; print_r($row);
//         }
// }


?>
 

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <form action="index.php" method="GET">
        <div>
            <label>Username</label>
            <input type="text" name="username" required>
        </div>
        <br>
        <div> 
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <br>
        <div>
            <button type="submit" name="submit">Submit</button>
        </div>
    </form>
    <?php if (isset($message)): ?>
    <div class="php-output">
        <?php echo $message; ?>
    </div>
    <?php endif; ?>
  </body>
</html>
