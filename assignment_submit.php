<?php
require_once 'moduleFunctions.php';

use Gibbon\Contracts\Database\Connection;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Forms\AssignmentSubmissionForm;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_submit.php') == false
    || !coursesAndClassesCanSubmitAssignment($session)) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$connection = $container->get(Connection::class);
$gibbonAssignmentID = (int)($_GET['gibbonAssignmentID'] ?? 0);
$gibbonPersonID = (int)$session->get('gibbonPersonID');

if ($gibbonAssignmentID <= 0 || $gibbonPersonID <= 0) {
    $page->addError(__('Invalid request. Assignment or Student ID missing.'));
    return;
}

$assignmentData = (new AssignmentGateway($connection))->getAssignmentByID($gibbonAssignmentID);
if (empty($assignmentData)) {
    $page->addError(__('The specified record cannot be found.'));
    return;
}

$form = Form::create('submitAssignment', $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_submit_process.php');
$form->addHiddenValue('address', $session->get('address'));

$submissionForm = new AssignmentSubmissionForm($form, $gibbonAssignmentID, $gibbonPersonID);
echo $submissionForm->build()->getOutput();
