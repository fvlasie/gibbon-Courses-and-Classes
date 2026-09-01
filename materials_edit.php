<?php

if (!isset($container)) {
    require_once __DIR__.'/../../gibbon.php';
}
require_once 'moduleFunctions.php';

$courseName = $_GET['courseName'] ?? $_POST['courseName'] ?? '';

echo '<div id="materialsManageContent" class="w-full">';
echo '<div id="addMaterialPanel">';
include 'materials_add.php';
echo '</div>';
echo '<div id="materialsTable">';
include 'materials_table.php';
echo '</div>';
echo '</div>';
?>
<script>
function toggleAddPanel() {
  const panel = document.getElementById('addMaterialPanel');
  if (!panel) return;
  panel.classList.toggle('visible');
  if (panel.classList.contains('visible')) {
    panel.scrollIntoView({ behavior: 'smooth' });
  }
}

if (!window.materialsTableClickBound) {
  window.materialsTableClickBound = true;
  document.addEventListener('click', function (e) {
    const table = e.target.closest('#materialsTable');
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
