<?php
session_start();
include('../includes/dbconnection.php');
if ($_SESSION['user_role']!=='logoped') {
  header('Location: ../index.php');
  exit;
}
if (empty($_GET['id'])) {
  header('Location: kids.php');
  exit;
}
$id = (int)$_GET['id'];

// 1) Обработка на качване
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755);

function handleUpload($field, $destDir) {
  if (empty($_FILES[$field]) || $_FILES[$field]['error']!==UPLOAD_ERR_OK) {
    return null;
  }
  $fn = uniqid() . '-' . basename($_FILES[$field]['name']);
  move_uploaded_file($_FILES[$field]['tmp_name'], $destDir . $fn);
  return $fn;
}

$success = '';
$error   = '';

// Ако се пусне form за карта
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upload_card'])) {
  $file = handleUpload('card_file', $uploadDir);
  if ($file) {
    $dbh->prepare("UPDATE students SET doc1 = :f WHERE id = :id")
        ->execute([':f'=>$file, ':id'=>$id]);
    $success = 'Логопедичната карта е прикачена.';
  } else {
    $error = 'Моля изберете файл за логопедична карта.';
  }
}

// Ако се пусне form за оценка
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['upload_assessment'])) {
  $file = handleUpload('assessment_file', $uploadDir);
  if ($file) {
    $dbh->prepare("UPDATE students SET doc2 = :f WHERE id = :id")
        ->execute([':f'=>$file, ':id'=>$id]);
    $success = 'Логопедичната оценка е прикачена.';
  } else {
    $error = 'Моля изберете файл за логопедична оценка.';
  }
}

// 2) Зареждаме детайлите на ученика
$stmt = $dbh->prepare("
  SELECT s.*, c.name AS class_name
  FROM students s
  JOIN classes c ON s.class_id = c.id
  WHERE s.id = :id
");
$stmt->execute([':id'=>$id]);
$kid = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kid) {
  header('Location: kids.php');
  exit;
}

// 3) Зареждаме прегледите
$stmt = $dbh->prepare("
  SELECT id, topic, exam_date, notes
  FROM examinations
  WHERE student_id=:sid AND logoped_id=:lid
  ORDER BY exam_date DESC
");
$stmt->execute([':sid'=>$id,':lid'=>$_SESSION['user_id']]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Детайли – <?= htmlspecialchars($kid['name']) ?></title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <style>
    .kid-details { max-width:700px; margin:20px auto; background:#fff;
      padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.05); }
    .kid-header { display:flex; align-items:center; gap:20px; margin-bottom:20px; }
    .kid-header img { width:100px; height:100px; border-radius:50%; object-fit:cover; }
    .kid-info p { margin:4px 0; font-size:15px; }
    .upload-section { margin-top:24px; padding-top:16px; border-top:1px solid #eee; }
    .upload-section h4 { margin-bottom:12px; }
    .upload-form { display:flex; align-items:center; gap:8px; margin-bottom:12px; }
    .upload-form input[type="file"] { flex:1; }
    .exams-table { width:100%; border-collapse:collapse; margin-top:16px; }
    .exams-table th, .exams-table td { border:1px solid #eee; padding:8px; font-size:14px; }
    .exams-table thead th { background:var(--purple-dark); color:#fff; }
    .btn-back { margin-bottom:12px; display:inline-block; color:var(--purple-dark);
      text-decoration:none; font-size:14px; }

      .exams-table td a.btn-sm,
.exams-table td button.btn-delete-sm {
  display: inline-block !important;
  width: auto !important;
  margin-right: 8px;      /* разстояние между бутоните */
  margin-bottom: 0 !important;
}

/* Формата също inline, за да не слага бутона на нов ред */
.inline-form {
  display: inline-block !important;
  margin: 0 !important;
  padding: 0 !important;
}

  </style>
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__.'/sidebar.php'; ?>
    <main class="dash-content">
      <a href="kids.php" class="rd-back-link">&larr; Назад към ученици</a>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <div class="kid-details">
        <!-- Учeник информация -->
        <div class="kid-header">
          <img src="../images/<?= htmlspecialchars($kid['profile_image']?: '../img/default-avatar.png') ?>" alt="">
          <div class="kid-info">
            <h2><?= htmlspecialchars($kid['name']) ?></h2>
            <p><strong>Клас:</strong> <?= htmlspecialchars($kid['class_name']) ?></p>
            <p><strong>Адрес:</strong> <?= htmlspecialchars($kid['address']) ?></p>
            <p><strong>Родител:</strong> <?= htmlspecialchars($kid['parent_name']) ?></p>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($kid['parent_phone']) ?></p>
          </div>
        </div>

        <!-- Секция за прикачване на логопедична карта -->
        <div class="upload-section">
          <h4>Прикачи логопедична карта</h4>
          <form method="POST" enctype="multipart/form-data" class="upload-form">
            <input type="hidden" name="upload_card" value="1">
            <input type="file" name="card_file" accept=".pdf,.doc,.docx">
            <button type="submit" class="btn-sm">Качи</button>
          </form>
          <?php if ($kid['doc1']): ?>
            <p><a href="../uploads/<?= htmlspecialchars($kid['doc1']) ?>" target="_blank">
              Виж прикачена карта
            </a></p>
          <?php endif; ?>
        </div>

        <!-- Секция за прикачване на логопедична оценка -->
        <div class="upload-section">
          <h4>Прикачи логопедична оценка</h4>
          <form method="POST" enctype="multipart/form-data" class="upload-form">
            <input type="hidden" name="upload_assessment" value="1">
            <input type="file" name="assessment_file" accept=".pdf,.doc,.docx">
            <button type="submit" class="btn-sm">Качи</button>
          </form>
          <?php if ($kid['doc2']): ?>
            <p><a href="../uploads/<?= htmlspecialchars($kid['doc2']) ?>" target="_blank">
              Виж прикачена оценка
            </a></p>
          <?php endif; ?>
        </div>


        <!-- <p style="margin-top:12px;">
          <a href="add_exam.php?student_id=<?= $kid['id'] ?>" class="btn-sm">
            <i class="fa fa-plus"></i> Добави преглед
          </a>
        </p> -->
      </div>
    </main>
  </div>
  <script>
    document.getElementById('sidebarToggle').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.remove('open');
    });
  </script>
</body>
</html>