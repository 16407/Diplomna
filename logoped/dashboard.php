<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'logoped') {
  header('Location: ../index.php');
  exit;
}

// Име на логопеда за поздрава
$userName = htmlspecialchars($_SESSION['user_name'] ?? '');

// Днешна дата
$today = date('Y-m-d');

// Сумираме метриките
$totalStudents = $dbh->query("SELECT COUNT(*) FROM students")->fetchColumn();
$classesCount  = $dbh->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$todayExams    = $dbh->prepare("SELECT COUNT(*) FROM examinations WHERE logoped_id = :lid AND DATE(exam_date)=:today");
$todayExams->execute([':lid'=>$_SESSION['user_id'],':today'=>$today]);
$todayExamsCount = $todayExams->fetchColumn();
$totalMaterials = $dbh->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$upcomingExams = $dbh->prepare("SELECT COUNT(*) FROM examinations WHERE logoped_id = :lid AND exam_date > NOW()");
$upcomingExams->execute([':lid'=>$_SESSION['user_id']]);
$upcomingExamsCount = $upcomingExams->fetchColumn();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
      <!-- Приветствие -->
      <div class="welcome-msg">
        Здравей, <strong><?= $userName ?></strong>!
      </div>

      <section class="dash-overview">
        <h2 class="overview-title"><i class="fa fa-tachometer"></i> Административно табло</h2>
        <div class="overview-cards">
          <div class="overview-card">
            <i class="fa fa-child card-icon"></i>
            <div class="card-info">
              <div class="card-number"><?= $totalStudents ?></div>
              <div class="card-label">Ученици</div>
            </div>
            <a href="kids.php" class="card-link">Управление &rarr;</a>
          </div>
          <div class="overview-card">
            <i class="fa fa-calendar-check-o card-icon"></i>
            <div class="card-info">
              <div class="card-number"><?= $todayExamsCount ?></div>
              <div class="card-label">Днешни прегледи</div>
            </div>
            <a href="schedule.php?date=<?= $today ?>" class="card-link">Виж график &rarr;</a>
          </div>

        <!-- ТУК Е НОВАТА КАРТА -->
  <div class="overview-card">
    <i class="fa fa-folder-open card-icon"></i>
    <div class="card-info">
      <div class="card-number"><?= $totalMaterials ?></div>
      <div class="card-label">Материали</div>
    </div>
    <a href="materials.php" class="card-link">Управление &rarr;</a>
  </div>
         
  <!-- <div class="overview-card">
            <i class="fa fa-list card-icon"></i>
            <div class="card-info">
              <div class="card-number"><?= $classesCount ?></div>
              <div class="card-label">Класове</div>
            </div>
            <a href="classes_students.php" class="card-link">Управление &rarr;</a>
          </div> -->
        </div>
      </section>
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