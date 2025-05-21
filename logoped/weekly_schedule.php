<?php
// logoped/weekly_schedule.php
session_start();
include('../includes/dbconnection.php');
if (!isset($_SESSION['user_role']) || $_SESSION['user_role']!=='logoped') {
    header('Location: ../index.php');
    exit;
}

// 1) дефинираме часовите слотове
$slots = [
  1 => ['08:00','08:40'],
  2 => ['08:50','09:30'],
  3 => ['09:50','10:30'],
  4 => ['10:40','11:20'],
  5 => ['11:30','12:10'],
  6 => ['12:20','13:00'],
];

// 2) изчисляваме началото на седмицата (понеделник)
if (!empty($_GET['start']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$_GET['start'])) {
    $monday = $_GET['start'];
} else {
    // monday this week
    $monday = date('Y-m-d', strtotime('monday this week'));
}

// 3) предходна/следваща седмица
$prevWeek = date('Y-m-d', strtotime("$monday -7 days"));
$nextWeek = date('Y-m-d', strtotime("$monday +7 days"));

// 4) изграждаме масив с дните от понеделник до неделя
$days = [];
for ($i = 0; $i < 7; $i++) {
    $days[$i] = date('Y-m-d', strtotime("$monday +{$i} days"));
}

// 5) взимаме всички прегледи за седмицата
$stmt = $dbh->prepare("
  SELECT
    e.exam_date,
    e.topic,
    e.student_id,         -- <--- добавяме го
    s.name AS student_name
  FROM examinations e
  JOIN students s ON s.id = e.student_id
  WHERE e.logoped_id = :lid
    AND DATE(e.exam_date) BETWEEN :start AND :end
");
$stmt->execute([
    ':lid'   => $_SESSION['user_id'],
    ':start' => $days[0],
    ':end'   => $days[6],
]);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 6) групираме по ден и по слот
$weekSchedule = [];
foreach ($days as $di => $day) {
    foreach ($slots as $si => $_) {
        $weekSchedule[$di][$si] = [];
    }
}
foreach ($all as $e) {
    $dayIndex = array_search(date('Y-m-d', strtotime($e['exam_date'])), $days);
    if ($dayIndex===false) continue;
    $time = date('H:i', strtotime($e['exam_date']));
    foreach ($slots as $si => list($start,$end)) {
        if ($time >= $start && $time <= $end) {
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
  <title>Седмичен график (<?= date('d.m.Y',strtotime($days[0])) ?> – <?= date('d.m.Y',strtotime($days[6])) ?>)</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__.'/sidebar.php'; ?>
    <main class="dash-content">
      <section class="ws-nav">
        <form method="GET" style="display:inline;">
          <input type="hidden" name="start" value="<?= $prevWeek ?>">
          <button type="submit">&larr; <?= date('d.m.Y',strtotime($prevWeek)) ?></button>
        </form>
        <span class="ws-week-label">
          <?= date('d.m.Y',strtotime($days[0])) ?> – <?= date('d.m.Y',strtotime($days[6])) ?>
        </span>
        <form method="GET" style="display:inline;">
          <input type="hidden" name="start" value="<?= $nextWeek ?>">
          <button type="submit"><?= date('d.m.Y',strtotime($nextWeek)) ?> &rarr;</button>
        </form>
      </section>

      <table class="ws-table">
        <thead>
          <tr>
            <th>Час</th>
            <?php foreach ($days as $day): ?>
              <th><?= mb_substr(['Понеделник','Вторник','Сряда','Четвъртък','Петък','Събота','Неделя'][array_search($day,$days)],0,3) ?>
                <br><small><?= date('d.m',strtotime($day)) ?></small>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($slots as $si => list($start,$end)): ?>
            <tr>
              <td class="ws-timeslot"><?= $start ?>–<?= $end ?></td>
              <?php foreach ($days as $di => $_): ?>
                <td class="ws-cell">
                  <?php if ($weekSchedule[$di][$si]): ?>
                    <ul class="ws-list">
                    <?php foreach ($weekSchedule[$di][$si] as $e): ?>
  <li class="ws-list__item">
    <!-- явно относителен път: -->
    <a href="./report_details.php?id=<?= $e['student_id'] ?>"

       class="ws-list__link">
      <strong><?= htmlspecialchars($e['student_name']) ?></strong>
      (<?= date('H:i', strtotime($e['exam_date'])) ?>)
      – <?= htmlspecialchars($e['topic'] ?: '(без тема)') ?>
    </a>
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
    document.getElementById('sidebarToggle').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.remove('open');
    });
  </script>
</body>
</html>