<?php
use Gibbon\Domain\DataSet;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Tables\DataTable;

require_once 'moduleFunctions.php';

$gateway = new CourseMaterialsGateway($pdo);
$materials = $gateway->selectByCourseNames([$courseName]);

$flatMaterials = [];
foreach ($materials as $course => $courseMaterials) {
    foreach ($courseMaterials as $material) {
        $flatMaterials[] = $material;
    }
}
$data = new DataSet($flatMaterials);

$absoluteURL = $session->get('absoluteURL');
$modulePath = $absoluteURL.'/modules/Courses and Classes';

$table = DataTable::create('CourseMaterials', null, ['class' => 'w-full mb-2 relative']);
$table->setTitle(__('📁 Course Materials for ') . htmlspecialchars($courseName));

$table->addHeaderAction('add', __('Add'))
    ->setURL('#')
    ->setAttribute('onclick', 'toggleAddPanel(); return false;')
    ->setAttribute('@click', 'modalOpen = true')
    ->setClass('button-visible');

$table->addColumn('name', __('Title'));
$table->addColumn('type', __('Type'));
$table->addColumn('timestamp', __('Uploaded'));

$table->addActionColumn()
    ->setClass('no-header')
    ->format(function ($row, $actions) use ($modulePath, $courseName) {
        $id = $row['gibbonResourceID'];
        $deleteUrl = $modulePath.'/materials_delete.php?courseName='.urlencode($courseName).'&materialID='.$id;

        $actions->addAction('remove', __('Confirm Deletion'))
            ->setURL('#')
            ->modalWindow()
            ->setClass('red button-invisible')
            ->setIcon('garbage')
            ->setAttribute('hx-get', $deleteUrl)
            ->setAttribute('hx-target', '#modalContent')
            ->setAttribute('hx-push-url', 'false')
            ->setAttribute('hx-swap', 'innerHTML');

        $actions->addAction('quit', __('Cancel'))
            ->setURL('#')
            ->setClass('button-invisible')
            ->setIcon('iconCross')
            ->setAttribute('@click', 'modalOpen = true');

        $actions->addAction('trash', __('Delete'))
            ->setURL('#')
            ->setClass('button-visible')
            ->setIcon('garbage')
            ->setAttribute('@click', 'modalOpen = true');
    });

echo $table->render($data);
