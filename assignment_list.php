<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Tables\StudentAssignmentListTable;

require_once __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_list.php') == false) {
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs->add(__('My Assignments'));

    $gibbonPersonID = (int)$session->get('gibbonPersonID');
    $gibbonSchoolYearID = (int)$session->get('gibbonSchoolYearID');
    $connection = $container->get(Connection::class);
    $gateway = new AssignmentGateway($connection);
    $studentView = coursesAndClassesCanSubmitAssignment($session);

    $assignments = $gateway->getAssignmentsForPerson($gibbonPersonID, $gibbonSchoolYearID, $studentView);

    if (empty($assignments) && !$studentView
        && isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_edit.php')) {
        $assignments = $gateway->getAssignmentsBySchoolYear($gibbonSchoolYearID);
    }

    echo StudentAssignmentListTable::create(new DataSet($assignments ?: []))->getOutput();
}
