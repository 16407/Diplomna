<?php
session_start();
include('../includes/dbconnection.php');
if ($_SESSION['user_role'] !== 'logoped') {
  header('Location: ../index.php');
  exit;
}

// Обработка на изтриване
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
  $del = $dbh->prepare("DELETE FROM students WHERE id = :id");
  $del->execute([':id'=>$_POST['delete_student_id']]);
  header('Location: kids.php');
  exit;
}

// Зареждаме учениците и техните класове
$stmt = $dbh->query("
  SELECT s.id, s.name AS student_name, s.profile_image, c.name AS class_name
  FROM students s
  JOIN classes c ON s.class_id = c.id
  ORDER BY s.name
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ученици – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
    <h2 class="kd-header">Ученици</h2>
    <div class="kd-cards-grid">
      <?php foreach ($students as $s): ?>
        <div class="kd-card-student">
          <a href="kid_details.php?id=<?= $s['id'] ?>" class="kd-details-link">
            <img src="../images/<?= htmlspecialchars($s['profile_image'] ?: '../images/default-avatar.png') ?>"
                 class="profile" alt="">
            <div class="kd-student-name"><?= htmlspecialchars($s['student_name']) ?></div>
            <div class="kd-class-name"><?= htmlspecialchars($s['class_name']) ?></div>
          </a>
          <div class="kd-actions">
            <a href="edit_kid.php?id=<?= $s['id'] ?>">Редактирай</a>
            <form method="POST" onsubmit="return confirm('Наистина ли?')">
              <input type="hidden" name="delete_student_id" value="<?= $s['id'] ?>">
              <button type="submit">Изтрий</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <div class="kd-card-add" onclick="location.href='add_kid.php'">
        +<small>Добавяне на нов ученик</small>
      </div>
    </div>
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