<?php
use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Tables\DataTable;

require_once 'moduleFunctions.php';

// Setup
$courseName = $_GET['courseName'] ?? '';
$gateway = new CourseMaterialsGateway($pdo);
$materials = $gateway->selectByCourseNames([$courseName]);

// Flatten materials for DataTable
$flatMaterials = [];
foreach ($materials as $course => $courseMaterials) {
    foreach ($courseMaterials as $material) {
        $flatMaterials[] = $material;
    }
}

echo '<div id="addMaterialPanel">';
include 'materials_add.php';
echo '</div>';
echo '<div id="materialsTable">';
include 'materials_table.php';
echo '</div>';

?>
<script>
document.querySelector('#materialsTable').addEventListener('click', function (e) {
  const btn = e.target.closest('a'); // Changed from 'button' to 'a'
  if (!btn) return;

  const row = btn.closest('tr');
  if (!row) return;

  const isVisible = btn.classList.contains('button-visible');
  const isInvisible = btn.classList.contains('button-invisible');

  if (isVisible || isInvisible) {
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
  }
});
function toggleAddPanel() {
  const panel = document.getElementById('addMaterialPanel');
  panel.classList.toggle('visible');

  // Optional: scroll into view when opening
  if (panel.classList.contains('visible')) {
    panel.scrollIntoView({ behavior: 'smooth' });
  }
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('/modules/Courses and Classes/materials_upload.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(html => {
    document.querySelector('#materialsTable').innerHTML = html;
  })
  .catch(error => {
    console.error('Upload failed:', error);
    alert('Something went wrong during upload.');
  });
});

</script>