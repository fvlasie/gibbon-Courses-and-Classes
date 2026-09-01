<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;

include '../../gibbon.php';
require_once __DIR__.'/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/assignment_add.php') == false) {
    echo "<div class='warning'>".__('You do not have access to this action.')."</div>";
    exit;
}

$gibbonAssignmentID = (int)($_GET['gibbonAssignmentID'] ?? 0);
$gibbonCourseID = (int)($_GET['gibbonCourseID'] ?? 0);
$gibbonCourseClassID = (int)($_GET['gibbonCourseClassID'] ?? 0);

if ($gibbonAssignmentID <= 0 || $gibbonCourseID <= 0) {
    echo "<div class='warning'>".__('Invalid assignment ID.')."</div>";
    exit;
}

$connection = $container->get(Connection::class);
$gateway = new AssignmentGateway($connection);
$assignment = $gateway->getAssignmentByID($gibbonAssignmentID);

if (empty($assignment)) {
    echo "<div class='warning'>".__('Assignment not found.')."</div>";
    exit;
}

$gateway->deleteAssignment($gibbonAssignmentID);

$courseName = (string)$pdo->selectOne(
    'SELECT name FROM gibbonCourse WHERE gibbonCourseID = :gibbonCourseID',
    ['gibbonCourseID' => $gibbonCourseID]
);

include 'assignment_manage.php';
