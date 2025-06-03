<?php

require 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $itemName = $_POST['item'];
  $qty = (int)$_POST['qty'];
  
  $stmt = $dbcon->prepare("SELECT id, price, stock FROM menu_items WHERE name = ?");
  $stmt->bind_param("s", $itemName);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 0) {
    echo "<h2>Menu tidak ditemukan.</h2>";
    exit;
  }

  $item = $result->fetch_assoc();
  $itemId = $item['id'];
  $price = $item['price'];
  $stock = $item['stock'];
  $total = $price * $qty;

  if ($stock < $qty) {
    echo "<h2>Stok tidak mencukupi. Stok tersisa: $stock</h2>";
    exit;
  }

  // Reduce stock
  $newStock = $stock - $qty;
  $updateStock = $dbcon->prepare("UPDATE menu_items SET stock = ? WHERE id = ?");
  $updateStock->bind_param("ii", $newStock, $itemId);
  $updateStock->execute();

  // Save to orders table
  $insertOrder = $dbcon->prepare("INSERT INTO orders (item, quantity, total_price) VALUES (?, ?, ?)");
  $insertOrder->bind_param("sii", $itemName, $qty, $total);
  $insertOrder->execute();

  echo "<h1>Terima kasih telah membeli $itemName sebanyak $qty. Total: Rp$total</h1>, sisa $newStock";
  echo "<img src='images/dummy-qr.png' alt='QR Code' style='width: 200px;'><br><br>";
}
?>
