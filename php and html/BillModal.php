<?php

function getBill($conn, $bill_id)
{
    $stmt = $conn->prepare("
        SELECT Shipping_address,
               Bill_Status
        FROM bill_master
        WHERE Bill_ID = ?
    ");

    $stmt->bind_param("i",$bill_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}