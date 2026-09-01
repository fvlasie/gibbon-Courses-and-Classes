<?php
use Gibbon\Contracts\Database\Connection;

// PSR-4 Autoloader for module classes under src/
spl_autoload_register(function ($class) {


    $prefix = 'Gibbon\\Module\\CoursesAndClasses\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

function coursesAndClassesCanSubmitAssignment($session): bool
{
    return $session->get('gibbonRoleIDCurrentCategory') === 'Student';
}

function checkAndMigrateCoursesAndClassesSchema($pdo)
{
    try {
        $table = $pdo->selectOne("SHOW TABLES LIKE 'gibbonCoursesAndClasses'");
        if (empty($table)) {
            return;
        }
    } catch (Exception $e) {
        return;
    }

    $columns = [];
    try {
        $columnRows = $pdo->select("SHOW COLUMNS FROM `gibbonCoursesAndClasses`")->fetchAll();
        foreach ($columnRows as $column) {
            $columns[$column['Field']] = true;
        }
    } catch (Exception $e) {
        return;
    }

    if (empty($columns['courseCode'])) {
        $pdo->statement("ALTER TABLE `gibbonCoursesAndClasses` ADD COLUMN `courseCode` VARCHAR(60) DEFAULT NULL AFTER `gibbonCourseID`");
    }

    if (empty($columns['credits'])) {
        $after = !empty($columns['externalCourseCode']) ? ' AFTER `externalCourseCode`' : '';
        $pdo->statement("ALTER TABLE `gibbonCoursesAndClasses` ADD COLUMN `credits` DECIMAL(4,2) NOT NULL DEFAULT 0.00{$after}");
    }

    $pdo->statement("UPDATE gibbonCoursesAndClasses AS cac
        INNER JOIN gibbonCourse AS c ON c.gibbonCourseID = cac.gibbonCourseID
        SET cac.courseCode = c.nameShort
        WHERE cac.courseCode IS NULL OR cac.courseCode = ''");

    $indexes = [];
    $foreignKeys = [];
    try {
        $indexRows = $pdo->select("SHOW INDEX FROM `gibbonCoursesAndClasses`")->fetchAll();
        foreach ($indexRows as $index) {
            $indexes[$index['Key_name']] = true;
        }
    } catch (Exception $e) {
        $indexes = [];
    }

    try {
        $fkRows = $pdo->select("SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'gibbonCoursesAndClasses'
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'")->fetchAll();
        foreach ($fkRows as $fk) {
            $foreignKeys[$fk['CONSTRAINT_NAME']] = true;
        }
    } catch (Exception $e) {
        $foreignKeys = [];
    }

    if (!empty($foreignKeys['gibbonCoursesAndClasses_ibfk_1'])) {
        $pdo->statement("ALTER TABLE `gibbonCoursesAndClasses` DROP FOREIGN KEY `gibbonCoursesAndClasses_ibfk_1`");
    }

    $pdo->statement("DELETE cac FROM gibbonCoursesAndClasses cac
        INNER JOIN gibbonCoursesAndClasses keep
            ON keep.courseCode = cac.courseCode
            AND keep.gibbonCoursesAndClassesID > cac.gibbonCoursesAndClassesID
        WHERE cac.courseCode IS NOT NULL AND cac.courseCode <> ''");

    if (empty($indexes['courseCode'])) {
        $pdo->statement("ALTER TABLE `gibbonCoursesAndClasses` ADD UNIQUE KEY `courseCode` (`courseCode`)");
    }
}


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

function getResourceLink($guid, $gibbonResourceID, $type, $name, $content) {
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
    $moduleName = $module ?? $session->get('module') ?? 'Courses and Classes';
    $path = '/modules/' . $moduleName . '/' . $script;

    // 'q' becomes the core routing param
    $query = array_merge(['q' => $path], $params);
    $queryString = http_build_query($query);

    return $baseURL . '?' . $queryString;
}

function collapseByCourse(array $rows, array $resources, string $guid, array $classMap, array $assignmentsMap = []): array {
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
                'externalCourseCode' => $row['externalCourseCode'] ?? '',
                'credits' => $row['credits'] ?? 0,
                'materials' => $uniqueFiles,
                'assignments' => $assignmentsMap[$row['gibbonCourseID']] ?? [],
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
            'rowType' => 'externalCode',
            'courseName' => $course['courseName'],
            'externalCourseCode' => $course['externalCourseCode'] ?? '',
            'credits' => $course['credits'] ?? 0,
            'gibbonCourseID' => $course['gibbonCourseID'],
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
            'rowType' => 'assignmentsHeader',
            'courseName' => $course['courseName'],
            'gibbonCourseID' => $course['gibbonCourseID'],
            'classes' => $course['classes'],
        ];

        $rows[] = [
            'rowType' => 'assignments',
            'courseName' => $course['courseName'],
            'gibbonCourseID' => $course['gibbonCourseID'],
            'assignments' => $course['assignments'] ?? [],
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

