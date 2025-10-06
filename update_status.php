<?php
include 'config.php';
session_start();

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];

    // Validasi nilai enum agar tidak bisa di-inject
    if (!in_array($status, ['Completed', 'Uncompleted'])) {
        header("Location: index.php?msg=" . urlencode("Status tidak valid!"));
        exit;
    }

    // Update status
    $stmt = $conn->prepare("UPDATE tugas SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    // Buat pesan
    $msg = ($status === 'Completed') ? "Tugas telah diselesaikan!" : "Tugas dikembalikan menjadi belum selesai.";
    header("Location: index.php?msg=" . urlencode($msg));
    exit;
}

header("Location: index.php");
exit;
?>
