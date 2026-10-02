<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Http\Url;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentSubmissionGateway;
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

$connection = $container->get(Connection::class);
$staffID = (int)$session->get('gibbonPersonID');
$service = new AssignmentService($connection);

if (($_POST['intent'] ?? '') === 'gradeRoster') {
    $assignment = (new AssignmentGateway($connection))->getAssignmentByID($gibbonAssignmentID);
    if (empty($assignment)) {
        header('Location: '.$redirect->withQueryParam('return', 'error1'));
        exit;
    }

    $roster = [];
    foreach ((new AssignmentSubmissionGateway($connection))->getClassRoster($gibbonAssignmentID) as $student) {
        $roster[(int) $student['gibbonPersonID']] = $student;
    }

    $saved = 0;
    $invalid = 0;
    $count = min(500, (int) ($_POST['count'] ?? 0));
    for ($i = 0; $i < $count; $i++) {
        $personID = (int) ($_POST['gibbonPersonID'.$i] ?? 0);
        if (!isset($roster[$personID])) {
            $invalid++;
            continue;
        }

        $pointsRaw = trim((string) ($_POST['points'.$i] ?? ''));
        if ($pointsRaw !== '' && !is_numeric($pointsRaw)) {
            $invalid++;
            continue;
        }

        $result = $service->saveRosterGrade(
            $staffID,
            $assignment,
            $roster[$personID],
            trim((string) ($_POST['grade'.$i] ?? '')),
            $pointsRaw === '' ? null : round((float) $pointsRaw, 2),
            trim((string) ($_POST['feedback'.$i] ?? ''))
        );
        if ($result === 'invalid') {
            $invalid++;
        } elseif ($result === 'saved') {
            $saved++;
        }
    }

    $returnParams = [
        'gibbonAssignmentID' => $gibbonAssignmentID,
        'return' => $invalid > 0 ? 'warning1' : 'success0',
    ];
    if (($_POST['show'] ?? '') === 'ungraded') {
        $returnParams['show'] = 'ungraded';
    }
    header('Location: '.$redirect->withQueryParams($returnParams));
    exit;
}

if ($gibbonAssignmentSubmissionID <= 0) {
    header('Location: '.$redirect->withQueryParam('return', 'error1'));
    exit;
}

$pointsRaw = trim((string) ($_POST['pointsEarned'] ?? ''));
if ($pointsRaw !== '' && !is_numeric($pointsRaw)) {
    header('Location: '.$redirect->withQueryParam('return', 'error1'));
    exit;
}

try {
    $service->gradeAssignment(
        $staffID,
        $gibbonAssignmentSubmissionID,
        trim((string)($_POST['grade'] ?? '')),
        $pointsRaw === '' ? null : round((float) $pointsRaw, 2),
        trim((string)($_POST['feedback'] ?? ''))
    );
} catch (Exception $e) {
    header('Location: '.$redirect->withQueryParam('return', 'error2'));
    exit;
}

header('Location: '.$redirect->withQueryParam('return', 'success0'));
