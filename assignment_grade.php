<?php
require_once 'moduleFunctions.php';

use Gibbon\Contracts\Database\Connection;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentSubmissionGateway;

global $container;
$connection = $container->get(Connection::class);

// Security check
if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_grade.php') == false) {
    $page->addError(__m('You do not have access to this action.'));
    return;
}

$gibbonAssignmentSubmissionID = (int)($_GET['gibbonAssignmentSubmissionID'] ?? 0);

if ($gibbonAssignmentSubmissionID <= 0) {
    echo "<div class='warning'>" . __('Invalid Submission ID.') . "</div>";
    return;
}

// Fetch submission details
$submissionGateway = new AssignmentSubmissionGateway($connection);
$submissionSql = "
    SELECT s.*, a.name AS assignmentName, a.points AS maxPoints, a.gibbonAssignmentID,
           p.preferredName AS studentFirstName, p.surname AS studentSurname
    FROM gibbonAssignmentSubmission AS s
    INNER JOIN gibbonAssignment AS a ON s.gibbonAssignmentID = a.gibbonAssignmentID
    INNER JOIN gibbonPerson AS p ON s.gibbonPersonID = p.gibbonPersonID
    WHERE s.gibbonAssignmentSubmissionID = :submissionID
";
$submissionResult = $connection->executeQuery(['submissionID' => $gibbonAssignmentSubmissionID], $submissionSql);
$submission = $submissionResult->fetch();

if (!$submission) {
    echo "<div class='warning'>" . __('Submission not found.') . "</div>";
    return;
}

$page->breadcrumbs
    ->add($session->get('module'))
    ->add('Overview', 'coursesAndClasses_view.php')
    ->add(__('Grade Submission'));

echo "<h2>" . __('Grade Assignment Submission') . "</h2>";

$form = Form::create('gradeAssignment', $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_grade_process.php');
$form->setMethod('post');
$form->addHiddenValue('address', $session->get('address'));
$form->addHiddenValue('gibbonAssignmentSubmissionID', $gibbonAssignmentSubmissionID);
$form->addHiddenValue('gibbonAssignmentID', $submission['gibbonAssignmentID']);

$row = $form->addRow();
$row->addLabel('student', __('Student'));
$row->addTextField('studentDisplay')
    ->setValue($submission['studentFirstName'] . ' ' . $submission['studentSurname'])
    ->readonly();

$row = $form->addRow();
$row->addLabel('assignment', __('Assignment'));
$row->addTextField('assignmentDisplay')
    ->setValue($submission['assignmentName'] . ' (Max Points: ' . ($submission['maxPoints'] ?? 'N/A') . ')')
    ->readonly();

$row = $form->addRow();
$row->addLabel('submittedDate', __('Submitted Date'));
$row->addTextField('submittedDateDisplay')
    ->setValue(($submission['submittedDate'] ?? '-') . ' ' . ($submission['submittedTime'] ?? ''))
    ->readonly();

if (!empty($submission['external_doc_id'])) {
    $row = $form->addRow();
        $row->addLabel('submissionFile', __('Submitted Work'));
        $fileUrl = $session->get('absoluteURL').'/'.$submission['external_doc_id'];
        $fileLabel = htmlspecialchars($submission['external_submission_id'] ?: __('Download'));
        $row->addContent('<p style="margin-top:7px;"><a href="'.htmlspecialchars($fileUrl).'" target="_blank">'.$fileLabel.'</a></p>');
}

$row = $form->addRow();
$row->addLabel('grade', __('Grade (Letter/Status)'));
$row->addTextField('grade')
    ->setValue($submission['grade'] ?? '')
    ->placeholder('e.g. A, B+, Pass');

$row = $form->addRow();
$row->addLabel('pointsEarned', __('Points Earned'))->description(__('Leave blank if this assignment has not been given points yet.'));
$pointsEarned = $row->addNumber('pointsEarned')->decimalPlaces(2)->minimum(0);
if ($submission['pointsEarned'] !== null && $submission['pointsEarned'] !== '') {
    $pointsEarned->setValue($submission['pointsEarned']);
}
if ($submission['maxPoints'] !== null && $submission['maxPoints'] !== '') {
    $pointsEarned->maximum((float) $submission['maxPoints']);
}

$row = $form->addRow();
$row->addLabel('feedback', __('Qualitative Feedback / Comments'));
$row->addTextArea('feedback')
    ->setValue($submission['feedback'] ?? '')
    ->setRows(5);

$row = $form->addRow();
$row->addSubmit(__('Save Grade & Feedback'));

echo $form->getOutput();
