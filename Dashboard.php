<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/database.php");
require 'vendor/autoload.php'; 
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

session_start();

if (!isset($_SESSION['secretKey']) || !isset($_SESSION['jwt'])) {
    echo "Session expired or not logged in. Please log in again.";
    header("Location: loginform.php");
    exit;
}

$secretKey = $_SESSION['secretKey'];
$jwt = $_SESSION['jwt'];

try {
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));  
    $username = $decoded->username;
    $roleID = $decoded->roleID;

} catch (\Firebase\JWT\ExpiredException $e) {
    header("Location: loginform.php?message=Session expired. Please log in again.");
    exit;
} catch (Exception $e) {
    echo "Access denied. Error: " . $e->getMessage();
    exit;
}

// Restrict actions based on role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Create User
    if (isset($_POST['createUser']) && ($roleID == 1 || $roleID == 2)) {
        $username = $_POST['userUsername'];
        $password = password_hash($_POST['userPassword'], PASSWORD_BCRYPT); // Hashing the password for security
        $email = $_POST['userEmail'];
        $roleID = $_POST['userRoleID'];

        createUser($username, $password, $email, $roleID);
    } elseif (isset($_POST['createUser'])) {
        echo "Access denied: You do not have permission to create users.";
    }

    // Create Manager
    if (isset($_POST['createManager']) && $roleID == 1) {
        $username = $_POST['managerUsername'];
        $password = password_hash($_POST['managerPassword'], PASSWORD_BCRYPT); // Hashing the password for security
        $email = $_POST['managerEmail'];
        $roleID = $_POST['roleID'];

        createUser($username, $password, $email, $roleID);
    } elseif (isset($_POST['createManager'])) {
        echo "Access denied: You do not have permission to create managers.";
    }

    // Create Item
    if (isset($_POST['itemName'], $_POST['itemDescription'], $_POST['minPrice'], $_POST['maxPrice']) && $roleID == 1) {
        $name = $_POST['itemName'];
        $description = $_POST['itemDescription'];
        $minimum = $_POST['minPrice'];
        $maximum = $_POST['maxPrice'];

        CreateItem($name, $description, $minimum, $maximum);
    } elseif (isset($_POST['itemName'])) {
        echo "Access denied: You do not have permission to create items.";
    }

    // Delete Item
    if (isset($_POST['delete']) && ($roleID == 1 || $roleID == 2)) {
        $itemId = $_POST['delete_id'];
        deleteItem($itemId);
    } elseif (isset($_POST['delete'])) {
        echo "Access denied: You do not have permission to delete items.";
    }
}

function createUser($username, $password, $email, $roleID) {
    global $conn;
    $createdBy = 1; // Assuming '1' is the admin ID; adjust as necessary
    $sql = "INSERT INTO Users (Username, Password, Email, RoleID, CreatedBy, Is_deleted) 
            VALUES ('$username', '$password', '$email', $roleID, $createdBy, 0)";

    if ($conn->query($sql) === TRUE) {
        echo "User created successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

function CreateItem($name, $description, $minimum, $maximum) {
    global $conn;
    $Is_deleted = 0;
    $Createdby = 1;
    $added_time = date('Y-m-d H:i:s');
    $updated_time = date('Y-m-d H:i:s');
    $Status = 1;

    $sql_check = "SELECT ID FROM AuctionItem WHERE Name = '$name' LIMIT 1";
    $result = $conn->query($sql_check);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $itemId = $row['ID'];
        $sql_update = "UPDATE AuctionItem SET Is_deleted = 0, Description = '$description', MinPrice = $minimum, MaxPrice = $maximum, updated_time = '$updated_time', Status = $Status WHERE ID = $itemId";
        
        if ($conn->query($sql_update) === TRUE) {
            echo "Item updated successfully.";
            header("Location: dashboard.php?message=Item updated successfully");
            exit;
        } else {
            echo "Error updating item: " . $conn->error;
        }
    } else {
        $sql_insert = "INSERT INTO AuctionItem (Name, Description, MinPrice, MaxPrice, CreatedBy, Is_deleted, added_time, updated_time, Status)
                        VALUES ('$name', '$description', $minimum, $maximum, $Createdby, $Is_deleted, '$added_time', '$updated_time', $Status)";

        if ($conn->query($sql_insert) === TRUE) {
            echo "Item inserted successfully.";
            header("Location: dashboard.php?message=Item created successfully");
            exit;
        } else {
            echo "Error inserting item: " . $conn->error;
        }
    }
}

function AddItem() {
    global $conn;
    
    $sql = "SELECT * FROM AuctionItem WHERE Status=1 AND Is_deleted=0";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
            echo "<td>$" . htmlspecialchars($row['MinPrice']) . "</td>";
            echo "<td>$" . htmlspecialchars($row['MaxPrice']) . "</td>";
            echo "<td class='actions-column'>";
            if ($GLOBALS['roleID'] == 1 || $GLOBALS['roleID'] == 2) {
                echo "<form method='POST' action='#' class='d-inline'>";
                echo "<input type='hidden' name='delete_id' value='" . $row['ID'] . "'>";
                echo "<button type='submit' name='delete' class='btn btn-danger btn-sm'>Delete</button>";
                echo "</form>";
            }
            echo "<form method='POST' action='#' class='d-inline'>";
            echo "<input type='hidden' name='auction_id' value='" . $row['ID'] . "'>";
            echo "<button type='submit' class='btn btn-success btn-sm'>Auction</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No items found.</td></tr>";
    }
}

