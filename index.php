<?php
include 'config.php';

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}


$msg = "";
if (isset($_GET['msg'])) {
  $msg = htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8');
}


$allowedSort = [
  'created' => 'created_at DESC',
  'prioritas' => "FIELD(prioritas, 'Tinggi', 'Sedang', 'Rendah')",
  'jatuh_tempo' => 'tanggal_jatuh_tempo ASC'
];
$orderBy = $allowedSort[$_GET['sort'] ?? 'created'] ?? 'created_at DESC';


$search = "";
$where = "WHERE 1";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
  $search = trim($_GET['search']);
  $where .= " AND (task_name LIKE ? OR desk LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $types .= "ss";
}


$query = "SELECT * FROM tugas $where ORDER BY $orderBy";
$stmt = $conn->prepare($query);

// Bind parameter hanya jika ada pencarian
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Tugas</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
  .notif { background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px; }
  .soon-due { background-color: #fff3cd; }
  .overdue { background-color: #f8d7da; }
  .completed { text-decoration: line-through; color: #777; }
</style>
</head>
<body>

<header>
  <div class="banner">
    <img src="assets/banner.jpg" alt="Task Manager Banner">
  </div>
  <div class="user-info">
    <img src="assets/profile.jpeg" class="avatar" alt="User Avatar">
    <span><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
    <a href="logout.php" class="logout">Logout</a>
  </div>
</header>

<h1>Daftar Tugas</h1>

<?php if ($msg): ?>
  <div class="notif"><?= $msg ?></div>
<?php endif; ?>

<a href="add_task.php" class="btn btn-add">+ Tambah Tugas</a>

<form method="get" class="search-form">
  <input type="text" name="search" placeholder="Cari nama atau deskripsi..."
         value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
  <label>Urutkan:</label>
  <select name="sort" onchange="this.form.submit()">
    <option value="created" <?= ($_GET['sort'] ?? '')=='created' ? 'selected' : '' ?>>Tanggal Dibuat</option>
    <option value="prioritas" <?= ($_GET['sort'] ?? '')=='prioritas' ? 'selected' : '' ?>>Prioritas</option>
    <option value="jatuh_tempo" <?= ($_GET['sort'] ?? '')=='jatuh_tempo' ? 'selected' : '' ?>>Tanggal Jatuh Tempo</option>
  </select>
  <button type="submit">Cari</button>
</form>


<table>
  <tr>
    <th>Nama Tugas</th>
    <th>Deskripsi</th>
    <th>Status</th>
    <th>Prioritas</th>
    <th>Tanggal Dibuat</th>
    <th>Jatuh Tempo</th>
    <th>Aksi</th>
  </tr>

  <?php
  $today = new DateTime();
  while ($row = $result->fetch_assoc()):
      $completed = ($row['status'] === 'Completed');
      $class = $completed ? 'completed' : '';

      if (!$completed && $row['tanggal_jatuh_tempo']) {
          $dueDate = new DateTime($row['tanggal_jatuh_tempo']);
          $interval = $today->diff($dueDate)->days;
          $isPast = ($dueDate < $today);
          if ($isPast) $class .= " overdue";
          elseif ($interval <= 3) $class .= " soon-due";
      }
  ?>
  <tr class="<?= $class ?>">
    <td><?= htmlspecialchars($row['task_name'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['desk'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['prioritas'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= $row['tanggal_jatuh_tempo'] ? htmlspecialchars($row['tanggal_jatuh_tempo'], ENT_QUOTES, 'UTF-8') : '-' ?></td>
    <td>
      <a class="btn btn-complete"
         href="update_status.php?id=<?= urlencode($row['id']) ?>&status=<?= $completed ? 'Uncompleted' : 'Completed' ?>">
         <?= $completed ? 'Batalkan' : 'Selesai' ?>
      </a>
      <a class="btn btn-edit" href="edit_task.php?id=<?= urlencode($row['id']) ?>">Edit</a>
      <a class="btn btn-delete" href="delete_task.php?id=<?= urlencode($row['id']) ?>" onclick="return confirm('Hapus tugas ini?')">Hapus</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
