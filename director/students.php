<?php
session_start();
include('../includes/dbconnection.php');

// Само за директор
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'director') {
    header('Location: ../index.php');
    exit;
}

// Изтриване
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student_id'])) {
    $del = $dbh->prepare("DELETE FROM students WHERE id = :id");
    $del->execute([':id' => $_POST['delete_student_id']]);
    header('Location: students.php');
    exit;
}

// Зареждаме учениците
$stmt = $dbh->query("
    SELECT
      s.id,
      s.name AS student_name,
      c.name AS class_name,
      s.address,
      s.parent_name,
      s.parent_phone,
      DATE_FORMAT(s.created_at, '%d.%m.%Y') AS created
    FROM students s
    JOIN classes c ON s.class_id = c.id
    ORDER BY s.name
");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Управление на ученици – Директор</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
  <?php include __DIR__ . '/sidebar.php'; ?>

  <main class="dir-main-content">
    <section class="dir-section">
      <h1 class="dir-page-title"><i class="fa fa-child"></i> Ученици</h1>

      <?php if (empty($students)): ?>
        <p class="dir-empty">Няма регистрирани ученици.</p>
      <?php else: ?>
        <table class="dir-students-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Име</th>
              <th>Клас</th>
              <th>Адрес</th>
              <th>Родител</th>
              <th>Телефон</th>
              <th>Рег. дата</th>
              <th>Действие</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($students as $i => $s): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($s['student_name']) ?></td>
                <td><?= htmlspecialchars($s['class_name']) ?></td>
                <td><?= htmlspecialchars($s['address']) ?></td>
                <td><?= htmlspecialchars($s['parent_name']) ?></td>
                <td><?= htmlspecialchars($s['parent_phone']) ?></td>
                <td><?= $s['created'] ?></td>
                <td class="dir-actions-cell">
                  <a href="edit_student.php?id=<?= $s['id'] ?>" class="dir-btn dir-btn-edit">Редактирай</a>
                  <form method="POST" class="dir-inline-form" onsubmit="return confirm('Сигурни ли сте?');">
                    <input type="hidden" name="delete_student_id" value="<?= $s['id'] ?>">
                    <button type="submit" class="dir-btn dir-btn-delete">Изтрий</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>