function deleteItem($itemId) {
    global $conn;
    $sql = "UPDATE AuctionItem SET Is_deleted = 1 WHERE ID = $itemId";
    
    if ($conn->query($sql) === TRUE) {
        echo "Item deleted successfully.";
    } else {
        echo "Error: " . $conn->error;
    }
}

function TotalUser(){
    global $conn;
    $sql = "SELECT COUNT(*) as user_count FROM Users WHERE ID = 3";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $user_count = $row['user_count'];
        echo "$user_count";
    } else {
        echo "Error: " . $conn->error;
    }  
}

function TotalManager(){
    global $conn;
    $sql = "SELECT COUNT(*) as user_count FROM Users WHERE ID = 2";
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $user_count = $row['user_count'];
        echo "$user_count";
    } else {
        echo "Error: " . $conn->error;
    }  
}

function TotalItem(){
    global $conn;
    $sql = "SELECT COUNT(*) as item_count FROM AuctionItem WHERE Status=2";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $item_count = $row['item_count'];
        echo "$item_count";
    } 
    else {
        echo "Error: " . $conn->error;
    }  
}

function TotalBid(){
    global $conn;
    $sql = "SELECT COUNT(*) as item_count FROM Bid";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $item_count = $row['item_count'];
        echo "$item_count";
    } 
    else {
        echo "Error: " . $conn->error;
    }  
}

