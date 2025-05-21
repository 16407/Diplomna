<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'logoped') {
    header('Location: ../index.php');
    exit;
}

// Проверяваме дали ид идва като GET
if (empty($_GET['id'])) {
    header('Location: report.php');
    exit;
}
$examId = (int)$_GET['id'];
$error = '';
$success = '';

// Зареждаме записа
$stmt = $dbh->prepare("
    SELECT id, student_id, topic, DATE_FORMAT(exam_date, '%d.%m.%Y %H:%i') AS exam_time
    FROM examinations
    WHERE id = :id
      AND logoped_id = :lid
    LIMIT 1
");
$stmt->execute([
    ':id'  => $examId,
    ':lid' => $_SESSION['user_id'],
]);
$exam = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$exam) {
    header('Location: report.php');
    exit;
}

// При POST заявка — обновяваме темата
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic'])) {
    $newTopic = trim($_POST['topic']);
    if ($newTopic === '') {
        $error = 'Моля въведете тема.';
    } else {
        $upd = $dbh->prepare("
            UPDATE examinations
            SET topic = :topic
            WHERE id = :id
        ");
        $upd->execute([
            ':topic' => $newTopic,
            ':id'    => $examId,
        ]);
        $success = 'Темата беше актуализирана.';
        // Презареждаме стойността
        $exam['topic'] = $newTopic;
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Редакция на тема</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__.'/sidebar.php'; ?>
    <main class="dash-content">

      <!-- Back -->
      <a href="report_details.php?id=<?= $exam['student_id'] ?>" class="rd-back-link">
        &larr; Назад към отчета
      </a>

      <div class="rd-report-panel">
        <div class="rd-section">
          <h3>Редактиране на тема за дата <?= htmlspecialchars($exam['exam_time']) ?></h3>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="rd-section">
          <form method="POST" class="rd-add-topic-form">
            <input type="text"
                   name="topic"
                   value="<?= htmlspecialchars($exam['topic']) ?>"
                   required>
            <button type="submit">Запази промените</button>
          </form>
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