<?php
session_start();
include('../includes/dbconnection.php');
if ($_SESSION['user_role']!=='logoped') {
  header('Location: ../index.php');
  exit;
}

// 1) дефинираме часовете
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

// 4) взимаме прегледите за избраната дата, включваме и student_id
$stmt = $dbh->prepare("
  SELECT
    e.exam_date,
    e.topic,
    e.student_id,
    s.name AS student_name
  FROM examinations e
  JOIN students   s ON e.student_id = s.id
  WHERE e.logoped_id = :lid
    AND DATE(e.exam_date) = :sel
");
$stmt->execute([
  ':lid' => $_SESSION['user_id'],
  ':sel' => $selected,
]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 5) наслагваме по слот
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
  <title>График за <?= date('d.m.Y', strtotime($selected)) ?> – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
      <section class="sch-section">
        <div class="sch-nav">
          <!-- Предишен ден -->
          <form method="GET" style="display:inline;">
            <input type="hidden" name="date" value="<?= $prev ?>">
            <button type="submit">&larr; <?= date('d.m', strtotime($prev)) ?></button>
          </form>

          <!-- Дата селектор -->
          <input
            type="date"
            value="<?= htmlspecialchars($selected) ?>"
            onchange="window.location='?date='+this.value"
          >

          <!-- Ако не е вече днес, показваме бутон към днес -->
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

        <h2 style="color:white; text-align:center; margin-bottom:20px;">
          График за <?= date('d.m.Y', strtotime($selected)) ?>
        </h2>

        <table class="sch-table">
  <thead>
    <tr>
      <th>Час</th>
      <th>Ученик и Тема</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($slots as $num => list($start,$end)): ?>
      <tr>
        <td class="sch-timeslot">
          <?= $num ?>. <?= $start ?> &ndash; <?= $end ?>
        </td>
        <td>
          <?php if (!empty($schedule[$num])): ?>
            <ul class="sch-list">
            <?php foreach($schedule[$num] as $e): ?>
                      <li>
                        <!-- Тук правим името линк -->
                        <a href="report_details.php?id=<?=$e['student_id']?>">
                          <strong><?=htmlspecialchars($e['student_name'])?></strong>
                        </a>
                        (<?=date('H:i',strtotime($e['exam_date']))?>)
                        – <?=htmlspecialchars($e['topic']?:'<em>(без тема)</em>')?>
                      </li>
                    <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <span class="sch-empty">– свободно –</span>
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
    document.getElementById('sidebarToggle').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click',()=>{
      document.querySelector('.dash-sidebar').classList.remove('open');
    });
  </script>
</body>
</html>