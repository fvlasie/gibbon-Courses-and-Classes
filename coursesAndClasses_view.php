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
            $data = collapseByCourse($courses->toArray(),$resources, $personID, $classMap);

            $table = DataTable::create('CoursesOverview');
            $table->setTitle(__('📘 My Courses'));
            $table->addColumn('courseNameFull', __('Course'));
            $table->addColumn('courseName', __('Code'));
            $table->addColumn('materials', __('Materials'))
                ->format(function ($row) use ($guid) {
                    $files = $row['materials'] ?? [];

                    if (empty($files)) return '<em>No materials</em>';

                    return implode('<br>', array_map(function ($file) use ($guid) {
                        $link = getResourceLink($guid, $file['gibbonResourceID'], $file['type'], $file['name'], $file['content']);
                        $staff = htmlspecialchars("{$file['title']} {$file['preferredName']} {$file['surname']}");
                        return $link . " <small>($staff)</small>";
                    }, $files));
                });

            $table->addActionColumn()
                ->setClass('no-header')
                ->format(function ($row, $actions) use ($guid, $connection2) {
                    if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_edit.php')) {
                        $actions->addAction('edit', __('Edit'), 'Edit Course Materials')
                        ->setURL('/fullscreen.php')
                        ->addParam('q', '/modules/Courses and Classes/materials_edit.php')
                        ->addParam('courseName', $row['courseName'])
                        ->directLink(true)
                        ->modalWindow();
                    }
                });

            $table->addColumn('spacer', __(''));

            $table->addColumn('classes', __('Classes'))->format(function ($row) {
                $classes = $row['classes'] ?? [];

                if (empty($classes)) return '<em>No classes</em>';

                $links = array_map(function ($class) {
                    $url = buildURL('department_course_class.php', ['gibbonCourseClassID' => $class['classID']], 'Departments');
                    return $class['classID']
                        ? "<a href='{$url}'>" . htmlspecialchars($class['fullName']) . "</a>"
                        : htmlspecialchars($class['fullName']);
                }, $classes);
                return implode(', ', $links);
            });

            $table->withData($data);
            echo $table->render($data);

        }
}
?>