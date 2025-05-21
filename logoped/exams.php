<?php
session_start();
include('../includes/dbconnection.php');

// Само за логопед
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'logoped') {
    header('Location: ../index.php');
    exit;
}

$logoped_id = $_SESSION['user_id'];

// Изтриване на преглед
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_exam_id'])) {
    $del = $dbh->prepare("
      DELETE FROM examinations
      WHERE id = :id AND logoped_id = :lid
    ");
    $del->execute([
      ':id'  => $_POST['delete_exam_id'],
      ':lid' => $logoped_id
    ]);
    header('Location: exams.php');
    exit;
}

// Зареждаме всички прегледи на този логопед
$stmt = $dbh->prepare("
  SELECT
    e.id,
    s.name       AS student_name,
    c.name       AS class_name,
    e.exam_date,
    e.topic      AS topic
  FROM examinations e
  JOIN students  s ON e.student_id = s.id
  JOIN classes   c ON s.class_id   = c.id
  WHERE e.logoped_id = :lid
  ORDER BY e.exam_date DESC
");
$stmt->execute([':lid' => $logoped_id]);
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Прегледи – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <style>
    .table-controls {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 12px;
    }
    #searchInput {
      padding: 6px 10px;
      border: 1px solid #ccc;
      border-radius: 4px;
      width: 200px;
    }
    th.sortable {
      cursor: pointer;
      position: relative;
    }
    th.sortable:after {
      content: '⇅';
      font-size: 0.8em;
      position: absolute;
      right: 8px;
      color: #888;
    }
    th.sortable.asc:after {
      content: '↑';
    }
    th.sortable.desc:after {
      content: '↓';
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
        <h3><i class="fa fa-list-alt"></i> Моите прегледи</h3>

        <?php if (empty($exams)): ?>
          <p><em>Все още нямате записани прегледи.</em></p>
        <?php else: ?>
          <!-- Търсачка -->
          <div class="table-controls">
            <input type="text" id="searchInput" placeholder="Търси ученик/клас/тема…">
          </div>

          <div class="exams-container">
  <table id="examsTable" class="table table-striped">

            <thead>
              <tr>
                <th>#</th>
                <th class="sortable">Дата и час</th>
                <th class="sortable">Ученик</th>
                <th class="sortable">Клас</th>
                <th class="sortable">Тема</th>
                <th>Действие</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($exams as $idx => $e): ?>
                <tr>
                  <td><?= $idx + 1 ?></td>
                  <td><?= date('d.m.Y H:i', strtotime($e['exam_date'])) ?></td>
                  <td><?= htmlspecialchars($e['student_name']) ?></td>
                  <td><?= htmlspecialchars($e['class_name']) ?></td>
                  <td><?= nl2br(htmlspecialchars($e['topic'])) ?></td>
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
          </div>

        <?php endif; ?>
      </section>
    </main>
  </div>

  <script>
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', () => {
      document.querySelector('.dash-sidebar').classList.toggle('open');
    });
    document.querySelector('.dash-content').addEventListener('click', () => {
      document.querySelector('.dash-sidebar').classList.remove('open');
    });

    // Филтриране (търсачка)
    const searchInput = document.getElementById('searchInput');
    const table       = document.getElementById('examsTable');
    const rows        = table.tBodies[0].rows;
    searchInput.addEventListener('keyup', () => {
      const term = searchInput.value.trim().toLowerCase();
      for (let row of rows) {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
      }
    });

    // Сортиране на колони
    const getCellValue = (row, idx) =>
      row.cells[idx].innerText || row.cells[idx].textContent;

    const comparer = (idx, asc) => (a, b) => {
      const v1 = getCellValue(asc ? a : b, idx);
      const v2 = getCellValue(asc ? b : a, idx);
      return v1.localeCompare(v2, 'bg', {numeric: true});
    };

    document.querySelectorAll('th.sortable').forEach((th, idx) => {
      let asc = true;
      th.addEventListener('click', () => {
        const tableBody = table.tBodies[0];
        Array.from(rows)
          .sort(comparer(idx, asc))
          .forEach(row => tableBody.appendChild(row));
        th.classList.toggle('asc', asc);
        th.classList.toggle('desc', !asc);
        asc = !asc;
      });
    });
  </script>
</body>
</html>