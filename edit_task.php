<?php
session_start();
include 'config.php';

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pastikan ID tugas ada
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();

if (!$task) {
    header("Location: index.php?msg=" . urlencode("Tugas tidak ditemukan!"));
    exit;
}

// Proses jika form disubmit
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['task_name']);
    $desk = trim($_POST['desk']);
    $prioritas = $_POST['prioritas'];
    $tanggal = $_POST['tanggal_jatuh_tempo'] ?: null;

    // ✅ Validasi server-side
    if (empty($name)) {
        $errors[] = "Nama tugas tidak boleh kosong.";
    } elseif (strlen($name) < 3) {
        $errors[] = "Nama tugas minimal 3 karakter.";
    }

    if (!in_array($prioritas, ['Tinggi', 'Sedang', 'Rendah'])) {
        $errors[] = "Prioritas tidak valid.";
    }

    if ($tanggal && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $errors[] = "Format tanggal tidak valid.";
    }

    // Jika tidak ada error → update
    if (count($errors) === 0) {
        $update = $conn->prepare("UPDATE tugas 
                                  SET task_name = ?, desk = ?, prioritas = ?, tanggal_jatuh_tempo = ? 
                                  WHERE id = ?");
        $update->bind_param("ssssi", $name, $desk, $prioritas, $tanggal, $id);
        $update->execute();

        header("Location: index.php?msg=" . urlencode("Tugas berhasil diperbarui!"));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Tugas</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <form method="post" id="editTaskForm">
    <h2>Edit Tugas</h2>

    <?php if (!empty($errors)): ?>
      <div class="error-box">
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <label>Nama Tugas:</label>
    <input type="text" name="task_name" value="<?= htmlspecialchars($task['task_name']) ?>" required minlength="3">

    <label>Deskripsi:</label>
    <textarea name="desk"><?= htmlspecialchars($task['desk']) ?></textarea>

    <label>Prioritas:</label>
    <select name="prioritas" required>
      <option value="Tinggi" <?= $task['prioritas'] === 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
      <option value="Sedang" <?= $task['prioritas'] === 'Sedang' ? 'selected' : '' ?>>Sedang</option>
      <option value="Rendah" <?= $task['prioritas'] === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
    </select>

    <label>Tanggal Jatuh Tempo:</label>
    <input type="date" name="tanggal_jatuh_tempo"
      value="<?= htmlspecialchars($task['tanggal_jatuh_tempo'] ? date('Y-m-d', strtotime($task['tanggal_jatuh_tempo'])) : '') ?>">

    <button type="submit">Simpan Perubahan</button>
    <a href="index.php">Kembali</a>
  </form>

  <script>
    // ✅ Validasi Client-side
    document.getElementById("editTaskForm").addEventListener("submit", (e) => {
      const name = document.querySelector("[name='task_name']").value.trim();
      if (name.length < 3) {
        alert("Nama tugas minimal 3 karakter!");
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
