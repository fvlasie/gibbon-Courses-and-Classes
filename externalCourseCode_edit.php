<?php

use Gibbon\Forms\Form;

if (!isset($container)) {
    require_once __DIR__ . '/../../gibbon.php';
}

require_once 'moduleFunctions.php';
checkAndMigrateCoursesAndClassesSchema($pdo);

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/externalCourseCode_edit.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$courseID = (int)($_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? 0);

$courseSql = "SELECT gibbonCourseID, name, nameShort FROM gibbonCourse WHERE gibbonCourseID = :gibbonCourseID";
$courseStmt = $connection2->prepare($courseSql);
$courseStmt->execute(['gibbonCourseID' => $courseID]);
$course = $courseStmt->fetch(PDO::FETCH_ASSOC);

if (empty($course)) {
    $page->addError(__('The specified record cannot be found.'));
    return;
}

$courseCode = $course['nameShort'];

$catalogSql = "SELECT externalCourseCode, credits
               FROM gibbonCoursesAndClasses
               WHERE courseCode = :courseCode
               LIMIT 1";
$catalogStmt = $connection2->prepare($catalogSql);
$catalogStmt->execute(['courseCode' => $courseCode]);
$catalog = $catalogStmt->fetch(PDO::FETCH_ASSOC) ?: [];

if (empty($catalog)) {
    $legacySql = "SELECT externalCourseCode, credits
                  FROM gibbonCoursesAndClasses
                  WHERE gibbonCourseID = :gibbonCourseID
                  LIMIT 1";
    $legacyStmt = $connection2->prepare($legacySql);
    $legacyStmt->execute(['gibbonCourseID' => $courseID]);
    $catalog = $legacyStmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

$form = Form::create('editCourseCatalog', $session->get('absoluteURL').'/modules/Courses and Classes/externalCourseCode_edit.php');
$form->addHiddenValue('gibbonCourseID', $courseID);
$form->addHiddenValue('courseCode', $courseCode);

$row = $form->addRow();
    $row->addLabel('courseCodeDisplay', __('Course Code'));
    $row->addTextField('courseCodeDisplay')->readonly()->setValue($courseCode);

$row = $form->addRow();
    $row->addLabel('courseNameDisplay', __('Course'));
    $row->addTextField('courseNameDisplay')->readonly()->setValue($course['name']);

$row = $form->addRow();
    $row->addLabel('externalCourseCode', __('External Course Code'))->description(__('Saved by course code so it carries into later school years.'));
    $row->addTextField('externalCourseCode')->setValue($catalog['externalCourseCode'] ?? '')->maxLength(64);

$row = $form->addRow();
    $row->addLabel('credits', __('Credits'))->description(__('Credit hours used on transcripts. Saved by course code so it carries into later school years.'));
    $row->addNumber('credits')->decimalPlaces(2)->minimum(0)->maximum(99.99)->setValue($catalog['credits'] ?? '0.00')->required();

$row = $form->addRow();
    $row->addSubmit();

if (!empty($_POST['gibbonCourseID'])) {
    $newCode = trim((string)($_POST['externalCourseCode'] ?? ''));
    $credits = round((float)($_POST['credits'] ?? 0), 2);
    $postedCourseCode = trim((string)($_POST['courseCode'] ?? $courseCode));

    $sql = "INSERT INTO gibbonCoursesAndClasses (gibbonCourseID, courseCode, externalCourseCode, credits)
            VALUES (:gibbonCourseID, :courseCode, :externalCourseCode, :credits)
            ON DUPLICATE KEY UPDATE
                gibbonCourseID = VALUES(gibbonCourseID),
                externalCourseCode = VALUES(externalCourseCode),
                credits = VALUES(credits)";
    $stmt = $connection2->prepare($sql);
    $stmt->execute([
        'gibbonCourseID' => $courseID,
        'courseCode' => $postedCourseCode,
        'externalCourseCode' => $newCode !== '' ? $newCode : null,
        'credits' => $credits,
    ]);

    header('Location: '.$session->get('absoluteURL').'/index.php?q=/modules/Courses and Classes/coursesAndClasses_view.php');
    exit;
}

echo $form->getOutput();
