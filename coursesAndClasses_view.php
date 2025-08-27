<?php

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Domain\Planner\PlannerEntryGateway;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Tables\Action;
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Module\CoursesAndClasses\Tables\CourseOverviewTable;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;

//Module includes
require_once 'moduleFunctions.php';

global $container;
$courseID = $_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? null;
$courses = [];
$coursesArray = [];
$moduleName = $session->get('module');
$personID = $session->get('gibbonPersonID');

$page->breadcrumbs
    ->add($moduleName)->add('Overview');

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/coursesAndClasses_view.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));

} else {
        $classMap = [];
        $connection = $container->get(Connection::class);
        $gateway = new CourseGateway($connection);        
        $criteria = new QueryCriteria();
        //$criteria->fromArray($_GET);
        $coursesArray = $gateway->queryRawCoursesByPerson($personID);
        //$courses = new DataSet($coursesArray);
        $courses = new DataSet($coursesArray);
        //error_log('[DataSet Row Count] ' . count($courses->toArray()));
        if (count($courses) === 0) {
            echo '<p><em>No courses found for this user.</em></p>';
        } else {
            $rawCourses = iterator_to_array($coursesArray);
            $courseIDs = array_column($rawCourses, 'gibbonCourseID');
            $courseNames = array_column($rawCourses, 'courseName');

            $materialsGateway = new CourseMaterialsGateway($connection);
            $resources = $materialsGateway->selectByCourseNames($courseNames);
            $classMap = [];
                foreach ($courseIDs as $courseID) {
                    $classMap[$courseID] = getClassInfoByCourse($connection, $courseID);
                }
            $collapsed = collapseByCourse($courses->toArray(), $resources, $personID, $classMap);
            $data = expandCoursesToRows($collapsed);
            echo "<h2>" . __('📚 My Courses') . "</h2>";

            $groupedCourses = [];

            foreach ($data as $row) {
                $courseKey = $row['courseName'] ?? 'Unknown';
                $groupedCourses[$courseKey][] = $row;
            }

            $formatOverview = function ($row) use ($guid, $connection2) {
                switch ($row['rowType']) {
                    case 'header':
                        return "<p class='course-header'><strong><span style='font-size:3em;vertical-align: -.3em'>🦉</span>{$row['courseNameFull']}</strong> <code>({$row['courseName']})</code></p>";

                    case 'details':
                        if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_edit.php')) {
                             $units = "<div class='material-item'><a href='{$row['units']}'><div class='material-icon'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 640'><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill='#A8ADB8' d='M348 62.7C330.7 52.7 309.3 52.7 292 62.7L207.8 111.3C190.5 121.3 179.8 139.8 179.8 159.8L179.8 261.7L91.5 312.7C74.2 322.7 63.5 341.2 63.5 361.2L63.5 458.5C63.5 478.5 74.2 497 91.5 507L175.8 555.6C193.1 565.6 214.5 565.6 231.8 555.6L320.1 504.6L408.4 555.6C425.7 565.6 447.1 565.6 464.4 555.6L548.5 507C565.8 497 576.5 478.5 576.5 458.5L576.5 361.2C576.5 341.2 565.8 322.7 548.5 312.7L460.2 261.7L460.2 159.8C460.2 139.8 449.5 121.3 432.2 111.3L348 62.7zM296 356.6L296 463.1L207.7 514.1C206.5 514.8 205.1 515.2 203.7 515.2L203.7 409.9L296 356.6zM527.4 357.2C528.1 358.4 528.5 359.8 528.5 361.2L528.5 458.5C528.5 461.4 527 464 524.5 465.4L440.2 514C439 514.7 437.6 515.1 436.2 515.1L436.2 409.8L527.4 357.2zM412.3 159.8L412.3 261.7L320 315L320 208.5L411.2 155.9C411.9 157.1 412.3 158.5 412.3 159.9z'/></svg></div>Units</a></div>"; 
                        } else { $units = ""; }
                        if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_edit.php')) {
                            $outcomes = "<div class='material-item'><a href='{$row['outcomes']}'><div class='material-icon'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 640'><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill='#A8ADB8' d='M197.8 100.3C208.7 107.9 211.3 122.9 203.7 133.7L147.7 213.7C143.6 219.5 137.2 223.2 130.1 223.8C123 224.4 116 222 111 217L71 177C61.7 167.6 61.7 152.4 71 143C80.3 133.6 95.6 133.7 105 143L124.8 162.8L164.4 106.2C172 95.3 187 92.7 197.8 100.3zM197.8 260.3C208.7 267.9 211.3 282.9 203.7 293.7L147.7 373.7C143.6 379.5 137.2 383.2 130.1 383.8C123 384.4 116 382 111 377L71 337C61.6 327.6 61.6 312.4 71 303.1C80.4 293.8 95.6 293.7 104.9 303.1L124.7 322.9L164.3 266.3C171.9 255.4 186.9 252.8 197.7 260.4zM288 160C288 142.3 302.3 128 320 128L544 128C561.7 128 576 142.3 576 160C576 177.7 561.7 192 544 192L320 192C302.3 192 288 177.7 288 160zM288 320C288 302.3 302.3 288 320 288L544 288C561.7 288 576 302.3 576 320C576 337.7 561.7 352 544 352L320 352C302.3 352 288 337.7 288 320zM224 480C224 462.3 238.3 448 256 448L544 448C561.7 448 576 462.3 576 480C576 497.7 561.7 512 544 512L256 512C238.3 512 224 497.7 224 480zM128 440C150.1 440 168 457.9 168 480C168 502.1 150.1 520 128 520C105.9 520 88 502.1 88 480C88 457.9 105.9 440 128 440z'/></svg></div>Outcomes</a></div>";
                        } else { $outcomes = ""; }
                        $rubrics = "<a href='{$row['rubrics']}'><div class='material-icon'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 640'><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill='#A8ADB8' d='M480 160L352 160L352 288L480 288L480 160zM544 288L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160C96 124.7 124.7 96 160 96L480 96C515.3 96 544 124.7 544 160L544 288zM160 352L160 480L288 480L288 352L160 352zM288 288L288 160L160 160L160 288L288 288zM352 352L352 480L480 480L480 352L352 352z'/></svg></div>Rubrics</a>";
                        $classes = array_map(function ($class) {
                            $url = buildURL('department_course_class.php', ['gibbonCourseClassID' => $class['classID']], 'Departments');
                            return "<a href='{$url}'>" . htmlspecialchars($class['fullName']) . "</a>";
                        }, $row['classes'] ?? []);
                        $classLinks = !empty($classes) ? implode(' &nbsp; ', $classes) : '<em>No classes</em>';
                        return "<div class='classes'><strong>Classes:</strong> {$classLinks}</div>
                                <div class='materials-row'>
                                    {$units}
                                    {$outcomes}
                                    <div class='material-item'>{$rubrics}</div>
                                </div>";

                    case 'materialsHeader': 
                        return "<p class='courseMaterials-header'><strong>Course Materials</strong></p>";

                    case 'materials':
                        $files = $row['materials'] ?? [];
                        if (empty($files)) return '<em>No course materials</em>';

                        $items = array_map(function ($file) use ($guid) {
                            $link = getResourceLink($guid, $file['gibbonResourceID'], $file['type'], $file['name'], $file['content']);
                            // Extract href from the anchor tag
                            preg_match("/href='([^']+)'/", $link, $matches);
                            $href = $matches[1] ?? '#';
                            return "<a href='{$href}' class='material-item'>
                                        <div class='material-icon'>
                                            <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 640 640'>
                                                <path fill='#A8ADB8' d='M32 176C32 134.5 63.6 100.4 104 96.4L104 96L384 96C437 96 480 139 480 192L480 368L304 368C264.2 368 232 400.2 232 440L232 500C232 524.3 212.3 544 188 544C163.7 544 144 524.3 144 500L144 272L80 272C53.5 272 32 250.5 32 224L32 176zM268.8 544C275.9 530.9 280 515.9 280 500L280 440C280 426.7 290.7 416 304 416L552 416C565.3 416 576 426.7 576 440L576 464C576 508.2 540.2 544 496 544L268.8 544zM112 144C94.3 144 80 158.3 80 176L80 224L144 224L144 176C144 158.3 129.7 144 112 144z'/>
                                            </svg>
                                        </div>{$file['name']}
                                    </a>";
                        }, $files);

                        return "<div class='materials-row'>" . implode('', $items) . "</div>";
 
                    }
            };

            $formatActions = function ($row, $actions) use ($guid, $connection2) {
                if ($row['rowType'] !== 'materialsHeader') return '';

                if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_edit.php')) {
                    $actions->addAction('edit', __('Edit'), 'Edit Course Materials')
                        ->setURL('/fullscreen.php')
                        ->addParam('q', '/modules/Courses and Classes/materials_edit.php')
                        ->addParam('courseName', $row['courseName'])
                        ->directLink(true)
                        ->modalWindow();
                }
            };

            foreach ($groupedCourses as $courseName => $courseRows) {
                $tableID = 'Course_' . preg_replace('/\W+/', '_', $courseName);
                $table = DataTable::create($tableID);
                $table->setTitle("");

                $table->addColumn('overview', __(''))->format($formatOverview);
                $table->addActionColumn()->setClass('no-header')->format($formatActions);

                $table->withData($courseRows);
                echo $table->render($courseRows);
            }

        };
    };
?>
