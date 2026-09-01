<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Http\Url;
use Gibbon\Module\CoursesAndClasses\Services\AssignmentService;

include '../../gibbon.php';
require_once __DIR__.'/moduleFunctions.php';

$moduleName = getModuleName($_POST['address'] ?? '') ?: 'Courses and Classes';
$gibbonAssignmentID = (int)($_POST['gibbonAssignmentID'] ?? 0);
$gibbonAssignmentSubmissionID = (int)($_POST['gibbonAssignmentSubmissionID'] ?? 0);
$redirect = Url::fromModuleRoute($moduleName, 'assignment_view.php')->withQueryParams(['gibbonAssignmentID' => $gibbonAssignmentID]);

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_grade.php') == false) {
    header('Location: '.$redirect->withQueryParam('return', 'error0'));
    exit;
}

if ($gibbonAssignmentSubmissionID <= 0) {
    header('Location: '.$redirect->withQueryParam('return', 'error1'));
    exit;
}

$connection = $container->get(Connection::class);
$staffID = (int)$session->get('gibbonPersonID');

try {
    (new AssignmentService($connection))->gradeAssignment(
        $staffID,
        $gibbonAssignmentSubmissionID,
        trim((string)($_POST['grade'] ?? '')),
        (float)($_POST['pointsEarned'] ?? 0),
        trim((string)($_POST['feedback'] ?? ''))
    );
} catch (Exception $e) {
    header('Location: '.$redirect->withQueryParam('return', 'error2'));
    exit;
}

header('Location: '.$redirect->withQueryParam('return', 'success0'));
