
<?php
include("config/database.php");

$name = $fathername = $mothername = $phone = $email = $gender = $dob = "";
$editMode = false;

if (isset($_GET['submit'])) {
        insertValue();
        
}

if (isset($_GET['delete'])) {
    delete($_GET['delete']);
}

if (isset($_GET['edit'])) {
    $editMode = true;
    $id = $_GET['edit'];
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

 
    $name = $row['name'];
    $fathername = $row['fathername'];
    $mothername = $row['mothername'];
    $phone = $row['phone'];
    $email = $row['email'];
    $gender = $row['gender'];
    $dob = $row['dob'];
    delete($id);
}

function insertValue() {
    global $conn;
    $name = $_GET['name'];
    $fathername = $_GET['fathername'];
    $mothername = $_GET['mothername'];
    $phone = $_GET['phone'];
    $email = $_GET['email'];
    $gender = $_GET['gender'];
    $dob = $_GET['dob'];

    $sql = "INSERT INTO users (name, fathername, mothername, phone, email, gender, dob) 
            VALUES ('$name', '$fathername', '$mothername', '$phone', '$email', '$gender', '$dob')";

    if ($conn->query($sql) === TRUE) {
        debug_to_console("New record created successfully");
    } else {
        debug_to_console("Error: " . $sql . "<br>" . $conn->error);
    }

}

function display() {
    global $conn;
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table class='data-table'>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Father's Name</th>
                    <th>Mother's Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row['id'] . "</td>
                    <td>" . $row['name'] . "</td>
                    <td>" . $row['fathername'] . "</td>
                    <td>" . $row['mothername'] . "</td>
                    <td>" . $row['phone'] . "</td>
                    <td>" . $row['email'] . "</td>
                    <td>" . $row['gender'] . "</td>
                    <td>" . $row['dob'] . "</td>
                    <td>
                        <a href='index2.php?edit=" . $row['id'] . "' class='button edit-button'>Edit</a>
                        <a href='index2.php?delete=" . $row['id'] . "' class='button delete-button'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No records found.";
    }
}

function delete($id) {
    global $conn;
    $sql = "DELETE FROM users WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        debug_to_console("Record deleted successfully");
    } else {
        debug_to_console("Error deleting record: " . $conn->error);
    }
}

function idExists($id) {
    global $conn;
    $sql = "SELECT id FROM users WHERE id = $id";
    $result = $conn->query($sql);

    return $result->num_rows > 0;
}

function debug_to_console($data) {
    if (is_array($data)) {
        $data = json_encode($data);
    }
    echo "<script>console.log('Debug Objects: " . $data . "');</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Form</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="container">
        <form action="index2.php" method="GET">
            <?php if ($editMode): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>" required>
            </div>
            <div class="form-group"> 
                <label>Father's Name</label>
                <input type="text" name="fathername" value="<?php echo $fathername; ?>" required>
            </div>
            <div class="form-group"> 
                <label>Mother's Name</label>
                <input type="text" name="mothername" value="<?php echo $mothername; ?>" required>
            </div>
            <div class="form-group"> 
                <label>Phone number</label>
                <input type="number" name="phone" value="<?php echo $phone; ?>" required>
            </div>
            <div class="form-group"> 
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo $email; ?>" required>
            </div>
            <div class="form-group"> 
                <label>Gender</label>
                <input type="radio" name="gender" value="Male" <?php echo ($gender == 'Male') ? 'checked' : ''; ?> required>Male
                <input type="radio" name="gender" value="Female" <?php echo ($gender == 'Female') ? 'checked' : ''; ?> required>Female
                <input type="radio" name="gender" value="Other" <?php echo ($gender == 'Other') ? 'checked' : ''; ?> required>Other
            </div>
            <div class="form-group"> 
                <label>Date Of Birth</label>
                <input type="date" name="dob" value="<?php echo $dob; ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" name="submit">Submit</button>
            </div>
        </form>

        <div class="php-output">
            <?php display(); ?>
        </div>
    </div>
</body>
</html>
