<?php

use Gibbon\Database\Connection;

require_once __DIR__ . '/src/Domain/CourseGateway.php';
require_once __DIR__ . '/src/Domain/ClassGateway.php';
require_once __DIR__ . '/src/Tables/CourseOverviewTable.php';

/* function getClassesByCourse(Connection $connection, $courseID): array {
    $sql = "SELECT * FROM class WHERE courseID = :courseID ORDER BY name";
    $params = ['courseID' => $courseID];
error_log('SQL Query: ' . $sql);
    $result = $connection->executeQuery($sql, $params);
    return $result->fetchAll();
} */

function getClassInfoByCourse(Connection $connection, $courseID): array {
    $sql = "SELECT gibbonCourseClassID, name AS classNameFull FROM gibbonCourseClass WHERE gibbonCourseID = :courseID ORDER BY name";
    $params = ['courseID' => $courseID];
    $result = $connection->executeQuery($params, $sql);
    $rows = $result->fetchAll();

    $classes = [];
    foreach ($rows as $row) {
        $classes[$row['classNameFull']] = [
            'id' => $row['gibbonCourseClassID'],
            'name' => $row['classNameFull']
        ];
    }

    return $classes;
}


function getResourceLink($guid, $gibbonResourceID, $type, $name, $content)
{
    global $session;

    $output = false;

    if ($type == 'Link') {
        $output = "<a target='_blank' style='font-weight: bold' href='".$content."'>".$name.'</a><br/>';
    } elseif ($type == 'File') {
        $output = "<a target='_blank' style='font-weight: bold' href='".$session->get('absoluteURL').'/'.$content."'>".$name.'</a><br/>';
    } elseif ($type == 'HTML') {
        $output = "<a style='font-weight: bold' class='thickbox' href='".$session->get('absoluteURL').'/fullscreen.php?q=/modules/Planner/resources_view_full.php&gibbonResourceID='.$gibbonResourceID."&width=1000&height=550'>".$name.'</a><br/>';
    }

    return $output;
}
