<?php
session_start();
include('includes/dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['lp_name']);
    $email    = trim($_POST['lp_email']);
    $password = $_POST['lp_password'];
    $confirm  = $_POST['lp_password_confirm'];
    $role     = 'logoped';  // роля по подразбиране

    // Валидация
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'Всички полета са задължителни.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Невалиден имейл.';
    } elseif ($password !== $confirm) {
        $error = 'Паролите не съвпадат.';
    } else {
        // Проверка за вече регистриран имейл
        $stmt = $dbh->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $error = 'Този имейл вече е регистриран.';
        } else {
            // Вмъкване на нов потребител
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $dbh->prepare("
                INSERT INTO users (name, email, password, role)
                VALUES (:name, :email, :pass, :role)
            ");
            $ins->bindParam(':name',  $name);
            $ins->bindParam(':email', $email);
            $ins->bindParam(':pass',  $hash);
            $ins->bindParam(':role',  $role);
            if ($ins->execute()) {
                header('Location: index.php?registered=1');
                exit;
            } else {
                $error = 'Грешка при регистрация. Опитайте отново.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Регистрация – Логопедична практика</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div id="lp-container">
    <!-- Ляв панел (branding) -->
    <div class="brand-panel">
      <div class="brand-content">
        <h1>Логопедична Практика</h1>
        <p>Създай своя профил</p>
      </div>
    </div>

    <!-- Десен панел (форма) -->
    <div class="form-panel">
      <form id="lp-registerForm" method="POST" action="">
        <h2>Регистрация</h2>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="lp-name">Име и фамилия</label>
          <input id="lp-name" name="lp_name" type="text" required>
        </div>

        <div class="form-group">
          <label for="lp-email">Имейл</label>
          <input id="lp-email" name="lp_email" type="email" required autocomplete="email">
        </div>

        <div class="form-group">
          <label for="lp-password">Парола</label>
          <input id="lp-password" name="lp_password" type="password" required autocomplete="new-password">
        </div>

        <div class="form-group">
          <label for="lp-password-confirm">Повтори паролата</label>
          <input id="lp-password-confirm" name="lp_password_confirm" type="password" required autocomplete="new-password">
        </div>

        <button id="lp-submit" type="submit">Регистрация</button>

        <p class="link-text">
          Имаш профил? <a href="index.php">Вход</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>