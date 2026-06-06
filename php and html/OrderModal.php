<?php

function getOrder($conn, $order_id, $user_id)
{
    $stmt = $conn->prepare("
        SELECT Order_ID,
               orderStatus,
               orderDate,
               TotalPrice,
               PaymentMethod
        FROM customer_order
        WHERE Order_ID = ?
        AND User_ID = ?
    ");

    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc();
}