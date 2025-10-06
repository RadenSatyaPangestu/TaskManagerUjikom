<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitasi input
  $username = htmlspecialchars(trim($_POST['username']));
  $password_plain = trim($_POST['password']);

  // Validasi panjang password minimal 8 karakter
  if (strlen($password_plain) < 8) {
    $error = "Kata sandi minimal harus 8 karakter.";
  } else {
    // Enkripsi password
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $error = "Username sudah digunakan, silakan pilih yang lain.";
    } else {
      // Insert ke database
      $stmt = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
      $stmt->bind_param("ss", $username, $password);
      if ($stmt->execute()) {
        header("Location: login.php?msg=register_success");
        exit;
      } else {
        $error = "Terjadi kesalahan saat mendaftar.";
      }
      $stmt->close();
    }
    $check->close();
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link rel="stylesheet" href=assets/css/style_register.css> <!-- css dipisah -->
</head>
<body>
<div class="register-box">
  <h2>Daftar Akun</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'register_success'): ?>
    <div class="success">Akun berhasil dibuat! Silakan login.</div>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password (min 8 karakter)" required>
    <button type="submit">Daftar</button>
  </form>

  <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
</div>
</body>
</html>
