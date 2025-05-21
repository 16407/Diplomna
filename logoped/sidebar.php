<?php
// logoped/sidebar.php
?>
<aside class="dash-sidebar">
  <!-- Лого -->
  <div class="sidebar-logo-container">
    <img src="../images/logo.png" alt="Логопедична практика" class="sidebar-logo">
  </div>

  <nav>
    <ul class="sidebar-menu">
      <li>
        <a href="dashboard.php">
          <i class="fa fa-home"></i> Начало
        </a>
      </li>
      <li>
        <a href="kids.php">
          <i class="fa fa-child"></i> Ученици
        </a>
      </li>
      <li>
        <a href="add_kid.php">
          <i class="fa fa-plus-circle"></i> Нов ученик
        </a>
      </li>
      <li>
        <a href="exams.php">
          <i class="fa fa-list-alt"></i> Прегледи
        </a>
      </li>
      <li>
        <a href="report.php">
          <i class="fa fa-book"></i> Отчет
        </a>
      </li>
      <li>
        <a href="schedule.php">
          <i class="fa fa-calendar"></i> График
        </a>
      </li>
      <!-- <li>
        <a href="classes_students.php">
          <i class="fa fa-list"></i> Класове
        </a>
      </li> -->
      <li>
        <a href="weekly_schedule.php">
          <i class="fa fa-calendar-o"></i> Седмичен график
        </a>
      </li>
      <li>
        <a href="materials.php">
          <i class="fa fa-file"></i> Материали
        </a>
      </li>
    </ul>
  </nav>

  <a href="../logout.php" class="logout-link">
    <i class="fa fa-sign-out"></i> Изход
  </a>
</aside>