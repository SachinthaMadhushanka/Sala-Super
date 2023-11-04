<?php

// Database connection details
$host = "localhost";
$dbname = "sala_super_db";
$user = "root";
$pass = "";
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$response = ['success' => false, 'message' => ''];

// Collect POST data
$postData = $_POST;

try {
  // Begin transaction
  $pdo->beginTransaction();

  // Insert into invoice table
  $invoiceSQL = "INSERT INTO invoice (date_time, subtotal, discount, total, payment_type, due, paid)
                   VALUES (:order_datetime, :sub_total, :discount, :total, :payment_method, :balance, :paid)";
  $stmt = $pdo->prepare($invoiceSQL);
  $stmt->execute([
    ':order_datetime' => $postData['order_datetime'],
    ':sub_total' => $postData['sub_total'],
    ':discount' => $postData['discount'],
    ':total' => $postData['total'],
    ':payment_method' => $postData['payment_method'],
    ':balance' => $postData['balance'],
    ':paid' => $postData['paid']
  ]);

  $lastInvoiceID = $pdo->lastInsertId();

  // Loop through each stock item and insert into invoice_details & update product_stock
  for ($i = 0; $i < count($postData['stock_ids']); $i++) {
    $stockID = $postData['stock_ids'][$i];
    $quantity = $postData['quantities'][$i];

    // Insert into invoice_details table
    $detailsSQL = "INSERT INTO invoice_details (invoice_id, stock_id, qty) VALUES (:invoice_id, :stock_id, :qty)";
    $stmt = $pdo->prepare($detailsSQL);
    $stmt->execute([
      ':invoice_id' => $lastInvoiceID,
      ':stock_id' => $stockID,
      ':qty' => $quantity
    ]);

    // Update product_stock
    $updateStockSQL = "UPDATE product_stock SET stock = stock - :qty WHERE id = :stock_id";
    $stmt = $pdo->prepare($updateStockSQL);
    $stmt->execute([
      ':qty' => $quantity,
      ':stock_id' => $stockID
    ]);

    // Check if stock becomes 0, then delete the record
    $checkStockSQL = "DELETE FROM product_stock WHERE stock = 0 AND id = :stock_id";
    $stmt = $pdo->prepare($checkStockSQL);
    $stmt->execute([':stock_id' => $stockID]);
  }

  $pdo->commit();

  $response['success'] = true;
  $response['message'] = "Order processed successfully!";
} catch (Exception $e) {
  $pdo->rollBack();
  $response['message'] = "Error processing the order: " . $e->getMessage();
}

echo json_encode($response);
?>
