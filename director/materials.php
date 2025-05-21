<?php
// logoped/materials.php
session_start();
include('../includes/dbconnection.php');
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['logoped','director'])) {
    header('Location: ../index.php');
    exit;
}

// Етикети за категориите
$labels = [
  'articulation'    => 'Артикулация',
  'placement'       => 'Постановка на звук',
  'differentiation' => 'Диференциация',
];

// Зареждаме материалите по категории
$materials = [];
foreach (array_keys($labels) as $cat) {
    $stmt = $dbh->prepare("SELECT * FROM materials WHERE category = :cat ORDER BY created_at DESC");
    $stmt->execute([':cat' => $cat]);
    $materials[$cat] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Материали – Логопед</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
      <section class="materials-section">
        <h2 class="materials-title">Материали</h2>
        <div class="materials-grid">
          <?php foreach ($materials as $cat => $items): ?>
            <div class="materials-col">
              <h3 class="materials-col__header"><?= htmlspecialchars($labels[$cat]) ?></h3>
              <?php if (empty($items)): ?>
                <p><em>Няма налични документи.</em></p>
              <?php else: ?>
                <ul class="materials-list">
                  <?php foreach ($items as $m): ?>
                    <li class="materials-list__item">
                      <a href="../uploads/materials/<?= htmlspecialchars($m['filepath']) ?>"
                         class="materials-list__link" target="_blank">
                        <?= htmlspecialchars($m['title']) ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
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