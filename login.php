<?php
include 'config.php';

// Jalankan session (pastikan hanya sekali)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitasi input
  $username = htmlspecialchars(trim($_POST['username']));
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['username'] = $user['username'];
      header("Location: index.php?msg=login_success");
      exit;
    } else {
      $error = "Password salah!";
    }
  } else {
    $error = "Username tidak ditemukan!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="assets/css/style_login.css"> <!-- CSS dipisah -->
</head>
<body>
<div class="login-box">
  <h2>Login</h2>

  <!-- Pesan sukses setelah register -->
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'register_success'): ?>
    <div class="success">Akun berhasil dibuat! Silakan login.</div>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Masuk</button>
  </form>
  
  <a href="register.php" class="register-link">Belum punya akun? Daftar di sini</a>
  
  <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
</div>
</body>
</html>
