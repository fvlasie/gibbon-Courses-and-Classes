<?php
use Gibbon\Contracts\Database\Connection;
use Gibbon\Tables\DataTable;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Domain\DataSet;
use Gibbon\Forms\Prefab\DeleteForm;
use Gibbon\Forms\Layout;

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
$data = new DataSet($flatMaterials);

// Render DataTable
$table = DataTable::create('CourseMaterials');
$table->setTitle(__('📁 Course Materials: ') . htmlspecialchars($courseName));

$table->addHeaderAction('add', __('Add'))
    ->setURL('#')
    ->setClass('add-toggle')
    ->displayLabel();

$table->addColumn('name', __('Title'));
$table->addColumn('type', __('Type'));
$table->addColumn('timestamp', __('Uploaded'));

$table->addActionColumn()
    ->setClass('no-header')
    ->format(function ($row, $actions) use ($session, $courseName) {
        $id = $row['gibbonResourceID'];
        $deleteClass = 'delete-confirm-' . $id;

        // Delete trigger button
        $actions->addAction('delete', __('Delete'))
            ->setURL('modules/Courses and Classes/materials_delete.php') 
            ->addParam('materialID', $id);
    });

echo $table->render($data);
?>

echo '<table class=" w-full mb-2 relative" cellspacing="0">';
foreach ($flatMaterials as $material) {
    
    echo '<tr>';
    echo '<td>' . htmlspecialchars($material['name']) . '</td>';
    echo '<td>' . htmlspecialchars($material['type']) . '</td>';
    echo '<td><button class="confirm-toggle" data-id="' . $id . '">Delete</button></td>';
    echo '</tr>';

    echo '<tr class="confirm-row" id="confirm-' . $id . '" style="display:none;">';
    echo '<td colspan="3">';
    echo '<div class="slideInConfirm">';

    // Create the form
    $form = Form::create('deleteMaterial_' . $id, $session->get('absoluteURL') . '/modules/Courses and Classes/materials_delete.php');
    $form->setMethod('post');

    // Hidden field
    $form->addHiddenValue('materialID', $id);

    // Action buttons
    $row = $form->addRow();
    $row->addContent('<p class="text-red-700">' . __('Please confirm deletion. This cannot be undone!') . '<br></p>');
    $row->addButton(__('Cancel'))
        ->setClass('cancel-toggle')
        ->setAttribute('type', 'button')
        ->setAttribute('data-id', $id);
    $row->addSubmit(__('Confirm Deletion'));

    // Output the form
    echo $form->getOutput();
    echo '</div>';
    echo '</td></tr>';
}
echo '</table>';
