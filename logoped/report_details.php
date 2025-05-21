<?php
session_start();
include('../includes/dbconnection.php');
if ($_SESSION['user_role']!=='logoped') {
  header('Location: report.php');
  exit;
}
if (empty($_GET['id'])) {
  header('Location: report.php');
  exit;
}
$id = (int)$_GET['id'];

$today       = date('Y-m-d');
$displayDate = date('d.m.Y');

// --- Обработка на изтриване на тема ---
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_topic_id'])) {
    $del = $dbh->prepare("DELETE FROM examinations WHERE id = :eid AND logoped_id = :lid");
    $del->execute([
      ':eid' => $_POST['delete_topic_id'],
      ':lid' => $_SESSION['user_id']
    ]);
    header("Location: report_details.php?id={$id}");
    exit;
}

// --- Добавяне на нова тема ---
$success = '';
$error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_topic'])) {
  $topic = trim($_POST['topic']);
  if ($topic === '') {
    $error = 'Моля въведете тема.';
  } else {
    $ins = $dbh->prepare("
      INSERT INTO examinations
        (logoped_id, student_id, topic, exam_date, notes)
      VALUES
        (:lid, :sid, :topic, NOW(), '')
    ");
    $ins->execute([
      ':lid'   => $_SESSION['user_id'],
      ':sid'   => $id,
      ':topic' => $topic,
    ]);
    $success = 'Новата тема беше добавена.';
  }
}

// --- Данни за ученик ---
$stmt = $dbh->prepare("
  SELECT s.name, s.profile_image, c.name AS class_name
  FROM students s
  JOIN classes c ON s.class_id = c.id
  WHERE s.id = :id
");
$stmt->execute([':id'=>$id]);
$kid = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$kid) {
  header('Location: report.php');
  exit;
}

// --- Днешни теми ---
$stmt = $dbh->prepare("
  SELECT id, topic, DATE_FORMAT(exam_date, '%H:%i') AS time
  FROM examinations
  WHERE student_id=:sid
    AND logoped_id=:lid
    AND DATE(exam_date)=:today
  ORDER BY exam_date ASC
");
$stmt->execute([
  ':sid'   => $id,
  ':lid'   => $_SESSION['user_id'],
  ':today' => $today
]);
$todayTopics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Предишни теми ---
$stmt = $dbh->prepare("
  SELECT id, topic, exam_date
  FROM examinations
  WHERE student_id=:sid
    AND logoped_id=:lid
    AND DATE(exam_date) <> :today
  ORDER BY exam_date DESC
");
$stmt->execute([
  ':sid'   => $id,
  ':lid'   => $_SESSION['user_id'],
  ':today' => $today
]);
$pastRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Групиране по дата ---
$pastTopics = [];
foreach ($pastRaw as $row) {
  $d = date('d.m.Y', strtotime($row['exam_date']));
  $pastTopics[$d][] = [
    'id'    => $row['id'],
    'time'  => date('H:i', strtotime($row['exam_date'])),
    'topic' => $row['topic']
  ];
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Отчет – <?= htmlspecialchars($kid['name']) ?></title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__.'/sidebar.php'; ?>
    <main class="dash-content">
      <!-- Back -->
      <a href="report.php" class="rd-back-link">&larr; Назад към отчета</a>

      <div class="rd-report-panel">
        <!-- Report Date -->
        <div class="rd-section">
          <h3>Отчет за дата: <?= htmlspecialchars($displayDate) ?></h3>
        </div>

        <!-- Kid Info -->
        <div class="rd-kid-header">
          <img src="../images/<?= htmlspecialchars($kid['profile_image']?: 'default-avatar.png') ?>" alt="">
          <div class="rd-kid-info">
            <h2><?= htmlspecialchars($kid['name']) ?></h2>
            <p><strong>Клас:</strong> <?= htmlspecialchars($kid['class_name']) ?></p>
          </div>
        </div>

        <!-- Today's Topics -->
        <div class="rd-section">
          <h3>Теми за днес</h3>
          <?php if (empty($todayTopics)): ?>
            <p><em>Все още няма теми за днес.</em></p>
          <?php else: ?>
            <ul class="rd-topic-list">
              <?php foreach ($todayTopics as $t): ?>
                <li>
                  <div class="rd-topic-text">
                    <strong><?= htmlspecialchars($t['time']) ?></strong> –
                    <?= htmlspecialchars($t['topic']) ?>
                  </div>
                  <div class="rd-topic-actions">
                    <a href="edit_topic.php?id=<?= $t['id'] ?>"><i class="fa fa-pencil"></i></a>
                    <form method="POST" onsubmit="return confirm('Изтриване на тема?')">
                      <input type="hidden" name="delete_topic_id" value="<?= $t['id'] ?>">
                      <button type="submit"><i class="fa fa-trash"></i></button>
                    </form>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <!-- Past Topics -->
        <?php if ($pastTopics): ?>
          <div class="rd-section">
            <h3>Предишни теми</h3>
            <?php foreach ($pastTopics as $date => $topics): ?>
              <div class="rd-past-date" style="margin:16px 0;font-weight:600;"><?= htmlspecialchars($date) ?></div>
              <ul class="rd-topic-list">
                <?php foreach ($topics as $t): ?>
                  <li>
                    <div class="rd-topic-text">
                      <strong><?= htmlspecialchars($t['time']) ?></strong> –
                      <?= htmlspecialchars($t['topic']) ?>
                    </div>
                    <div class="rd-topic-actions">
                      <a href="edit_topic.php?id=<?= $t['id'] ?>"><i class="fa fa-pencil"></i></a>
                      <form method="POST" onsubmit="return confirm('Изтриване на тема?')">
                        <input type="hidden" name="delete_topic_id" value="<?= $t['id'] ?>">
                        <button type="submit"><i class="fa fa-trash"></i></button>
                      </form>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <!-- Add New Topic -->
        <div class="rd-section">
          <h3>Добави нова тема</h3>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>
          <form method="POST" class="rd-add-topic-form">
            <input type="hidden" name="add_topic" value="1">
            <input type="text" name="topic" placeholder="Въведете тема" required>
            <button type="submit">Добави</button>
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