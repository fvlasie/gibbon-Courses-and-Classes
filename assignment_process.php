<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Http\Url;
use Gibbon\Module\CoursesAndClasses\Services\AssignmentService;
use Gibbon\Module\CoursesAndClasses\Domain\Assignment;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;

include '../../gibbon.php';
require_once __DIR__.'/moduleFunctions.php';

$moduleName = getModuleName($_POST['address'] ?? '') ?: 'Courses and Classes';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_add.php') == false
    && isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_edit.php') == false) {
    header('Location: '.Url::fromModuleRoute($moduleName, 'coursesAndClasses_view.php')->withQueryParam('return', 'error0'));
    exit;
}

$connection = $container->get(Connection::class);
$gibbonAssignmentID = (int)($_POST['gibbonAssignmentID'] ?? 0);
$assignmentGateway = new AssignmentGateway($connection);

if ($gibbonAssignmentID > 0) {
    $assignmentData = $assignmentGateway->getAssignmentByID($gibbonAssignmentID);
    if (empty($assignmentData)) {
        header('Location: '.Url::fromModuleRoute($moduleName, 'coursesAndClasses_view.php')->withQueryParam('return', 'error1'));
        exit;
    }
    $assignment = new Assignment($assignmentData);
} else {
    $assignment = new Assignment();
}

$assignment->name = trim((string)($_POST['name'] ?? ''));
$assignment->description = (string)($_POST['description'] ?? '');
$assignment->dueDate = (string)($_POST['dueDate'] ?? '');
$assignment->dueTime = (string)($_POST['dueTime'] ?? '') ?: null;
$assignment->points = (float)($_POST['points'] ?? 10);
$assignment->type = (string)($_POST['type'] ?? 'written');
$assignment->category = (string)($_POST['category'] ?? '') ?: null;
$assignment->status = (string)($_POST['status'] ?? 'Published');

$allowedStatus = ['Draft', 'Published', 'Closed'];
if (!in_array($assignment->status, $allowedStatus, true)) {
    $assignment->status = 'Published';
}

if (empty($assignment->gibbonAssignmentID)) {
    $gibbonCourseClassID = (int)($_POST['gibbonCourseClassID'] ?? 0);
    $assignment->gibbonCourseClassID = $gibbonCourseClassID;

    $assignment->gibbonCourseID = (int)$pdo->selectOne(
        'SELECT gibbonCourseID FROM gibbonCourseClass WHERE gibbonCourseClassID = :gibbonCourseClassID',
        ['gibbonCourseClassID' => $gibbonCourseClassID]
    );
    $assignment->gibbonSchoolYearID = (int)$session->get('gibbonSchoolYearID');
    $assignment->gibbonPersonIDCreator = (int)$session->get('gibbonPersonID');
    $assignment->gibbonStaffID = (int)$pdo->selectOne(
        'SELECT gibbonStaffID FROM gibbonStaff WHERE gibbonPersonID = :gibbonPersonID',
        ['gibbonPersonID' => $session->get('gibbonPersonID')]
    );
} else {
    $assignment->gibbonPersonIDLastEdit = (int)$session->get('gibbonPersonID');
}

try {
    $assignmentService = new AssignmentService($connection);
    if (empty($assignment->gibbonAssignmentID)) {
        $gibbonAssignmentID = $assignmentService->createAssignment($assignment);
    } else {
        $assignmentService->updateAssignment($assignment);
        $gibbonAssignmentID = $assignment->gibbonAssignmentID;
    }
} catch (Exception $e) {
    if (($_POST['returnTo'] ?? '') === 'manage' || !empty($_SERVER['HTTP_HX_REQUEST'])) {
        echo "<div class='error'>".__('Error processing assignment: ').htmlspecialchars($e->getMessage())."</div>";
        exit;
    }
    header('Location: '.Url::fromModuleRoute($moduleName, 'coursesAndClasses_view.php')->withQueryParam('return', 'error2'));
    exit;
}

$gibbonCourseID = (int)($_POST['gibbonCourseID'] ?? $assignment->gibbonCourseID);
$gibbonCourseClassID = (int)($_POST['gibbonCourseClassID'] ?? $assignment->gibbonCourseClassID);
$returnTo = $_POST['returnTo'] ?? '';

if ($returnTo === 'manage' || !empty($_SERVER['HTTP_HX_REQUEST'])) {
    $_GET['gibbonCourseID'] = $gibbonCourseID;
    $_GET['gibbonCourseClassID'] = $gibbonCourseClassID;
    include __DIR__.'/assignment_manage.php';
    exit;
}

header('Location: '.Url::fromModuleRoute($moduleName, 'assignment_view.php')->withQueryParams([
    'gibbonAssignmentID' => $gibbonAssignmentID,
    'return' => 'success0',
]));
