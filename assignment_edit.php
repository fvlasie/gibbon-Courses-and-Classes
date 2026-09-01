<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Forms\AssignmentForm;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Domain\Assignment;

if (!isset($container)) {
    require_once __DIR__.'/../../gibbon.php';
}
require_once 'moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_edit.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$connection = $container->get(Connection::class);
$gibbonAssignmentID = (int)($_GET['gibbonAssignmentID'] ?? 0);
$gibbonCourseID = (int)($_GET['gibbonCourseID'] ?? 0);
$gibbonCourseClassID = (int)($_GET['gibbonCourseClassID'] ?? 0);

if ($gibbonAssignmentID <= 0) {
    $page->addError(__('Invalid Assignment ID.'));
    return;
}

$assignmentData = (new AssignmentGateway($connection))->getAssignmentByID($gibbonAssignmentID);
if (empty($assignmentData)) {
    $page->addError(__('The specified record cannot be found.'));
    return;
}

if ($gibbonCourseID <= 0) {
    $gibbonCourseID = (int)($assignmentData['gibbonCourseID'] ?? 0);
}
if ($gibbonCourseClassID <= 0) {
    $gibbonCourseClassID = (int)($assignmentData['gibbonCourseClassID'] ?? 0);
}

$processUrl = $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_process.php';
$fromManage = $gibbonCourseID > 0;

$form = Form::create('editAssignment', $processUrl);
$form->addHiddenValue('address', $session->get('address'));
$form->addHiddenValue('gibbonAssignmentID', $gibbonAssignmentID);
$form->addHiddenValue('gibbonCourseID', $gibbonCourseID);
$form->addHiddenValue('gibbonCourseClassID', $gibbonCourseClassID);

if ($fromManage) {
    $manageUrl = $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_manage.php?gibbonCourseID='.$gibbonCourseID.'&gibbonCourseClassID='.$gibbonCourseClassID;
    $form->addHiddenValue('returnTo', 'manage');
    $form->setAttribute('hx-post', $processUrl);
    $form->setAttribute('hx-target', '#modalContent');
    $form->setAttribute('hx-swap', 'innerHTML');
}

$assignmentForm = new AssignmentForm($form, new Assignment($assignmentData));
echo $assignmentForm->build()->getOutput();

if ($fromManage) {
    echo '<p><a href="#" hx-get="'.htmlspecialchars($manageUrl).'" hx-target="#modalContent" hx-swap="innerHTML">'.__('Back to Assignments').'</a></p>';
}
