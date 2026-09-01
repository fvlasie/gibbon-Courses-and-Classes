<?php

use Gibbon\Domain\DataSet;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Tables\DataTable;

require_once 'moduleFunctions.php';

$connection = $container->get(\Gibbon\Contracts\Database\Connection::class);
$assignments = (new AssignmentGateway($connection))->getAssignmentsByCourse((int)$gibbonCourseID);
$data = new DataSet($assignments ?: []);
$absoluteURL = $session->get('absoluteURL');
$modulePath = $absoluteURL.'/modules/Courses and Classes';

$table = DataTable::create('courseAssignments', null, ['class' => 'w-full']);
$table->setTitle(__('Assignments').(!empty($courseName) ? ': '.htmlspecialchars($courseName) : ''));

$table->addHeaderAction('add', __('Add'))
    ->setURL('#')
    ->setAttribute('onclick', 'toggleAddAssignmentPanel(); return false;')
    ->setAttribute('@click', 'modalOpen = true');

$table->addColumn('name', __('Assignment'));
$table->addColumn('dueDate', __('Due Date'));
$table->addColumn('status', __('Status'));
$table->addColumn('points', __('Points'));

$table->addActionColumn()
    ->setClass('no-header')
    ->format(function ($row, $actions) use ($modulePath, $gibbonCourseID, $gibbonCourseClassID) {
        $id = (int)$row['gibbonAssignmentID'];
        $editUrl = $modulePath.'/assignment_edit.php?gibbonAssignmentID='.$id.'&gibbonCourseID='.(int)$gibbonCourseID.'&gibbonCourseClassID='.(int)$gibbonCourseClassID;
        $deleteUrl = $modulePath.'/assignment_delete.php?gibbonAssignmentID='.$id.'&gibbonCourseID='.(int)$gibbonCourseID.'&gibbonCourseClassID='.(int)$gibbonCourseClassID;

        // Name must not be "delete" — Gibbon sizes the modal to max-w-2xl for that action.
        $actions->addAction('edit', __('Edit'))
            ->setURL('#')
            ->modalWindow()
            ->setAttribute('hx-get', $editUrl)
            ->setAttribute('hx-target', '#modalContent')
            ->setAttribute('hx-push-url', 'false')
            ->setAttribute('hx-swap', 'innerHTML');

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
