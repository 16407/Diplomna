<?php
// logoped/weekly_schedule.php  (или director/weekly_schedule.php ако предпочитате отделно)
session_start();
include('../includes/dbconnection.php');

// Разрешаваме достъп само на логопед или директор
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['logoped', 'director'])) {
  header('Location: ../index.php');
  exit;
}

// 1) Дефинираме часовите слотове
$slots = [
  1 => ['08:00', '08:40'],
  2 => ['08:50', '09:30'],
  3 => ['09:50', '10:30'],
  4 => ['10:40', '11:20'],
  5 => ['11:30', '12:10'],
  6 => ['12:20', '13:00'],
];

// 2) Изчисляваме понеделника на текущата или избраната седмица
if (!empty($_GET['start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['start'])) {
  $monday = $_GET['start'];
} else {
  $monday = date('Y-m-d', strtotime('monday this week'));
}

// 3) Предишна и следваща седмица
$prevWeek = date('Y-m-d', strtotime("$monday -7 days"));
$nextWeek = date('Y-m-d', strtotime("$monday +7 days"));

// 4) Масив с дните от понеделник до неделя
$days = [];
for ($i = 0; $i < 7; $i++) {
  $days[$i] = date('Y-m-d', strtotime("$monday +{$i} days"));
}

// 5) Взимаме всички прегледи за тази седмица и името на логопеда
$stmt = $dbh->prepare("
  SELECT
    e.exam_date,
    e.topic,
    e.student_id,
    s.name   AS student_name,
    u.name   AS logoped_name
  FROM examinations e
  JOIN students s ON s.id = e.student_id
  JOIN users    u ON u.id = e.logoped_id
  WHERE DATE(e.exam_date) BETWEEN :start AND :end
    AND (:role = 'director' OR e.logoped_id = :lid)
  ORDER BY e.exam_date
");
$stmt->execute([
  ':start' => $days[0],
  ':end' => $days[6],
  ':role' => $_SESSION['user_role'],
  ':lid' => $_SESSION['user_id'],
]);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6) Групираме по ден и по слот
$weekSchedule = [];
foreach ($days as $di => $day) {
  foreach ($slots as $si => $_) {
    $weekSchedule[$di][$si] = [];
  }
}
foreach ($all as $e) {
  $dayIndex = array_search(date('Y-m-d', strtotime($e['exam_date'])), $days);
  if ($dayIndex === false)
    continue;
  $t = date('H:i', strtotime($e['exam_date']));
  foreach ($slots as $si => list($start, $end)) {
    if ($t >= $start && $t <= $end) {
      $weekSchedule[$dayIndex][$si][] = $e;
      break;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="bg">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Седмичен график (<?= date('d.m.Y', strtotime($days[0])) ?>–<?= date('d.m.Y', strtotime($days[6])) ?>)</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <style>
    /* Минимална стилизация за таблицата */
    .ws-nav {
      text-align: center;
      margin: 20px 0;
    }

    .ws-nav button {
      margin: 0 10px;
      padding: 6px 12px;
      border: none;
      background: #6f42c1;
      color: #fff;
      border-radius: 4px;
    }

    .ws-week-label {
      font-weight: 600;
      color: #333;
    }

    .ws-table {
      width: 100%;
      border-collapse: collapse;
      margin: 0 20px;
    }

    .ws-table th,
    .ws-table td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }

    .ws-table thead th {
      background: #6f42c1;
      color: #fff;
    }

    .ws-timeslot {
      font-weight: 600;
      background: #7d35dc;
    }

    .ws-free {
      color: #999;
      font-style: italic;
    }

    .ws-list {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .ws-list__item+.ws-list__item {
      margin-top: 6px;
    }

    .ws-list__link {
      display: block;
      padding: 6px;
      background: #e9ecef;
      border-radius: 4px;
      text-decoration: none;
      color: #212529;
      transition: background .2s;
    }

    .ws-list__link:hover {
      background: #dee2e6;
    }

    .ws-list__link small {
      display: block;
      font-size: 0.85em;
      color: #555;
    }
  </style>
</head>

<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
      <section class="ws-nav">
        <form method="GET" style="display:inline">
          <input type="hidden" name="start" value="<?= $prevWeek ?>">
          <button>&larr; Пред. седмица</button>
        </form>
        <span class="ws-week-label">
          <?= date('d.m.Y', strtotime($days[0])) ?> – <?= date('d.m.Y', strtotime($days[6])) ?>
        </span>
        <form method="GET" style="display:inline">
          <input type="hidden" name="start" value="<?= $nextWeek ?>">
          <button>След. седмица &rarr;</button>
        </form>
      </section>

      <table class="ws-table">
        <thead>
          <tr>
            <th>Час</th>
            <?php foreach ($days as $day): ?>
              <th>
                <?= mb_substr(['Пон', 'Вто', 'Сря', 'Чет', 'Пет', 'Съб', 'Нед'][array_search($day, $days)], 0, 3) ?><br>
                <small><?= date('d.m', strtotime($day)) ?></small>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($slots as $si => list($start, $end)): ?>
            <tr>
              <td class="ws-timeslot"><?= $start ?>–<?= $end ?></td>
              <?php foreach ($days as $di => $_): ?>
                <td>
                  <?php if ($weekSchedule[$di][$si]): ?>
                    <ul class="ws-list">
                      <?php foreach ($weekSchedule[$di][$si] as $e): ?>
                        <li class="ws-list__item">
                          <div class="ws-item-info">
                            <strong><?= htmlspecialchars($e['student_name']) ?></strong>
                            (<?= date('H:i', strtotime($e['exam_date'])) ?>)
                            – <?= htmlspecialchars($e['topic'] ?: '(без тема)') ?>
                            <small class="ws-item-logoped">логопед: <?= htmlspecialchars($e['logoped_name']) ?></small>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <span class="ws-free">– свободно –</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
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