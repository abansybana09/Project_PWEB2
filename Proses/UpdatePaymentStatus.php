<?php
require_once __DIR__ . '/../Admin/Koneksi.php';
header('Content-Type: application/json');

$order_id = $_GET['order_id'] ?? '';
$status = ($_GET['status'] === 'success') ? 'Ya' : 'Pending';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

// Update berdasarkan midtrans_order_id
$sql = "UPDATE tb_order SET pembayaran = ? WHERE midtrans_order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $status, $order_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'affected_rows' => $stmt->affected_rows]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>