function getTotalBidAmount() {
    global $conn;
    $sql = "SELECT IFNULL(SUM(Amount), 0) as total_amount FROM Bid"; 
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        $total_amount = $row['total_amount'];
        echo $total_amount;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Management Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="Dashboard.css">
</head>

<body>
    <!-- Main Content -->
    <div id="main-content" class="container">
        <!-- Header -->
        <div id="header" class="d-flex justify-content-between align-items-center">
            <a href="#" class="navbar-brand">Auction Management System</a>
            <div>
                <button class="btn btn-light" id="userProfileButton">User Profile</button>
                <button class="btn btn-light" onclick="window.location.href='logout.php';">Logout</button>
            </div>
        </div>

        <!-- Profile Modal -->
        <div class="modal fade" id="profileModal" tabindex="-1" aria-labelledby="profileModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="profileModalLabel">User Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                        <p><strong>User ID:</strong> <?php echo htmlspecialchars($roleID); ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Overview -->
        <section id="overview" class="mt-4">
            <h2>Dashboard Overview</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3 animate-card">
                        <div class="card-body">
                            <h5 class="card-title">Active Manager</h5>
                            <p class="card-text"><?php TotalManager(); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3 animate-card">
                        <div class="card-body">
                            <h5 class="card-title">Revenue</h5>
                            <p class="card-text"><?php getTotalBidAmount(); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3 animate-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Bids</h5>
                            <p class="card-text"><?php TotalBid(); ?> </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3 animate-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Items</h5>
                            <p class="card-text"><?php TotalItem(); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3 animate-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text"><?php TotalUser(); ?> </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Create Item -->
        <?php if ($roleID == 1) { ?>
        <section id="create-item" class="mt-4">
            <h2>Create Item</h2>
            <div class="card animate-form">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="itemName">Item Name</label>
                            <input type="text" class="form-control" id="itemName" name="itemName" placeholder="Enter item name" required>
                        </div>
                        <div class="form-group">
                            <label for="itemDescription">Description</label>
                            <textarea class="form-control" id="itemDescription" name="itemDescription" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="minPrice">Min Price</label>
                            <input type="number" class="form-control" id="minPrice" name="minPrice" placeholder="Enter minimum price" required>
                        </div>
                        <div class="form-group">
                            <label for="maxPrice">Max Price</label>
                            <input type="number" class="form-control" id="maxPrice" name="maxPrice" placeholder="Enter maximum price" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </section>
        <?php } ?>

        <!-- Add Item -->
        <?php if ($roleID == 1 || $roleID == 2) { ?>
        <section id="add-item" class="mt-4">
            <h2>Added Item</h2>
            <div class="card animate-form">
                <div class="card-body">
                    <h5 class="card-title">Item List</h5>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Min Price</th>
                                <th>Max Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Items will be listed here dynamically -->
                            <?php AddItem(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
        <?php } ?>
        <!-- Auction -->
        <section id="auction" class="mt-4">
            <h2>Auction</h2>
            <div class="card animate-form">
                <div class="card-body">
                    <h5 class="card-title">Start Auction</h5>
                    <p>Auction functionality will be here with dynamic item display.</p>
                </div>
            </div>
        </section>

        <!-- User Management -->
        <?php if ($roleID == 1 || $roleID == 2) { ?>
        <section id="user-management" class="mt-4">
            <h2>User Management</h2>
            <div class="card animate-form">
                <div class="card-body">
                    <h5 class="card-title">Create User</h5>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="userUsername">Username</label>
                            <input type="text" class="form-control" id="userUsername" name="userUsername" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label for="userPassword">Password</label>
                            <input type="password" class="form-control" id="userPassword" name="userPassword" placeholder="Enter password" required>
                        </div>
                        <div class="form-group">
                            <label for="userEmail">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="userEmail" placeholder="Enter email" required>
                        </div>
                        <div class="form-group">
                            <input type="hidden" class="form-control" id="userRoleID" name="userRoleID" value="3" readonly>
                        </div>
                        <button type="submit" name="createUser" class="btn btn-primary">Create User</button>
                    </form>
                </div>
            </div>
        </section>
        <?php } ?>

      <!-- Manager Control -->
<?php if ($roleID == 1) { ?>
<section id="manager-control" class="mt-4">
    <h2>Manager Control</h2>
    <div class="card animate-form">
        <div class="card-body">
            <h5 class="card-title">Create Manager</h5>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="managerUsername">Username</label>
                    <input type="text" class="form-control" id="managerUsername" name="managerUsername" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label for="managerPassword">Password</label>
                    <input type="password" class="form-control" id="managerPassword" name="managerPassword" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                    <label for="managerEmail">Email</label>
                    <input type="email" class="form-control" id="managerEmail" name="managerEmail" placeholder="Enter email" required>
                </div>
                <div class="form-group">
                    <input type="hidden" class="form-control" id="roleID" name="roleID" value="2" readonly>
                </div>
                <button type="submit" name="createManager" class="btn btn-primary">Create Manager</button>
            </form>
        </div>
    </div>
</section>
<?php } ?>

<div class="footer">
    <p>&copy; 2024 Auction Management System. All rights reserved.</p>
    <a href="#" class="text-white">Terms & Conditions</a> |
    <a href="#" class="text-white">Privacy Policy</a> |
    <a href="#" class="text-white">Contact Us</a> |
    <a href="#" class="text-white">Help & Support</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('userProfileButton').addEventListener('click', function () {
        var myModal = new bootstrap.Modal(document.getElementById('profileModal'), {
            keyboard: true
        });
        myModal.show();
    });
</script>
</body>
</html>