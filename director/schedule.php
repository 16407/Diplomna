<?php
session_start();
include('../includes/dbconnection.php');

// Само за директор
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'director') {
    header('Location: ../index.php');
    exit;
}

// 1) дефинираме учебните часове
$slots = [
  1 => ['08:00', '08:40'],
  2 => ['08:50', '09:30'],
  3 => ['09:50', '10:30'],
  4 => ['10:40', '11:20'],
  5 => ['11:30', '12:10'],
  6 => ['12:20', '13:00'],
];

// 2) дата от GET или днешна
$selected = $_GET['date'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected)) {
    $selected = date('Y-m-d');
}
$prev  = date('Y-m-d', strtotime("$selected -1 day"));
$next  = date('Y-m-d', strtotime("$selected +1 day"));
$today = date('Y-m-d');

// 3) взимаме всички прегледи за тази дата, за всички логопеди
$stmt = $dbh->prepare("
    SELECT
      e.exam_date,
      e.topic,
      s.name AS student_name,
      u.name AS logoped_name
    FROM examinations e
    JOIN students s   ON e.student_id = s.id
    JOIN users    u   ON e.logoped_id  = u.id
    WHERE DATE(e.exam_date) = :sel
    ORDER BY e.exam_date ASC
");
$stmt->execute([':sel' => $selected]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) разпределяме по слотове
$schedule = [];
foreach ($slots as $num => list($start, $end)) {
    $schedule[$num] = [];
    foreach ($exams as $e) {
        $t = date('H:i', strtotime($e['exam_date']));
        if ($t >= $start && $t <= $end) {
            $schedule[$num][] = $e;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>График за <?= date('d.m.Y', strtotime($selected)) ?> – Директор</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <style>

  </style>
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
      <section class="sch-section">
        <div id="dir-sch-nav">
          <!-- Предишен ден -->
          <form method="GET" style="display:inline;">
            <input type="hidden" name="date" value="<?= $prev ?>">
            <button type="submit">&larr; <?= date('d.m', strtotime($prev)) ?></button>
          </form>
          <!-- Дата селектор -->
          <input type="date" value="<?= htmlspecialchars($selected) ?>"
                 onchange="window.location='?date='+this.value">
          <!-- Ако не е днес, бутон за днес -->
          <?php if ($selected !== $today): ?>
            <form method="GET" style="display:inline;">
              <input type="hidden" name="date" value="<?= $today ?>">
              <button type="submit">Днес</button>
            </form>
          <?php endif; ?>
          <!-- Следващ ден -->
          <form method="GET" style="display:inline;">
            <input type="hidden" name="date" value="<?= $next ?>">
            <button type="submit"><?= date('d.m', strtotime($next)) ?> &rarr;</button>
          </form>
        </div>

        <h2 style="text-align:center; color:white; margin-bottom:20px;">
          График за <?= date('d.m.Y', strtotime($selected)) ?>
        </h2>

        <table id="dir-sch-table">
          <thead>
            <tr>
              <th>Час</th>
              <th>Прегледи</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($slots as $num => list($start, $end)): ?>
            <tr>
              <td class="dir-timeslot">
                <?= $num ?>. <?= $start ?>–<?= $end ?>
              </td>
              <td>
                <?php if (!empty($schedule[$num])): ?>
                  <ul class="dir-list">
  <?php foreach ($schedule[$num] as $e): ?>
    <li class="dir-list-item">
      <span class="student"><?= htmlspecialchars($e['student_name']) ?></span>
      <span class="time">(<?= date('H:i', strtotime($e['exam_date'])) ?>)</span>
      <span class="topic">– <?= htmlspecialchars($e['topic'] ?: '(без тема)') ?></span>
      <small class="lp">логопед: <?= htmlspecialchars($e['logoped_name']) ?></small>
    </li>
  <?php endforeach; ?>
</ul>
                  </ul>
                <?php else: ?>
                  <span class="dir-empty">– свободно –</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <script>
    document.getElementById('sidebarToggle').addEventListener('click', () =>
      document.querySelector('.dash-sidebar').classList.toggle('open')
    );
    document.querySelector('.dash-content').addEventListener('click', () =>
      document.querySelector('.dash-sidebar').classList.remove('open')
    );
  </script>
</body>
</html>