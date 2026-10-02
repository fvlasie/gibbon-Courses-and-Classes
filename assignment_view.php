<?php
require_once 'moduleFunctions.php';

use Gibbon\Contracts\Database\Connection;
use Gibbon\Forms\Form;
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
 
$canGrade = isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_grade.php');

if (!$canGrade && ($assignmentData['status'] ?? '') === 'Draft') {
    $page->addError(__('The specified record cannot be found.'));
    return;
}

// Map data to Domain Model
$assignment = new Assignment($assignmentData);

// Graders see every submission; everyone else only sees their own
$submissionGateway = new AssignmentSubmissionGateway($connection);
$submissions = $canGrade
    ? $submissionGateway->getSubmissionsByAssignment($gibbonAssignmentID)
    : $submissionGateway->getSubmissionsByAssignmentAndPerson($gibbonAssignmentID, (int)$session->get('gibbonPersonID'));

$page->breadcrumbs
    ->add($session->get('module'))
    ->add('Overview', 'coursesAndClasses_view.php')
    ->add(__('View Assignment'));

$page->return->addReturns([
    'success0' => __('Your changes were saved successfully.'),
    'warning1' => __('Some rows were not saved. Points must be a number from 0 up to the assignment maximum, and a grade can be at most 50 characters.'),
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

    <?php if (!$canGrade): ?>
    <div class="assignment-submissions">
        <h2><?php echo __('My Submission'); ?></h2>
        <?php if (empty($submissions)): ?>
            <p><?php echo __('You have not submitted this assignment yet.'); ?></p>
        <?php else: ?>
            <?php foreach ($submissions as $submission): ?>
                <div class="assignment-details">
                    <p><strong><?php echo __('Status'); ?>:</strong> <?php echo htmlspecialchars($submission['status']); ?></p>
                    <p><strong><?php echo __('Submitted'); ?>:</strong> <?php echo htmlspecialchars(trim(($submission['submittedDate'] ?? '').' '.($submission['submittedTime'] ?? ''))); ?></p>
                    <?php if (!empty($submission['external_doc_id'])): ?>
                        <p><strong><?php echo __('File'); ?>:</strong>
                            <a href="<?php echo htmlspecialchars($session->get('absoluteURL').'/'.$submission['external_doc_id']); ?>" target="_blank"><?php echo htmlspecialchars($submission['external_submission_id'] ?: __('File')); ?></a>
                        </p>
                    <?php endif; ?>
                    <?php if (in_array($submission['status'], ['Graded', 'Returned'], true)): ?>
                        <p><strong><?php echo __('Grade'); ?>:</strong> <?php echo htmlspecialchars($submission['grade'] ?? '-'); ?></p>
                        <p><strong><?php echo __('Points'); ?>:</strong> <?php echo $submission['pointsEarned'] !== null ? htmlspecialchars($submission['pointsEarned']).' / '.htmlspecialchars($assignment->points) : '-'; ?></p>
                        <p><strong><?php echo __('Feedback'); ?>:</strong></p>
                        <div class="assignment-description"><?php echo !empty($submission['feedback']) ? nl2br(htmlspecialchars($submission['feedback'])) : '<em>'.__('No feedback given.').'</em>'; ?></div>
                    <?php else: ?>
                        <p><em><?php echo __('Not graded yet.'); ?></em></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="assignment-submissions">
        <?php
        $classAssignments = $assignmentGateway->getAssignmentsByClass($assignment->gibbonCourseClassID);
        usort($classAssignments, function ($left, $right) {
            $byDate = strcmp((string) $left['dueDate'], (string) $right['dueDate']);
            return $byDate !== 0 ? $byDate : ((int) $left['gibbonAssignmentID'] <=> (int) $right['gibbonAssignmentID']);
        });
        $classAssignmentIDs = array_map('intval', array_column($classAssignments, 'gibbonAssignmentID'));
        $assignmentIndex = array_search($gibbonAssignmentID, $classAssignmentIDs, true);
        $neighborParams = ($_GET['show'] ?? '') === 'ungraded' ? ['show' => 'ungraded'] : [];
        ?>
        <p class="assignment-neighbors">
            <?php if ($assignmentIndex !== false && $assignmentIndex > 0): ?>
                <a href="<?php echo buildURL('assignment_view.php', ['gibbonAssignmentID' => $classAssignmentIDs[$assignmentIndex - 1]] + $neighborParams); ?>"><?php echo __('Previous Assignment'); ?></a>
            <?php endif; ?>
            <?php if ($assignmentIndex !== false && $assignmentIndex < count($classAssignmentIDs) - 1): ?>
                <a href="<?php echo buildURL('assignment_view.php', ['gibbonAssignmentID' => $classAssignmentIDs[$assignmentIndex + 1]] + $neighborParams); ?>"><?php echo __('Next Assignment'); ?></a>
            <?php endif; ?>
        </p>
        <h2><?php echo __('Grade the Class'); ?></h2>
        <?php
        $roster = $submissionGateway->getClassRoster($gibbonAssignmentID);
        $ungradedOnly = ($_GET['show'] ?? '') === 'ungraded';
        $visible = [];
        foreach ($roster as $student) {
            $graded = in_array($student['status'] ?? '', ['Graded', 'Returned'], true);
            if (!$ungradedOnly || !$graded) {
                $visible[] = $student;
            }
        }
        $allUrl = buildURL('assignment_view.php', ['gibbonAssignmentID' => $gibbonAssignmentID]);
        $ungradedUrl = buildURL('assignment_view.php', ['gibbonAssignmentID' => $gibbonAssignmentID, 'show' => 'ungraded']);
        ?>
        <p>
            <a href="<?php echo $allUrl; ?>"><?php echo __('All Students'); ?></a>
            | <a href="<?php echo $ungradedUrl; ?>"><?php echo __('Ungraded'); ?></a>
            <span class="text-gray-600"><?php echo sprintf(__('%1$s students, %2$s ungraded'), count($roster), count(array_filter($roster, function ($student) {
                return !in_array($student['status'] ?? '', ['Graded', 'Returned'], true);
            }))); ?></span>
        </p>
        <?php if (empty($visible)): ?>
            <p><?php echo $ungradedOnly ? __('Every student on this roster has a grade.') : __('There are no students enrolled in this class.'); ?></p>
        <?php else:
            $gradeForm = Form::create('gradeRoster', $session->get('absoluteURL').'/modules/'.$session->get('module').'/assignment_grade_process.php');
            $gradeForm->addHiddenValue('address', $session->get('address'));
            $gradeForm->addHiddenValue('intent', 'gradeRoster');
            $gradeForm->addHiddenValue('gibbonAssignmentID', $gibbonAssignmentID);
            $gradeForm->addHiddenValue('show', $ungradedOnly ? 'ungraded' : '');
            $gradeForm->addHiddenValue('count', count($visible));
            $maxPoints = $assignment->points;
            $rows = '';
            foreach ($visible as $index => $student) {
                $pointsValue = ($student['pointsEarned'] === null || $student['pointsEarned'] === '') ? '' : htmlspecialchars((string) $student['pointsEarned'], ENT_QUOTES, 'UTF-8');
                $file = '';
                if (!empty($student['external_doc_id'])) {
                    $fileUrl = htmlspecialchars($session->get('absoluteURL').'/'.$student['external_doc_id'], ENT_QUOTES, 'UTF-8');
                    $fileLabel = htmlspecialchars($student['external_submission_id'] ?: __('File'), ENT_QUOTES, 'UTF-8');
                    $file = '<a href="'.$fileUrl.'" target="_blank">'.$fileLabel.'</a>';
                }
                $detail = '';
                if (!empty($student['gibbonAssignmentSubmissionID'])) {
                    $detail = '<br/><a href="'.buildURL('assignment_grade.php', ['gibbonAssignmentSubmissionID' => (int) $student['gibbonAssignmentSubmissionID']]).'">'.__('Details').'</a>';
                }
                $rows .= '<tr>'
                    .'<td>'.htmlspecialchars($student['studentFirstName'].' '.$student['studentSurname'], ENT_QUOTES, 'UTF-8')
                    .'<input type="hidden" name="gibbonPersonID'.$index.'" value="'.(int) $student['gibbonPersonID'].'"></td>'
                    .'<td>'.htmlspecialchars($student['status'] ?: __('Not Started'), ENT_QUOTES, 'UTF-8').$detail.'</td>'
                    .'<td>'.($file !== '' ? $file : '—').'</td>'
                    .'<td><input type="text" name="grade'.$index.'" maxlength="50" value="'.htmlspecialchars((string) ($student['grade'] ?? ''), ENT_QUOTES, 'UTF-8').'" class="w-full"></td>'
                    .'<td><input type="number" name="points'.$index.'" min="0" step="0.01"'.($maxPoints !== null ? ' max="'.htmlspecialchars((string) $maxPoints, ENT_QUOTES, 'UTF-8').'"' : '').' value="'.$pointsValue.'" class="w-20"></td>'
                    .'<td><textarea name="feedback'.$index.'" rows="2" class="w-full">'.htmlspecialchars((string) ($student['feedback'] ?? ''), ENT_QUOTES, 'UTF-8').'</textarea></td>'
                    .'</tr>';
            }
            $gradeForm->addRow()->addContent(
                '<table class="assignment-submissions-table"><thead><tr>'
                .'<th>'.__('Student').'</th><th>'.__('Status').'</th><th>'.__('File').'</th>'
                .'<th>'.__('Grade').'</th><th>'.__('Points').'</th><th>'.__('Feedback').'</th>'
                .'</tr></thead><tbody>'.$rows.'</tbody></table>'
                .'<p class="text-gray-600">'.__('Leave points blank when a student has not been given a score. Saving this page does not turn a blank into 0.').'</p>'
            );
            $gradeForm->addRow()->addSubmit(__('Save Grades'));
            echo $gradeForm->getOutput();
        endif; ?>
    </div>
    <?php endif; ?>
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
