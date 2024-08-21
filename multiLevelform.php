<?php
include("config/database.php");
session_start();

// Initialize step variable
$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$isBack = isset($_POST['back']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($isBack) {
        $step--;
    } else {
        switch ($step) {
            case 1:
                // Save step 1 data
                $_SESSION['name'] = $_POST['name'];
                $_SESSION['email'] = $_POST['email'];
                $step = 2;
                break;
            case 2:
                // Save step 2 data
                $_SESSION['address'] = $_POST['address'];
                $_SESSION['city'] = $_POST['city'];
                $step = 3;
                break;
            case 3:
                // Process final submission
                $name = isset($_SESSION['name']) ? trim($_SESSION['name']) : null;
                $email = isset($_SESSION['email']) ? trim($_SESSION['email']) : null;
                $address = isset($_SESSION['address']) ? trim($_SESSION['address']) : null;
                $city = isset($_SESSION['city']) ? trim($_SESSION['city']) : null;

                if ($name && $email && $address && $city) {
                    $time = date("Y-m-d H:i:s");

                    $sql = "INSERT INTO users (name, email, address, city, created_At) 
                            VALUES ('$name', '$email', '$address', '$city', '$time')";
                    if ($conn->query($sql) === TRUE) {
                        echo "<h2>Form Submitted Successfully!</h2>";
                        echo "<p>Thank you, $name. Your information has been saved.</p>";
                    }

                    session_unset();
                    session_destroy();
                } else {
                    echo "<h2>Error: All fields are required.</h2>";
                }
                exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Multi-Step Form</title>
    <link rel="stylesheet" type="text/css" href="style3.css">
    <script>
        function removeRequiredAttributes(form) {
            var elements = form.elements;
            for (var i = 0; i < elements.length; i++) {
                elements[i].removeAttribute('required');
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <?php if ($step == 1): ?>
            <form method="post" action="">
                <h2>Step 1: Personal Information</h2>
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_SESSION['name']) ? $_SESSION['name'] : ''; ?>" required>
                <br>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" required>
                <br>
                <input type="hidden" name="step" value="1">
                <div class="button-group">
                    <button type="submit" class="next-btn">Next</button>
                </div>
            </form>
        <?php elseif ($step == 2): ?>
            <form method="post" action="">
                <h2>Step 2: Address Information</h2>
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo isset($_SESSION['address']) ? $_SESSION['address'] :''; ?>" required> <!--echo $isBack ? '' : 'required';  -->
                <br>
                <label for="city">City:</label>
                <input type="text" id="city" name="city" value="<?php echo isset($_SESSION['city']) ? $_SESSION['city'] :''; ?>" required>
                <br>
                <input type="hidden" name="step" value="2">
                <div class="button-group">
                    <button type="submit" name="back" class="back-btn" onclick="removeRequiredAttributes(this.form)">Back</button>
                    <button type="submit" class="next-btn">Next</button>
                </div>
            </form>
        <?php elseif ($step == 3): ?>
            <form method="post" action="">
                <h2>Step 3: Confirmation</h2>
                <p><strong>Name:</strong> <?php echo $_SESSION['name']; ?></p>
                <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                <p><strong>Address:</strong> <?php echo $_SESSION['address']; ?></p>
                <p><strong>City:</strong> <?php echo $_SESSION['city']; ?></p>
                <input type="hidden" name="step" value="3">
                <div class="button-group">
                    <button type="submit" name="back" class="back-btn" onclick="removeRequiredAttributes(this.form)">Back</button>
                    <button type="submit" class="next-btn">Submit</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>