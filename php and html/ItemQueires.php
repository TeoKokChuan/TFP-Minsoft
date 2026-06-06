<?php

function getOrderItems($conn, $bill_id)
{
    $stmt = $conn->prepare("
        SELECT
            bt.transaction_ID,
            bt.Product_ID,
            bt.quantity,
            bt.unitPrice,
            bt.subtotal,
            bt.item_status,
            bt.refund_review,
            p.Product_name,
            p.imageUrl
        FROM bill_transaction bt
        JOIN product p
        ON bt.Product_ID = p.Product_ID
        WHERE bt.Bill_ID = ?
    ");

    $stmt->bind_param("i",$bill_id);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}