<?php
session_start();
include('../includes/dbconnection.php');

// Само за директор
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'director') {
    header('Location: ../index.php');
    exit;
}

$action = $_GET['action'] ?? 'overview';

// POST: изтриване логопед, преглед, ученик или клас
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['delete_logoped_id'])) {
        $dbh->prepare("DELETE FROM users WHERE id = :id AND role = 'logoped'")
            ->execute([':id'=>$_POST['delete_logoped_id']]);
        header('Location: dashboard.php?action=logopeds'); exit;
    }
    if (!empty($_POST['delete_exam_id'])) {
        $dbh->prepare("DELETE FROM examinations WHERE id = :id")
            ->execute([':id'=>$_POST['delete_exam_id']]);
        header('Location: dashboard.php?action=exams'); exit;
    }
    if (!empty($_POST['delete_student_id'])) {
        $dbh->prepare("DELETE FROM students WHERE id = :id")
            ->execute([':id'=>$_POST['delete_student_id']]);
        header('Location: dashboard.php?action=students'); exit;
    }
    if (!empty($_POST['delete_class_id'])) {
        $dbh->prepare("DELETE FROM classes WHERE id = :id")
            ->execute([':id'=>$_POST['delete_class_id']]);
        header('Location: dashboard.php?action=classes'); exit;
    }
}

// Данни за overview
if ($action === 'overview') {
    $logoped_count  = $dbh->query("SELECT COUNT(*) FROM users WHERE role='logoped'")->fetchColumn();
    $exam_count     = $dbh->query("SELECT COUNT(*) FROM examinations WHERE exam_date >= NOW()")->fetchColumn();
    $student_count  = $dbh->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $class_count    = $dbh->query("SELECT COUNT(*) FROM classes")->fetchColumn();
}

// Списък логопеди
if ($action === 'logopeds') {
    $logopeds = $dbh
      ->prepare("SELECT id, name, email, created_at FROM users WHERE role='logoped' ORDER BY created_at DESC");
    $logopeds->execute();
    $logopeds = $logopeds->fetchAll(PDO::FETCH_ASSOC);
}

