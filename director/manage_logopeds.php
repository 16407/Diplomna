<?php
session_start();
include('../includes/dbconnection.php');

// Само за директор
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'director') {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Добавяне на логопед
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_logoped'])) {
    $name     = trim($_POST['logoped_name']);
    $email    = trim($_POST['logoped_email']);
    $pass     = $_POST['logoped_password'];
    $confirm  = $_POST['logoped_password_confirm'];

    // Валидация
    if ($name === '' || $email === '' || $pass === '' || $confirm === '') {
        $error = 'Всички полета са задължителни.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Невалиден имейл.';
    } elseif ($pass !== $confirm) {
        $error = 'Паролите не съвпадат.';
    } else {
        // Проверка за съществуващ имейл
        $chk = $dbh->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $chk->execute([':email' => $email]);
        if ($chk->fetch()) {
            $error = 'Този имейл вече е регистриран.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins  = $dbh->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (:name, :email, :pass, 'logoped')
            ");
            $ins->execute([
                ':name'  => $name,
                ':email' => $email,
                ':pass'  => $hash
            ]);
            $success = 'Логопедът беше добавен успешно.';
        }
    }
}

// Изтриване на логопед
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logoped_id'])) {
    $del = $dbh->prepare("DELETE FROM users WHERE id = :id AND role = 'logoped'");
    $del->execute([':id' => $_POST['delete_logoped_id']]);
    header('Location: manage_logopeds.php');
    exit;
}

// Зареждаме всички логопеди
$stmt    = $dbh->query("
    SELECT id, name, email, created_at
    FROM users
    WHERE role = 'logoped'
    ORDER BY created_at DESC
");
$logopeds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Управление на логопеди</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
  <!-- Toggle бутон -->
  <button id="sidebarToggle" class="sidebar-toggle">
    <i class="fa fa-bars"></i>
  </button>
  <div id="dash-container">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="dash-content">
      <section class="dash-section">
        <h3><i class="fa fa-user"></i> Логопеди</h3>

        <!-- Изведи съобщения -->
        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Форма за добавяне -->
        <form class="dash-form dash-form-inline" method="POST">
          <input type="hidden" name="add_logoped" value="1">
          <div class="form-group">
            <label for="logoped-name">Име</label>
            <input id="logoped-name" name="logoped_name" type="text" required>
          </div>
          <div class="form-group">
            <label for="logoped-email">Имейл</label>
            <input id="logoped-email" name="logoped_email" type="email" required>
          </div>
          <div class="form-group">
            <label for="logoped-pass">Парола</label>
            <input id="logoped-pass" name="logoped_password" type="password" required>
          </div>
          <div class="form-group">
            <label for="logoped-pass-conf">Повтори паролата</label>
            <input id="logoped-pass-conf" name="logoped_password_confirm" type="password" required>
          </div>
          <button type="submit" class="btn-sm">Добави логопед</button>
        </form>

        <!-- Списък с логопеди -->
        <?php if (empty($logopeds)): ?>
          <p>Все още няма добавени логопеди.</p>
        <?php else: ?>
          <table class="table table-striped" >
            <thead>
              <tr>
                <th>Име</th>
                <th>Имейл</th>
                <th>Регистриран на</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logopeds as $lp): ?>
                <tr>
                  <td><?= htmlspecialchars($lp['name']) ?></td>
                  <td><?= htmlspecialchars($lp['email']) ?></td>
                  <td><?= date('d.m.Y H:i', strtotime($lp['created_at'])) ?></td>
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
        <?php endif; ?>
      </section>
    </main>
  </div>

  <script>
    const toggleBtn = document.getElementById('sidebarToggle'),
          sidebar   = document.querySelector('.dash-sidebar'),
          content   = document.querySelector('.dash-content');
    toggleBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    content.addEventListener('click', () => sidebar.classList.remove('open'));
  </script>
</body>
</html>