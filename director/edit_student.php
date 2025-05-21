<?php
session_start();
include('../includes/dbconnection.php');

// Само за директор
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'director') {
    header('Location: ../index.php');
    exit;
}

// Ид на ученика
if (empty($_GET['id'])) {
    header('Location: students.php');
    exit;
}
$id = (int)$_GET['id'];

// Зареждаме класовете за селекта
$classes = $dbh->query("SELECT id,name FROM classes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Инициализиране
$error = '';
$success = '';

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $class_id    = (int)$_POST['class_id'];
    $address     = trim($_POST['address']);
    $parent_name = trim($_POST['parent_name']);
    $parent_phone= trim($_POST['parent_phone']);

    if (!$name || !$class_id || !$address || !$parent_name || !$parent_phone) {
        $error = 'Всички полета са задължителни.';
    } else {
        // Обработка на профилната снимка
        $profileImage = null;
        if (!empty($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $fn = uniqid() . '-' . basename($_FILES['profile_image']['name']);
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fn);
            $profileImage = $fn;
        }

        // Създаваме запитване
        $sql = "UPDATE students SET
                  name = :nm,
                  class_id = :cid,
                  address = :adr,
                  parent_name = :par,
                  parent_phone = :pho";
        if ($profileImage) {
            $sql .= ", profile_image = :prf";
        }
        $sql .= " WHERE id = :id";

        $stmt = $dbh->prepare($sql);
        $params = [
          ':nm'  => $name,
          ':cid' => $class_id,
          ':adr' => $address,
          ':par' => $parent_name,
          ':pho' => $parent_phone,
          ':id'  => $id
        ];
        if ($profileImage) {
            $params[':prf'] = $profileImage;
        }
        $stmt->execute($params);
        $success = 'Данните на ученика бяха обновени успешно.';
    }
}

// Зареждаме отново данните
$stmt = $dbh->prepare("
  SELECT name,address,parent_name,parent_phone,profile_image,class_id
  FROM students WHERE id = :id
");
$stmt->execute([':id'=>$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    header('Location: students.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Редактиране на ученик – Директор</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="dir-main-content">
    <section class="dash-section">
      <h3><i class="fa fa-edit"></i> Редактиране на ученик</h3>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="dash-form">
        <div class="form-group">
          <label for="stu-name">Име</label>
          <input id="stu-name" name="name" type="text"
                 value="<?= htmlspecialchars($student['name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="stu-class">Клас</label>
          <select id="stu-class" name="class_id" required>
            <option value="">— изберете —</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= $c['id'] ?>"
                <?= $c['id']==$student['class_id']?'selected':''?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="stu-address">Адрес</label>
          <input id="stu-address" name="address" type="text"
                 value="<?= htmlspecialchars($student['address']) ?>" required>
        </div>

        <div class="form-group">
          <label for="stu-parent">Име на родител</label>
          <input id="stu-parent" name="parent_name" type="text"
                 value="<?= htmlspecialchars($student['parent_name']) ?>" required>
        </div>

        <div class="form-group">
          <label for="stu-phone">Телефон на родител</label>
          <input id="stu-phone" name="parent_phone" type="text"
                 value="<?= htmlspecialchars($student['parent_phone']) ?>" required>
        </div>

        <div class="form-group">
          <label>Сегашна снимка</label><br>
          <?php if ($student['profile_image']): ?>
            <img src="../images/<?= htmlspecialchars($student['profile_image']) ?>"
                 alt="" class="dir-stu-avatar">
          <?php else: ?>
            <span class="text-muted">Няма</span>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="stu-photo">Смяна на снимка</label>
          <input id="stu-photo" name="profile_image" type="file" accept="image/*">
        </div>

        <div class="form-group">
          <button type="submit" class="btn-sm">Запази</button>
          <a href="students.php" class="btn-sm btn-secondary">Отказ</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>