<?php
session_start();
include('../includes/dbconnection.php');

if ($_SESSION['user_role'] !== 'logoped') {
    header('Location: ../index.php');
    exit;
}

$error   = '';
$success = '';

// Зареждаме класовете
$classes = $dbh
    ->query("SELECT id,name FROM classes ORDER BY name")
    ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Полета от формата
    $name        = trim($_POST['name']);
    $class_id    = (int)$_POST['class_id'];
    $address     = trim($_POST['address']);
    $parent_name = trim($_POST['parent_name']);
    $parent_phone= trim($_POST['parent_phone']);

    if (!$name || !$class_id || !$address || !$parent_name || !$parent_phone) {
        $error = 'Всички полета са задължителни.';
    } else {
        // Папка за качване на профилна снимка
        $uploadDir = __DIR__ . '/../images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $profileImage = null;
        if (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fn = uniqid() . '-' . basename($_FILES['profile_image']['name']);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fn);
            $profileImage = $fn;
        }

        // Вмъкваме ученика с profile_image
        $ins = $dbh->prepare("
            INSERT INTO students
              (name, class_id, address, parent_name, parent_phone, profile_image)
            VALUES (:nm, :cid, :adr, :par, :pho, :prf)
        ");
        $ins->execute([
            ':nm'  => $name,
            ':cid' => $class_id,
            ':adr' => $address,
            ':par' => $parent_name,
            ':pho' => $parent_phone,
            ':prf' => $profileImage
        ]);

        $success = 'Ученикът беше успешно записан.';
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Добавяне на ученик</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
    <section class="ap-section">

       <div class="ap-header">
          <h3>Добавяне на нов ученик</h3>
          <a id="ap-back-btn" href="kids.php" class="ap-back-btn">
            <i class="fa fa-arrow-left"></i> Назад
          </a>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="ap-form">
          <div class="ap-field-group">
            <label>Име на ученика</label>
            <input type="text" name="name" required>
          </div>

          <div class="ap-field-group">
            <label>Клас</label>
            <select name="class_id" required>
              <option value="">— изберете —</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="ap-field-group">
            <label>Адрес</label>
            <input type="text" name="address" required>
          </div>

          <div class="ap-field-group">
            <label>Име на родител</label>
            <input type="text" name="parent_name" required>
          </div>

          <div class="ap-field-group">
            <label>Телефон на родител</label>
            <input type="text" name="parent_phone" required>
          </div>

          <div class="ap-field-group">
            <label>Профилна снимка</label>
            <input type="file" name="profile_image" accept="image/*">
          </div>

          <button type="submit" class="ap-btn">Запази</button>

        </form>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('sidebarToggle').addEventListener('click', () => {
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click', () => {
      document.querySelector('.dash-sidebar').classList.remove('open');
    });
  </script>
</body>
</html>