// Списък прегледи
if ($action === 'exams') {
    $stmt = $dbh->prepare("
      SELECT e.id, s.name AS student_name, c.name AS class_name,
             e.exam_date, u.name AS logoped_name, e.notes
      FROM examinations e
      JOIN students s ON e.student_id = s.id
      JOIN classes  c ON s.class_id   = c.id
      JOIN users    u ON e.logoped_id  = u.id
      ORDER BY e.exam_date ASC
    ");
    $stmt->execute();
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Списък ученици
if ($action === 'students') {
    $stmt = $dbh->prepare("
      SELECT s.id, s.name, c.name AS class_name, s.created_at
      FROM students s
      JOIN classes c ON s.class_id = c.id
      ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Списък класове
if ($action === 'classes') {
    $stmt = $dbh->prepare("SELECT id, name FROM classes ORDER BY name");
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Director Dashboard</title>
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

    <?php if ($action === 'overview'): ?>
  <section class="dash-section">
    <h3>Добре дошли, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h3>
    <div class="cards">
      <!-- Управление на логопеди -->
      <div class="card">
        <h4>Логопеди</h4>
        <p class="card-number"><?= $logoped_count ?></p>
        <a href="manage_logopeds.php" class="btn-sm">Управление &rarr;</a>
      </div>
      <!-- Управление на ученици -->
      <div class="card">
        <h4>Ученици</h4>
        <p class="card-number"><?= $student_count ?></p>
        <a href="students.php" class="btn-sm">Управление &rarr;</a>
      </div>
      <!-- Материали -->
      <div class="card">
        <h4>Материали</h4>
        <a href="materials.php" class="btn-sm">Прегледай &rarr;</a>
      </div>
      <!-- Днешен график -->
      <div class="card">
        <h4>График</h4>
        <a href="schedule.php" class="btn-sm">Виж график &rarr;</a>
      </div>
      <!-- Седмичен график -->
      <div class="card">
        <h4>Седмичен график</h4>
        <p class="card-number">&mdash;</p>
        <a href="weekly_schedule.php" class="btn-sm">Виж седмица &rarr;</a>
      </div>
    </div>
  </section>

    <?php elseif ($action === 'logopeds'): ?>
      <section class="dash-section">
        <h3>Списък на логопеди</h3>
        <table class="table table-striped">
          <thead>
            <tr><th>Име</th><th>Имейл</th><th>Регистриран на</th><th>Действие</th></tr>
          </thead>
          <tbody>
          <?php foreach ($logopeds as $lp): ?>
            <tr>
              <td><?= htmlspecialchars($lp['name']) ?></td>
              <td><?= htmlspecialchars($lp['email']) ?></td>
              <td><?= date('d.m.Y', strtotime($lp['created_at'])) ?></td>
              <td>
                <form method="POST" class="inline-form">
                  <input type="hidden" name="delete_logoped_id" value="<?= $lp['id'] ?>">
                  <button type="submit" class="btn-delete-sm">Изтрий</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php elseif ($action === 'exams'): ?>
      <section class="dash-section">
        <h3>Списък на прегледи</h3>
        <table class="table table-striped">
          <thead>
            <tr><th>Дата и час</th><th>Ученик</th><th>Клас</th><th>Логопед</th><th>Бележки</th><th>Действие</th></tr>
          </thead>
          <tbody>
          <?php foreach ($exams as $e): ?>
            <tr>
              <td><?= date('d.m.Y H:i',strtotime($e['exam_date'])) ?></td>
              <td><?= htmlspecialchars($e['student_name']) ?></td>
              <td><?= htmlspecialchars($e['class_name']) ?></td>
              <td><?= htmlspecialchars($e['logoped_name']) ?></td>
              <td><?= nl2br(htmlspecialchars($e['notes'])) ?></td>
              <td>
                <form method="POST" class="inline-form">
                  <input type="hidden" name="delete_exam_id" value="<?= $e['id'] ?>">
                  <button type="submit" class="btn-delete-sm">Изтрий</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php elseif ($action === 'students'): ?>
      <section class="dash-section">
        <h3>Списък на ученици</h3>
        <table class="table table-striped">
          <thead>
            <tr><th>Име</th><th>Клас</th><th>Добавен на</th><th>Действие</th></tr>
          </thead>
          <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['name']) ?></td>
              <td><?= htmlspecialchars($s['class_name']) ?></td>
              <td><?= date('d.m.Y H:i',strtotime($s['created_at'])) ?></td>
              <td>
                <form method="POST" class="inline-form">
                  <input type="hidden" name="delete_student_id" value="<?= $s['id'] ?>">
                  <button type="submit" class="btn-delete-sm">Изтрий</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php elseif ($action === 'classes'): ?>
      <section class="dash-section">
        <h3>Списък на класове</h3>
        <table class="table table-striped">
          <thead>
            <tr><th>ID</th><th>Име на клас</th><th>Действие</th></tr>
          </thead>
          <tbody>
          <?php foreach ($classes as $c): ?>
            <tr>
              <td><?= $c['id'] ?></td>
              <td><?= htmlspecialchars($c['name']) ?></td>
              <td>
                <form method="POST" class="inline-form">
                  <input type="hidden" name="delete_class_id" value="<?= $c['id'] ?>">
                  <button type="submit" class="btn-delete-sm">Изтрий</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>

    <?php endif; ?>

    </main>
  </div>

  <script>
    const toggleBtn = document.getElementById('sidebarToggle'),
          sidebar   = document.querySelector('.dash-sidebar'),
          content   = document.querySelector('.dash-content');
    toggleBtn.addEventListener('click', ()=> sidebar.classList.toggle('open'));
    content.addEventListener('click', ()=> sidebar.classList.remove('open'));
  </script>
</body>
</html>