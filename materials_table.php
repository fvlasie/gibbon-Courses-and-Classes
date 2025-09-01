<?php
use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Tables\DataTable;

require_once 'moduleFunctions.php';
// Setup
$gateway = new CourseMaterialsGateway($pdo);
$materials = $gateway->selectByCourseNames([$courseName]);
$material = [];

// Flatten materials for DataTable
$flatMaterials = [];
foreach ($materials as $course => $courseMaterials) {
    foreach ($courseMaterials as $material) {
        $flatMaterials[] = $material;
    }
}
$data = new DataSet($flatMaterials);

$table = DataTable::create('CourseMaterials', null, ['class' => 'w-full mb-2 relative']);
$table->setTitle(__('📁 Course Materials for ') . htmlspecialchars($courseName));

$table->addHeaderAction('add', __('Add'))
    ->setURL('#')
    ->setAttribute('onclick', 'toggleAddPanel();')
    ->setAttribute('@click', 'modalOpen = true')
    ->setAttribute('hx-post', $session->get('absoluteURL') . '/modules/Courses and Classes/materials_add.php?courseName=' . urlencode($courseName))
    ->setAttribute('hx-target', '#addMaterialPanel')
    ->setAttribute('hx-swap', 'innerHTML')
    ->setClass('button-visible');

$table->addColumn('name', __('Title'));
$table->addColumn('type', __('Type'));
$table->addColumn('timestamp', __('Uploaded'));

$table->addActionColumn()
    ->setClass('no-header')
    ->format(function ($row, $actions) use ($session, $courseName) {
        $id = $row['gibbonResourceID'];

        $actions->addAction('delete', __('Confirm Deletion'))
            ->setURL('modules/Courses and Classes/materials_delete.php')
            ->setAttribute('@click', 'modalOpen = true')
            ->setClass('red button-invisible')
            ->setAttribute('hx-post', 'modules/Courses and Classes/materials_delete.php?courseName=' . urlencode($courseName).'&materialID='.$id)
            ->setAttribute('hx-target', '#materialsTable')
            ->setAttribute('hx-swap', 'innerHTML')
            ->setIcon('garbage');

        $actions->addAction('quit', __('Cancel'))
            ->setURL('#') // Prevent default navigation
            ->setAttribute('@click', 'modalOpen = true')
            ->setClass('button-invisible')
            ->setIcon('iconCross');

        $actions->addAction('trash', __('Delete'))
            ->setURL('#') // Prevent default navigation
            ->setAttribute('@click', 'modalOpen = true')
            ->setClass('button-visible')
            ->setIcon('garbage');
    });

echo '<div id="materialsTable" >';
echo $table->render($data);
echo '</div>';
?>
