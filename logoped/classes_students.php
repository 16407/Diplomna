<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'logoped') {
    header('Location: ../index.php');
    exit;
}

$logoped_id = $_SESSION['user_id'];

// Вземаме за всеки клас и ученик списъка от теми на този логопед
$stmt = $dbh->prepare("
  SELECT
    c.name   AS class_name,
    s.id     AS student_id,
    s.name   AS student_name,
    e.exam_date,
    e.topic
  FROM classes c
  JOIN students s ON s.class_id = c.id
  LEFT JOIN examinations e
    ON e.student_id = s.id
    AND e.logoped_id = :lid
  ORDER BY c.name, s.name, e.exam_date DESC
");
$stmt->execute([':lid' => $logoped_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Групираме: class_name → student_id → student_name → [ topics... ]
$grouped = [];
foreach ($rows as $r) {
    $class = $r['class_name'];
    $sid   = $r['student_id'];
    $stud  = $r['student_name'];
    if (!isset($grouped[$class][$sid])) {
        $grouped[$class][$sid] = [
            'name'   => $stud,
            'topics' => []
        ];
    }
    if ($r['exam_date']) {
        $grouped[$class][$sid]['topics'][] = [
            'date'  => $r['exam_date'],
            'topic' => $r['topic']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Класове и Прегледи – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <style>
    .class-block {
      margin-bottom: 40px;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .class-block h4 {
      color: var(--purple-dark);
      margin-bottom: 16px;
    }
    .student-block {
      margin-bottom: 24px;
      padding-left: 16px;
      border-left: 4px solid var(--purple-light);
    }
    .student-block h5 {
      font-size: 18px;
      margin-bottom: 8px;
      color: var(--text-dark);
    }
    .student-block p {
      font-style: italic;
      color: #555;
      margin-bottom: 12px;
    }
    .exam-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    .exam-table th,
    .exam-table td {
      border: 1px solid #e0e0e0;
      padding: 8px;
      text-align: left;
      font-size: 14px;
    }
    .exam-table thead th {
      background: var(--purple-dark);
      color: #fff;
      font-weight: 600;
    }
    .exam-table tbody tr:nth-child(odd) {
      background: #f9f9f9;
    }
    .exam-table td.notes {
      white-space: pre-wrap;
    }
  </style>
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <main class="dash-content">
      <section class="dash-section">
        <h3><i class="fa fa-list-alt"></i> Класове и Теми</h3>

        <?php if (empty($grouped)): ?>
          <p><em>Няма въведени класове или ученици.</em></p>
        <?php else: ?>
          <?php foreach ($grouped as $className => $students): ?>
            <div class="class-block">
              <h4>Клас: <?= htmlspecialchars($className) ?></h4>

              <?php foreach ($students as $sid => $data): ?>
                <div class="student-block">
                <h5>
  <a href="report_details.php?id=<?= $sid ?>" class="btn-student-link">
    <?= htmlspecialchars($data['name']) ?>
  </a>
</h5>

                  <?php if (empty($data['topics'])): ?>
                    <p>Няма записани теми.</p>
                  <?php else: ?>
                    <table class="exam-table">
                      <thead>
                        <tr>
                          <th>Дата и час</th>
                          <th>Тема</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($data['topics'] as $t): ?>
                          <tr>
                            <td><?= date('d.m.Y H:i', strtotime($t['date'])) ?></td>
                            <td><?= htmlspecialchars($t['topic']) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>

            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
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