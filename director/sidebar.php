<?php
// director/sidebar.php
?>
<aside class="dash-sidebar">
  <div class="sidebar-brand">Директор</div>
  <nav>
    <ul class="sidebar-menu">
      <li>
        <a href="dashboard.php">
          <i class="fa fa-tachometer"></i> Табло
        </a>
      </li>
      <li>
        <a href="manage_logopeds.php">
          <i class="fa fa-user"></i> Логопеди
        </a>
      </li>
      <li>
        <a href="materials.php">
          <i class="fa fa-file"></i> Материали
        </a>
      </li>
      <li>
        <a href="schedule.php">
          <i class="fa fa-calendar"></i> График
        </a>
      </li>
      <li>
        <a href="students.php">
          <i class="fa fa-child"></i> Ученици
        </a>
      </li>
      <li>
        <a href="weekly_schedule.php">
        <i class="fa fa-calendar-o"></i> Седмичен график
        </a>

      </li>
    </ul>
  </nav>
  <a href="../logout.php" class="logout-link">
    <i class="fa fa-sign-out"></i> Изход
  </a>
</aside>