<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'logoped') {
  header('Location: ../index.php');
  exit;
}

// Днешна дата
$today = date('Y-m-d');
$displayDate = date('d.m.Y');

// Брой прегледи днес
$stmt = $dbh->prepare("
    SELECT COUNT(*)
    FROM examinations
    WHERE logoped_id = :lid
      AND DATE(exam_date) = :today
");
$stmt->execute([
  ':lid' => $_SESSION['user_id'],
  ':today' => $today
]);
$todayExamsCount = $stmt->fetchColumn();

// Брой нови ученици днес
$stmt = $dbh->prepare("
    SELECT COUNT(*)
    FROM students
    WHERE DATE(created_at) = :today
");
$stmt->execute([':today' => $today]);
$todayStudentsCount = $stmt->fetchColumn();

// Зареждаме учениците за картите
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Отчет и Ученици – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>


<body >

  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
      <!-- Отчет на дейност -->
      <section class="report-section">
  <div class="report-header">Отчет на дейност</div>
  <div class="cards-row cards-row--single">
    <div class="report-card date-card">
      <h4>Днешна дата</h4>
      <p><?= htmlspecialchars($displayDate) ?></p>
    </div>
  </div>
</section>

      <!-- Карти на ученици -->
      <h2 class="students-header">Ученици</h2>
<div class="students-grid">
  <?php foreach ($students as $s): ?>
    <div class="student-card">
      <a href="report_details.php?id=<?= $s['id'] ?>" class="student-card__link">
        <img src="../images/<?= htmlspecialchars($s['profile_image'] ?: '../img/default-avatar.png') ?>"
             class="student-card__avatar" alt="">
        <div class="student-card__name"><?= htmlspecialchars($s['student_name']) ?></div>
        <div class="student-card__class"><?= htmlspecialchars($s['class_name']) ?></div>
      </a>
      <div class="student-card__actions">
        <a href="edit_kid.php?id=<?= $s['id'] ?>">Редактирай</a>
        <form method="POST" onsubmit="return confirm('Наистина ли?')">
          <input type="hidden" name="delete_student_id" value="<?= $s['id'] ?>">
          <button type="submit">Изтрий</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <div class="student-card--add" onclick="location.href='add_kid.php'">
    +<small>Нов ученик</small>
  </div>
</div>
  <script>
    const toggleBtn = document.getElementById('sidebarToggle'),
      sidebar = document.querySelector('.dash-sidebar'),
      content = document.querySelector('.dash-content');
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    content.addEventListener('click', () => sidebar.classList.remove('open'));
  </script>
</body>

</html>