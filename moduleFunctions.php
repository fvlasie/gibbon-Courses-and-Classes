<?php

//echo "ModuleFunctions loaded!";

require_once __DIR__ . '/src/Domain/CourseGateway.php';
require_once __DIR__ . '/src/Domain/ClassGateway.php';
require_once __DIR__ . '/src/Tables/CourseOverviewTable.php';

/* function getCoursesForUser($connection2, string $personID): array {
    $query = "SELECT c.gibbonCourseID, c.name AS courseNameFull, c.nameShort AS courseName, cc.nameShort AS className
              FROM gibbonCourseClassPerson AS p
              JOIN gibbonCourseClass AS cc ON cc.gibbonCourseClassID = p.gibbonCourseClassID
              JOIN gibbonCourse AS c ON c.gibbonCourseID = cc.gibbonCourseID
              WHERE p.gibbonPersonID = :personID
              ORDER BY c.nameShort, cc.nameShort";

    $stmt = $connection2->prepare($query);
    $stmt->bindValue(':personID', $personID);
    $stmt->execute();

    return $stmt->fetchAll();
} */

function getClassesByCourse($connection2, $courseID): array {
    $query = "SELECT * FROM class WHERE courseID = :courseID ORDER BY name";
    $stmt = $connection2->prepare($query);
    $stmt->bindValue(':courseID', $courseID);
    $stmt->execute();

    return $stmt->fetchAll();
}
