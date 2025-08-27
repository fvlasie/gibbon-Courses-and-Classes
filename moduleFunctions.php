<?php

use Gibbon\Database\Connection;

require_once __DIR__ . '/src/Domain/CourseGateway.php';
require_once __DIR__ . '/src/Domain/ClassGateway.php';
require_once __DIR__ . '/src/Tables/CourseOverviewTable.php';

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

function buildURL(string $script, array $params = [], ?string $module = null): string {
    global $session;

    $baseURL = $session->get('absoluteURL') . '/index.php';
    $path = '/modules/' . ($module ?? $session->get('module')) . '/' . $script;

    // 'q' becomes the core routing param
    $query = array_merge(['q' => $path], $params);
    $queryString = http_build_query($query);

    return $baseURL . '?' . $queryString;
}

function collapseByCourse(array $rows, array $resources, string $guid, array $classMap): array
{
    $grouped = [];

    foreach ($rows as $row) {
        $code = $row['courseName'] ?? '[Unknown]';
        $className = $row['className'] ?? '[Unassigned]';

        if (!isset($grouped[$code])) {
            $files = $resources[$code] ?? [];
            $uniqueFiles = [];
            foreach ($files as $file) {
                $id = $file['gibbonResourceID'];
                if (!isset($uniqueFiles[$id])) {
                    $uniqueFiles[$id] = $file;
                }
            }

            $grouped[$code] = [
                'courseName' => $code,
                'gibbonCourseID' => $row['gibbonCourseID'],
                'courseNameFull' => $row['courseNameFull'] ?? '[Unknown Name]',
                'materials' => $uniqueFiles,
                'classes' => []
            ];
        }

        if (!array_filter($grouped[$code]['classes'], fn($c) => $c['name'] === $className)) {
            $classInfo = $classMap[$row['gibbonCourseID']][$className] ?? [];

            $grouped[$code]['classes'][] = [
                'name' => $className,
                'fullName' => $code . '.' . $className ?? $className,
                'classID' => $classInfo['id'] ?? null,
            ];

            // Now rebuild the links from the updated list
            $classes = array_map(function ($class) {
                $url = buildURL('class_view.php', ['gibbonCourseClassID' => $class['classID']]);
                return $class['classID']
                    ? "<a href='{$url}'>" . htmlspecialchars($class['fullName']) . "</a>"
                    : htmlspecialchars($class['fullName']);
            }, $grouped[$code]['classes']);

            $grouped[$code]['classLinks'] = implode(', ', $classes);
        }
    }
    return $grouped;
}

function expandCoursesToRows(array $courses): array {
    $rows = [];

    foreach ($courses as $course) {
        $rows[] = [
            'rowType' => 'header',
            'courseNameFull' => $course['courseNameFull'],
            'courseName' => $course['courseName'],
        ];

        $rows[] = [
            'rowType' => 'details',
            'courseName' => $course['courseName'],            
            'units' => "index.php?q=%2Fmodules%2FPlanner%2Funits.php&viewBy=class&gibbonCourseID={$course['gibbonCourseID']}&Go=Go",
            'outcomes' => "index.php?q=%2Fmodules%2FPlanner%2Foutcomes.php&gibbonCourseID={$course['gibbonCourseID']}",
            'rubrics' => "index.php?q=/modules/Rubrics/rubrics_view.php&gibbonCourseID={$course['gibbonCourseID']}",
            'classes' => $course['classes'],
        ];

        $rows[] = [
            'rowType' => 'materialsHeader',
            'courseName' => $course['courseName'],
        ];

        $rows[] = [
            'rowType' => 'materials',
            'courseName' => $course['courseName'],
            'materials' => $course['materials'],
        ];

    }

    return $rows;
}
