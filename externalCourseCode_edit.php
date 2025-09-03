<?php
require_once __DIR__ . '/../../gibbon.php';

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Forms\Form;
use Gibbon\Tables\DataTable;

require_once 'moduleFunctions.php';

$courseID = $_GET['gibbonCourseID'] ?? '';

error_log($courseID);

$sql = "SELECT externalCourseCode FROM gibbonCoursesAndClasses WHERE gibbonCourseID = :gibbonCourseID";
$stmt = $connection2->prepare($sql);
$stmt->execute(['gibbonCourseID' => $courseID]);
$existingCode = $stmt->fetchColumn();

$form = Form::create('editExternalCode', $_SESSION[$guid]['absoluteURL'] . '/modules/Courses and Classes/externalCourseCode_edit.php');

$form->addHiddenValue('gibbonCourseID', $courseID);

$row = $form->addRow();
$row->addLabel('externalCourseCode', __('External Course Code'));
$row->addTextField('externalCourseCode')->setValue($existingCode)->required()->maxLength(64);

$row = $form->addRow();
$row->addSubmit();

if ($_POST['externalCourseCode'] ?? false) {
    $newCode = $_POST['externalCourseCode'];
    $courseID = $_POST['gibbonCourseID'] ?? '';
    $sql = "SELECT COUNT(*) FROM gibbonCoursesAndClasses WHERE gibbonCourseID = :gibbonCourseID";
    $stmt = $connection2->prepare($sql);
    $stmt->execute(['gibbonCourseID' => $courseID]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        $sql = "INSERT INTO gibbonCoursesAndClasses (gibbonCourseID, externalCourseCode)
                VALUES (:gibbonCourseID, :externalCourseCode)";
    } else {
        $sql = "UPDATE gibbonCoursesAndClasses
                SET externalCourseCode = :externalCourseCode
                WHERE gibbonCourseID = :gibbonCourseID";
    }

    $stmt = $connection2->prepare($sql);
    $stmt->execute([
        'externalCourseCode' => $newCode,
        'gibbonCourseID' => $courseID
    ]);

    error_log("Saving externalCourseCode: " . $newCode ." to ".$courseID);
    header('Location: ' . $_SESSION[$guid]['absoluteURL'] . '/index.php?q=/modules/Courses and Classes/coursesAndClasses_view.php');
    exit;
}

echo $form->getOutput();

echo "
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('#editExternalCode');
        if (form) {
            form.addEventListener('submit', function () {
                window.onbeforeunload = null;
            });
        }
    });
</script>
";

?>

