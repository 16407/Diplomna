<?php
$host = 'localhost';
$dbname = 'logoped'; // промени при нужда
$username = 'root'; // промени, ако си на хостинг
$password = '';     // празна парола по подразбиране в XAMPP

try {
    $dbh = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Грешка при връзката с базата данни: " . $e->getMessage());
}
?>