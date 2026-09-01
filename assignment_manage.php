<?php

if (!isset($container)) {
    require_once __DIR__.'/../../gibbon.php';
}
require_once 'moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_add.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$gibbonCourseID = (int)($_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? 0);
$gibbonCourseClassID = (int)($_GET['gibbonCourseClassID'] ?? $_POST['gibbonCourseClassID'] ?? 0);

if ($gibbonCourseID <= 0) {
    $page->addError(__('The specified record cannot be found.'));
    return;
}

if ($gibbonCourseClassID <= 0) {
    $class = $pdo->selectOne(
        'SELECT gibbonCourseClassID FROM gibbonCourseClass WHERE gibbonCourseID = :gibbonCourseID ORDER BY nameShort LIMIT 1',
        ['gibbonCourseID' => $gibbonCourseID]
    );
    $gibbonCourseClassID = (int)$class;
}

$courseName = (string)$pdo->selectOne(
    'SELECT name FROM gibbonCourse WHERE gibbonCourseID = :gibbonCourseID',
    ['gibbonCourseID' => $gibbonCourseID]
);

echo '<div id="assignmentManageContent" class="w-full">';
echo '<div id="addAssignmentPanel">';
if ($gibbonCourseClassID > 0) {
    include 'assignment_add.php';
}
echo '</div>';
echo '<div id="assignmentsTable">';
include 'assignment_table.php';
echo '</div>';
echo '</div>';
?>
<script>
function toggleAddAssignmentPanel() {
  const panel = document.getElementById('addAssignmentPanel');
  if (!panel) return;
  panel.classList.toggle('visible');
  if (panel.classList.contains('visible')) {
    panel.scrollIntoView({ behavior: 'smooth' });
  }
}

if (!window.assignmentManageClickBound) {
  window.assignmentManageClickBound = true;
  document.addEventListener('click', function (e) {
    const table = e.target.closest('#assignmentsTable');
    if (!table) return;

    const btn = e.target.closest('a');
    if (!btn) return;
    if (btn.hasAttribute('hx-get') || btn.hasAttribute('hx-post')) return;

    const row = btn.closest('tr');
    if (!row) return;

    const isVisible = btn.classList.contains('button-visible');
    const isInvisible = btn.classList.contains('button-invisible');
    if (!isVisible && !isInvisible) return;

    e.preventDefault();
    const visibleBtns = row.querySelectorAll('a.button-visible');
    const invisibleBtns = row.querySelectorAll('a.button-invisible');
    visibleBtns.forEach(el => {
      el.classList.remove('button-visible');
      el.classList.add('button-invisible');
    });
    invisibleBtns.forEach(el => {
      el.classList.remove('button-invisible');
      el.classList.add('button-visible');
    });
  });
}
</script>
