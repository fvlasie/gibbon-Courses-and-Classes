<?php
require_once 'moduleFunctions.php';

use Gibbon\Contracts\Database\Connection;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentSubmissionGateway;
use Gibbon\Module\CoursesAndClasses\Domain\Assignment;

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_view.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

global $container;
$connection = $container->get(Connection::class);

// Ensure we have the required assignment ID
$gibbonAssignmentID = (int)($_GET['gibbonAssignmentID'] ?? 0);

if ($gibbonAssignmentID <= 0) {
    $page->addError(__('Invalid Assignment ID.'));
    return;
}

// Fetch the assignment
$assignmentGateway = new AssignmentGateway($connection);
$assignmentData = $assignmentGateway->getAssignmentByID($gibbonAssignmentID);

if (!$assignmentData) {
    $page->addError(__('The specified record cannot be found.'));
    return;
}
 
// Map data to Domain Model
$assignment = new Assignment($assignmentData);

// Fetch submissions for this assignment
$submissionGateway = new AssignmentSubmissionGateway($connection);
$submissions = $submissionGateway->getSubmissionsByAssignment($gibbonAssignmentID);

$page->breadcrumbs
    ->add($session->get('module'))
    ->add('Overview', 'coursesAndClasses_view.php')
    ->add(__('View Assignment'));

$page->return->addReturns([
    'success0' => __('Your changes were saved successfully.'),
    'error3' => __('Please upload a file to submit your assignment.'),
]);

if (coursesAndClassesCanSubmitAssignment($session)
    && isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_submit.php')) {
    $page->navigator->addHeaderAction('submit', __('Submit Assignment'))
        ->setURL('/fullscreen.php')
        ->addParam('q', '/modules/Courses and Classes/assignment_submit.php')
        ->addParam('gibbonAssignmentID', $gibbonAssignmentID)
        ->directLink(true)
        ->modalWindow();
}

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_edit.php')) {
    $page->navigator->addHeaderAction('edit', __('Edit'))
        ->setURL('/fullscreen.php')
        ->addParam('q', '/modules/Courses and Classes/assignment_edit.php')
        ->addParam('gibbonAssignmentID', $gibbonAssignmentID)
        ->directLink(true)
        ->modalWindow();
}
?>

<div class="assignment-view">
    <h1><?php echo htmlspecialchars($assignment->name); ?></h1>
    
    <div class="assignment-details">
        <p><strong><?php echo __('Due Date:'); ?></strong> <?php echo htmlspecialchars($assignment->dueDate); ?> <?php echo htmlspecialchars($assignment->dueTime); ?></p>
        <p><strong><?php echo __('Points:'); ?></strong> <?php echo htmlspecialchars($assignment->points); ?></p>
        <p><strong><?php echo __('Status:'); ?></strong> <?php echo htmlspecialchars($assignment->status); ?></p>
        <p><strong><?php echo __('Type:'); ?></strong> <?php echo htmlspecialchars($assignment->type); ?></p>
        <hr>
        <div class="assignment-description">
            <?php echo nl2br(htmlspecialchars($assignment->description)); ?>
        </div>
    </div>

    <div class="assignment-submissions">
        <h2><?php echo __('Submissions'); ?></h2>
        <?php if (empty($submissions)): ?>
            <p><?php echo __('No submissions yet.'); ?></p>
        <?php else: ?>
            <table class="assignment-submissions-table">
                <thead>
                    <tr>
                        <th><?php echo __('Student'); ?></th>
                        <th><?php echo __('Status'); ?></th>
                        <th><?php echo __('Grade'); ?></th>
                        <th><?php echo __('Points'); ?></th>
                        <th><?php echo __('Submitted'); ?></th>
                        <th><?php echo __('File'); ?></th>
                        <th><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($submission['studentFirstName'] . ' ' . $submission['studentSurname']); ?></td>
                            <td><?php echo htmlspecialchars($submission['status']); ?></td>
                            <td><?php echo htmlspecialchars($submission['grade'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($submission['pointsEarned'] ?? '0'); ?></td>
                            <td><?php echo htmlspecialchars($submission['submittedDate']); ?></td>
                            <td>
                                <?php if (!empty($submission['external_doc_id'])): ?>
                                    <a href="<?php echo htmlspecialchars($session->get('absoluteURL').'/'.$submission['external_doc_id']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($submission['external_submission_id'] ?: __('File')); ?>
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php $gradeUrl = buildURL('assignment_grade.php', ['gibbonAssignmentSubmissionID' => $submission['gibbonAssignmentSubmissionID']]); ?>
                                <a href="<?php echo $gradeUrl; ?>">
                                    <?php echo __('Grade'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>


<style>
.assignment-view {
    padding: 20px;
}
.assignment-details {
    margin-bottom: 30px;
    padding: 15px;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
}
.assignment-submissions-table {
    width: 100%;
    border-collapse: collapse;
}
.assignment-submissions-table th, .assignment-submissions-table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}
.assignment-submissions-table th {
    background-color: #eee;
}
</style>
