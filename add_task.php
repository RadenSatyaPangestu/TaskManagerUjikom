<?php
session_start();
include 'config.php';

// Cegah akses tanpa login
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $task_name = trim($_POST['task_name']);
  $desk = trim($_POST['desk']);
  $prioritas = $_POST['prioritas'];
  $status = $_POST['status'];
  $tanggal_jatuh_tempo = !empty($_POST['tanggal_jatuh_tempo']) ? $_POST['tanggal_jatuh_tempo'] : null;

  // ✅ Validasi server-side
  $errors = [];

  if (empty($task_name)) {
    $errors[] = "Nama tugas tidak boleh kosong.";
  }
  if (!in_array($prioritas, ['Tinggi', 'Sedang', 'Rendah'])) {
    $errors[] = "Prioritas tidak valid.";
  }
  if (!in_array($status, ['Completed', 'Uncompleted'])) {
    $errors[] = "Status tidak valid.";
  }

  if (count($errors) === 0) {
    $stmt = $conn->prepare("INSERT INTO tugas (user_id, task_name, desk, prioritas, status, tanggal_jatuh_tempo)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $task_name, $desk, $prioritas, $status, $tanggal_jatuh_tempo);
    $stmt->execute();

    header("Location: index.php?msg=" . urlencode("Tugas berhasil ditambahkan!"));
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Tugas</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <form method="post" id="taskForm">
    <h2>Tambah Tugas</h2>

    <?php if (!empty($errors)): ?>
      <div class="error-box">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <label>Nama Tugas</label>
    <input type="text" name="task_name" required minlength="3" placeholder="Masukkan nama tugas">

    <label>Deskripsi</label>
    <textarea name="desk" placeholder="Deskripsi tugas..."></textarea>

    <label>Status</label>
    <select name="status" required>
      <option value="Uncompleted">Belum</option>
      <option value="Completed">Selesai</option>
    </select>

    <label>Prioritas</label>
    <select name="prioritas" required>
      <option value="Tinggi">Tinggi</option>
      <option value="Sedang" selected>Sedang</option>
      <option value="Rendah">Rendah</option>
    </select>

    <label>Tanggal Jatuh Tempo</label>
    <input type="date" name="tanggal_jatuh_tempo">

    <button type="submit">Simpan</button>
    <a href="index.php">Kembali</a>
  </form>

  <script>
    // ✅ Validasi Client-side
    document.getElementById("taskForm").addEventListener("submit", (e) => {
      const name = document.querySelector("[name='task_name']").value.trim();
      if (name.length < 3) {
        alert("Nama tugas minimal 3 karakter!");
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
