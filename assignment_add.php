<?php

use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Forms\AssignmentForm;

if (!isset($container)) {
    require_once __DIR__.'/../../gibbon.php';
}
require_once 'moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_add.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$gibbonCourseClassID = (int)($_GET['gibbonCourseClassID'] ?? 0);
$gibbonCourseID = (int)($_GET['gibbonCourseID'] ?? 0);

if ($gibbonCourseClassID <= 0) {
    $page->addError(__('Invalid Course Class ID.'));
    return;
}

$processUrl = $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_process.php';

$form = Form::create('addAssignment', $processUrl);
$form->addHiddenValue('address', $session->get('address'));
$form->addHiddenValue('gibbonCourseClassID', $gibbonCourseClassID);
$form->addHiddenValue('gibbonCourseID', $gibbonCourseID);
$form->addHiddenValue('returnTo', 'manage');
$form->setAttribute('hx-post', $processUrl);
$form->setAttribute('hx-target', '#modalContent');
$form->setAttribute('hx-swap', 'innerHTML');

$assignmentForm = new AssignmentForm($form);
echo $assignmentForm->build()->getOutput();
