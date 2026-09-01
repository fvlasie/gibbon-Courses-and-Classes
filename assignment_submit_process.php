<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\FileUploader;
use Gibbon\Http\Url;
use Gibbon\Module\CoursesAndClasses\Services\AssignmentService;

include '../../gibbon.php';
require_once __DIR__.'/moduleFunctions.php';

$moduleName = getModuleName($_POST['address'] ?? '') ?: 'Courses and Classes';
$gibbonAssignmentID = (int)($_POST['gibbonAssignmentID'] ?? 0);
$redirect = Url::fromModuleRoute($moduleName, 'assignment_view.php')->withQueryParams(['gibbonAssignmentID' => $gibbonAssignmentID]);

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_submit.php') == false
    || !coursesAndClassesCanSubmitAssignment($session)) {
    header('Location: '.$redirect->withQueryParam('return', 'error0'));
    exit;
}

$connection = $container->get(Connection::class);
$gibbonPersonID = (int)($_POST['gibbonPersonID'] ?? $session->get('gibbonPersonID'));
$gibbonSchoolYearID = (int)($_POST['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID'));

if ($gibbonAssignmentID <= 0 || $gibbonPersonID <= 0) {
    header('Location: '.$redirect->withQueryParam('return', 'error1'));
    exit;
}

if (empty($_FILES['file']['tmp_name'])) {
    header('Location: '.$redirect->withQueryParam('return', 'error3'));
    exit;
}

$fileUploader = new FileUploader($pdo, $session);
$attachmentPath = $fileUploader->uploadFromPost($_FILES['file'], 'Assignment'.$gibbonAssignmentID.'-'.$gibbonPersonID);

if (empty($attachmentPath)) {
    header('Location: '.$redirect->withQueryParam('return', 'error3'));
    exit;
}

$submissionData = [
    'gibbonSchoolYearID' => $gibbonSchoolYearID,
    'external_submission_id' => $_FILES['file']['name'] ?? basename($attachmentPath),
    'external_doc_id' => $attachmentPath,
    'storage_provider_type' => 'local',
    'fields' => ['comments' => $_POST['comments'] ?? ''],
];

try {
    (new AssignmentService($connection))->submitAssignment($gibbonPersonID, $gibbonAssignmentID, $submissionData);
} catch (Exception $e) {
    header('Location: '.$redirect->withQueryParam('return', 'error2'));
    exit;
}

header('Location: '.$redirect->withQueryParam('return', 'success0'));
