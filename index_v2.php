<?php

$servername = "localhost";
$username = "root";
$password = "admin@db";
$dbname = "dev2";

// Initialize selectedCustomerID to a default value (you can adjust this based on your requirements)
$selectedCustomerID = isset($_POST["customer_id"]) ? $_POST["customer_id"] : 1;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select data from Customers table for dropdown list
$sqlCustomers = "SELECT CustomerID, CONCAT(FirstName, ' ', LastName) AS CustomerName FROM Customers";
$resultCustomers = $conn->query($sqlCustomers);

$customerDropdown = '<form method="post" action="' . $_SERVER['PHP_SELF'] . '">';
$customerDropdown .= '<label for="customer_id">Select Customer:</label>';
$customerDropdown .= '<select name="customer_id">';
while ($row = $resultCustomers->fetch_assoc()) {
    $selected = ($row["CustomerID"] == $selectedCustomerID) ? 'selected' : '';
    $customerDropdown .= '<option value="' . $row["CustomerID"] . '" ' . $selected . '>' . $row["CustomerName"] . '</option>';
}
$customerDropdown .= '</select>';
$customerDropdown .= '<input type="submit" value="Submit">';
$customerDropdown .= '</form>';

echo "<h2>Customer Dropdown List:</h2>" . $customerDropdown;

// Display order details based on the selected customer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedCustomerID = $_POST["customer_id"];

    // Fetch the selected customer's name
    $customerNameQuery = "SELECT CONCAT(FirstName, ' ', LastName) AS CustomerName FROM Customers WHERE CustomerID = $selectedCustomerID";
    $customerNameResult = $conn->query($customerNameQuery);

    if ($customerNameResult->num_rows > 0) {
        $customerName = $customerNameResult->fetch_assoc()["CustomerName"];
        echo "<h2>Order Details for Customer $customerName:</h2>";
    } else {
        echo "<p>Error fetching customer name.</p>";
    }

    // Query to get order details based on the selected customer
    $orderDetailsQuery = "SELECT Orders.OrderID, Orders.OrderDate, Products.ProductName, OrderDetails.Quantity, OrderDetails.Price
                        FROM Orders
                        JOIN OrderDetails ON Orders.OrderID = OrderDetails.OrderID
                        JOIN Products ON OrderDetails.ProductID = Products.ProductID
                        WHERE Orders.CustomerID = $selectedCustomerID
                        ORDER BY Orders.OrderID, Products.ProductName";
    $orderDetailsResult = $conn->query($orderDetailsQuery);

    if ($orderDetailsResult->num_rows > 0) {
        echo '<table border="1">';
        echo '<tr><th>Order ID</th><th>Order Date</th><th>Product Name</th><th>Quantity</th><th>Price</th></tr>';

        $currentOrderID = null;
        $totalAmount = 0;

        while ($orderRow = $orderDetailsResult->fetch_assoc()) {
            // Check if the order ID has changed
            if ($orderRow["OrderID"] != $currentOrderID) {
                // If this is not the first row, print the total amount for the previous order
                if ($currentOrderID !== null) {
                    echo '<tr><td colspan="4" align="right">Total Price:</td><td>$' . number_format($totalAmount, 2) . '</td></tr>';
                }

                $currentOrderID = $orderRow["OrderID"];
                $totalAmount = 0;
            }

            // Print the regular row
            echo '<tr>';
            echo '<td>' . $orderRow["OrderID"] . '</td>';
            echo '<td>' . $orderRow["OrderDate"] . '</td>';
            echo '<td>' . $orderRow["ProductName"] . '</td>';
            echo '<td>' . $orderRow["Quantity"] . '</td>';
            echo '<td>$' . number_format($orderRow["Price"], 2) . '</td>';
            echo '</tr>';

            // Add the quantity and price to the total amount for the current order
            $totalAmount += $orderRow["Quantity"] * $orderRow["Price"];
        }

        // Print the total amount for the last order
        if ($currentOrderID !== null) {
            echo '<tr><td colspan="4" align="right">Total Price:</td><td>$' . number_format($totalAmount, 2) . '</td></tr>';
        }

        echo '</table>';
    } else {
        echo "<p>No orders found for the selected customer.</p>";
    }
}

// Close connection
$conn->close();

?>
