<?php

function generateOrderValues($startOrderID, $numOrders) {
    $orderValues = [];

    for ($i = 0; $i < $numOrders; $i++) {
        $orderID = $startOrderID + $i;
        $customerID = rand(1, 10);
        $productID = rand(1, 10);
        $orderDate = date('Y-m-d', strtotime("2023-01-01 + $i days"));
        $totalAmount = number_format(rand(1500, 5000) / 100, 2);

        $orderValues[] = "($orderID, $customerID, $productID, '$orderDate', $totalAmount)";
    }

    return implode(",\n    ", $orderValues);
}

$startOrderID = 101;
$numOrders = 50; // You can change this to the desired number of orders

$sqlStatement = "INSERT INTO Orders (OrderID, CustomerID, ProductID, OrderDate, TotalAmount)\nVALUES\n";
$sqlStatement .= generateOrderValues($startOrderID, $numOrders) . ";\n";

echo $sqlStatement;
