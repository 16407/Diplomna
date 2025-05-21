<?php
session_start();
include('includes/dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['lp_email']);
    $password = $_POST['lp_password'];

    $stmt = $dbh->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'director') {
            header('Location: director/dashboard.php');
        } else {
            header('Location: logoped/dashboard.php');
        }
        exit;
    } else {
        $error = 'Невалиден имейл или парола.';
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Вход – Логопедична практика</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div id="lp-container">
    <div class="brand-panel">
      <div class="brand-content">
        <h1>Логопедична Практика</h1>
        <p>Добре дошли! Моля, влезте във Вашия профил.</p>
      </div>
    </div>

    <div class="form-panel">
      <form id="lp-loginForm" method="POST" action="">
        <h2>Вход</h2>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
          <label for="lp-email">Имейл</label>
          <input id="lp-email" name="lp_email" type="email" required autocomplete="username">
        </div>

        <div class="form-group">
          <label for="lp-password">Парола</label>
          <input id="lp-password" name="lp_password" type="password" required autocomplete="current-password">
        </div>

        <button id="lp-submit" type="submit">Вход</button>

        <p class="link-text">
          Нямаш акаунт? <a href="register.php">Регистрация</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>