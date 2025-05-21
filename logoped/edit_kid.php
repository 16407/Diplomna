<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if ($_SESSION['user_role'] !== 'logoped') {
    header('Location: ../index.php');
    exit;
}

// Проверка на ID
if (empty($_GET['id'])) {
    header('Location: kids.php');
    exit;
}
$id = (int)$_GET['id'];

// Зареждаме списъка с класове
$classes = $dbh
    ->query("SELECT id,name FROM classes ORDER BY name")
    ->fetchAll(PDO::FETCH_ASSOC);

// Инициализираме променливи за грешки/успех
$error = '';
$success = '';

// Обработка на изпратена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $class_id    = (int)$_POST['class_id'];
    $address     = trim($_POST['address']);
    $parent_name = trim($_POST['parent_name']);
    $parent_phone= trim($_POST['parent_phone']);

    if (!$name || !$class_id || !$address || !$parent_name || !$parent_phone) {
        $error = 'Всички полета са задължителни.';
    } else {
        // Поправяме профилната снимка, ако е качена нова
        $profileImage = null;
        if (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fn = uniqid() . '-' . basename($_FILES['profile_image']['name']);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fn);
            $profileImage = $fn;
        }

        // Сглобяваме UPDATE
        $sql = "UPDATE students
                   SET name=:nm, class_id=:cid, address=:adr,
                       parent_name=:par, parent_phone=:pho";
        if ($profileImage !== null) {
            $sql .= ", profile_image=:prf";
        }
        $sql .= " WHERE id=:id";

        $upd = $dbh->prepare($sql);
        $params = [
            ':nm'=>$name, ':cid'=>$class_id, ':adr'=>$address,
            ':par'=>$parent_name, ':pho'=>$parent_phone,
            ':id'=>$id
        ];
        if ($profileImage !== null) {
            $params[':prf'] = $profileImage;
        }
        $upd->execute($params);

        $success = 'Данните на ученика бяха обновени.';
    }
}

// Зареждаме текущите данни на ученика
$stmt = $dbh->prepare("
  SELECT name, class_id, address, parent_name, parent_phone, profile_image
  FROM students WHERE id=:id
");
$stmt->execute([':id'=>$id]);
$kid = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kid) {
    header('Location: kids.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Редакция на ученик – <?= htmlspecialchars($kid['name']) ?></title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
      <a href="kids.php" class="btn-back">&larr; Обратно към ученици</a>

      <section class="ap-section">
        <h3>Редакция на ученик</h3>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="ap-form">
          <div class="ap-field-group">
            <label>Име на ученика</label>
            <input type="text" name="name" value="<?= htmlspecialchars($kid['name']) ?>" required>
          </div>

          <div class="ap-field-group">
            <label>Клас</label>
            <select name="class_id" required>
              <option value="">— изберете —</option>
              <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>"
                  <?= $c['id']==$kid['class_id']?'selected':''?>>
                  <?= htmlspecialchars($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="ap-field-group">
            <label>Адрес</label>
            <input type="text" name="address" value="<?= htmlspecialchars($kid['address']) ?>" required>
          </div>

          <div class="ap-field-group">
            <label>Име на родител</label>
            <input type="text" name="parent_name" value="<?= htmlspecialchars($kid['parent_name']) ?>" required>
          </div>

          <div class="ap-field-group">
            <label>Телефон на родител</label>
            <input type="text" name="parent_phone" value="<?= htmlspecialchars($kid['parent_phone']) ?>" required>
          </div>

          <div class="ap-field-group">
            <label>Профилна снимка</label>
            <?php if ($kid['profile_image']): ?>
              <div><img src="../images/<?=htmlspecialchars($kid['profile_image'])?>" style="max-width:120px;border-radius:4px;margin-bottom:8px"></div>
            <?php endif; ?>
            <input type="file" name="profile_image" accept="image/*">
          </div>

          <button type="submit" class="ap-btn">Запази промените</button>
        </form>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('sidebarToggle').addEventListener('click', ()=>{
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click', ()=>{
      document.querySelector('.dash-sidebar').classList.remove('open');
    });
  </script>
</body>
</html>