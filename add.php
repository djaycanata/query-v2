<?php
$servername = "localhost";
$username = "root";
$password = "admin@db";
$dbname = "dev2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch products from the database
$sql = "SELECT ProductID, ProductName FROM Products";
$result = $conn->query($sql);

// Check if there are products
if ($result->num_rows > 0) {
    // Fetch products into an array
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    // Handle the case where there are no products
    $products = array();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Order</title>
</head>
<body>

<h2>Add New Order</h2>

<form method="post" action="process_order.php">
    <!-- Customer Information -->
    <label for="first_name">First Name:</label>
    <input type="text" name="first_name" required><br>

    <label for="last_name">Last Name:</label>
    <input type="text" name="last_name" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" required><br>

    <!-- Product Selection -->
    <label for="product_ids">Select Products:</label>
    <select name="product_ids[]" multiple required>
        <?php
        // Assume $products is an array of products fetched from the database
        foreach ($products as $product) {
            echo '<option value="' . $product["ProductID"] . '">' . $product["ProductName"] . '</option>';
        }
        ?>
    </select><br>

    <!-- Product Details for Each Selected Product -->
    <div id="productDetailsContainer">
        <!-- JavaScript will dynamically add product details fields here -->
    </div>

    <!-- Order Date -->
    <label for="order_date">Order Date:</label>
    <input type="date" name="order_date" required><br>

    <input type="submit" value="Add Order">
</form>

<script>
// JavaScript to dynamically add product details fields
document.addEventListener('DOMContentLoaded', function() {
    const productSelection = document.querySelector('select[name="product_ids[]"]');
    const productDetailsContainer = document.getElementById('productDetailsContainer');

    productSelection.addEventListener('change', function() {
        productDetailsContainer.innerHTML = ''; // Clear previous details

        // Create input fields for each selected product
        for (const option of productSelection.selectedOptions) {
            const productId = option.value;
            const productLabel = option.text;

            const productDetailFields = `
                <div>
                    <label>${productLabel} Quantity:</label>
                    <input type="number" name="product_quantities[${productId}]" required>
                </div>
            `;

            productDetailsContainer.innerHTML += productDetailFields;
        }
    });
});
</script>

</body>
</html>
