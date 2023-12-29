<?php
$servername = "localhost";
$username = "root";
$password = "admin@db";
$dbname = "dev2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve customer information
$firstName = isset($_POST['first_name']) ? $_POST['first_name'] : '';
$lastName = isset($_POST['last_name']) ? $_POST['last_name'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';

// Insert customer information into the Customers table
$sqlInsertCustomer = "INSERT INTO Customers (FirstName, LastName, Email) VALUES (?, ?, ?)";
$stmtInsertCustomer = $conn->prepare($sqlInsertCustomer);
$stmtInsertCustomer->bind_param("sss", $firstName, $lastName, $email);
$resultInsertCustomer = $stmtInsertCustomer->execute();

// Check if the customer insertion was successful
if (!$resultInsertCustomer) {
    die("Error inserting into Customers table: " . $stmtInsertCustomer->error);
}

// Get the CustomerID of the newly inserted customer
$customerID = $conn->insert_id;

// Process selected products
$productQuantities = isset($_POST['product_quantities']) ? $_POST['product_quantities'] : array();

// Insert order details into the Orders table
$sqlInsertOrder = "INSERT INTO Orders (CustomerID, OrderDate, TotalAmount) VALUES (?, NOW(), 0)";
$stmtInsertOrder = $conn->prepare($sqlInsertOrder);
$stmtInsertOrder->bind_param("i", $customerID);
$resultInsertOrder = $stmtInsertOrder->execute();

// Check if the order insertion was successful
if (!$resultInsertOrder) {
    die("Error inserting into Orders table: " . $stmtInsertOrder->error);
}

// Get the OrderID of the newly inserted order
$orderID = $conn->insert_id;

// Calculate total amount and insert product details into the OrderDetails table
$totalAmount = 0; // Initialize totalAmount

foreach ($productQuantities as $productID => $quantity) {
    // Ensure $quantity is a scalar value (e.g., integer)
    if (!is_scalar($quantity)) {
        // Handle the case where $quantity is not a scalar value (e.g., set a default value)
        $quantity = 0;
    }

    $sqlProduct = "SELECT Price FROM Products WHERE ProductID = ?";
    $stmtProduct = $conn->prepare($sqlProduct);
    $stmtProduct->bind_param("i", $productID);
    $stmtProduct->execute();
    $resultProduct = $stmtProduct->get_result();

    if ($resultProduct->num_rows > 0) {
        $productData = $resultProduct->fetch_assoc();
        $price = $productData['Price'];
        $totalAmount += $price * $quantity;

        $sqlInsertOrderDetails = "INSERT INTO OrderDetails (OrderID, ProductID, Quantity, Price) VALUES (?, ?, ?, ?)";
        $stmtInsertOrderDetails = $conn->prepare($sqlInsertOrderDetails);
        $stmtInsertOrderDetails->bind_param("iiid", $orderID, $productID, $quantity, $price);
        $resultInsertOrderDetails = $stmtInsertOrderDetails->execute();

        // Check if the order details insertion was successful
        if (!$resultInsertOrderDetails) {
            die("Error inserting into OrderDetails table: " . $stmtInsertOrderDetails->error);
        }
    } else {
        die("Error fetching product information: " . $conn->error);
    }
}

// Update total amount in the Orders table
$sqlUpdateOrderAmount = "UPDATE Orders SET TotalAmount = ? WHERE OrderID = ?";
$stmtUpdateOrderAmount = $conn->prepare($sqlUpdateOrderAmount);
$stmtUpdateOrderAmount->bind_param("di", $totalAmount, $orderID);
$resultUpdateOrderAmount = $stmtUpdateOrderAmount->execute();

// Check if the update was successful
if (!$resultUpdateOrderAmount) {
    die("Error updating total amount in Orders table: " . $stmtUpdateOrderAmount->error);
}

// Close the database connection
$conn->close();

// Redirect or display success message as needed
header("Location: success.php");
exit();
?>
