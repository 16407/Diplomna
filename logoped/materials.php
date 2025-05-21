<?php
session_start();
include('../includes/dbconnection.php');
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['logoped','director'])) {
    header('Location: ../index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_material'])) {
    $cat   = $_POST['category'];
    $title = trim($_POST['title']);
    if ($title && in_array($cat,['articulation','placement','differentiation'])
      && $_FILES['doc']['error']===UPLOAD_ERR_OK) {
        $uploaddir = __DIR__ . '/../uploads/materials/';
        if (!is_dir($uploaddir)) mkdir($uploaddir,0755,true);
        $fn = uniqid().'-'.basename($_FILES['doc']['name']);
        move_uploaded_file($_FILES['doc']['tmp_name'], $uploaddir.$fn);
        $stmt = $dbh->prepare("
          INSERT INTO materials (category,title,filepath)
          VALUES(:cat,:title,:fp)
        ");
        $stmt->execute([
          ':cat'=>$cat,':title'=>$title,':fp'=>$fn
        ]);
    }
    header('Location: materials.php');
    exit;
}

$labels = [
  'articulation'      => 'Артикулация',
  'placement'         => 'Постановка на звук',
  'differentiation'   => 'Диференциация'
];
$materials = [];
foreach (array_keys($labels) as $key) {
    $stmt = $dbh->prepare("SELECT * FROM materials WHERE category=:cat ORDER BY created_at DESC");
    $stmt->execute([':cat'=>$key]);
    $materials[$key] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Материали</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <button id="sidebarToggle" class="sidebar-toggle"><i class="fa fa-bars"></i></button>
  <div id="dash-container">
    <?php include __DIR__.'/sidebar.php'; ?>
    <main class="dash-content">
      <div class="mat-container">
        <h2>Материали</h2>
        <div class="mat-grid">
          <?php foreach($materials as $cat=>$items): ?>
            <div class="mat-col">
              <h3 class="mat-col__header"><?= $labels[$cat] ?></h3>
              <ul class="mat-list">
                <?php foreach($items as $m): ?>
                  <li class="mat-list__item">
                    <a class="mat-list__link"
                       href="../uploads/materials/<?= htmlspecialchars($m['filepath']) ?>"
                       target="_blank">
                      <?= htmlspecialchars($m['title']) ?>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
              <details>
                <summary class="mat-add">
                  <i class="fa fa-plus-circle"></i> Добави документ
                </summary>
                <form method="POST" enctype="multipart/form-data" class="mat-form">
                  <input type="hidden" name="add_material" value="1">
                  <input type="hidden" name="category"   value="<?= $cat ?>">
                  <input type="text"    name="title"      placeholder="Заглавие" required class="mat-form__input">
                  <input type="file"    name="doc"        accept=".pdf,.doc,.docx" required class="mat-form__file">
                  <button type="submit" class="mat-form__button">Качи</button>
                </form>
              </details>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
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