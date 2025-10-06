<?php

include 'config.php';
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

include 'config.php';
if (isset($_GET['id'])) {
  $id = (int)$_GET['id'];
  $conn->query("DELETE FROM tugas WHERE id = $id");
  header("Location: index.php?msg=" . urlencode("Tugas berhasil dihapus."));
  exit;
}
header("Location: index.php");
exit;
?>
