<?php

use Gibbon\Domain\DataSet;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\Planner\Tables\LessonTable;
use Gibbon\Domain\Planner\PlannerEntryGateway;
use Gibbon\Domain\Students\StudentGateway;
use Gibbon\Module\CoursesAndClasses\Tables\CourseOverviewTable;
use Gibbon\Contracts\Database\Connection;

//Module includes
require_once 'moduleFunctions.php';

global $container;
$courseID = $_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? null;
$courses = [];
$moduleName = $session->get('module');
$personID = $session->get('gibbonPersonID');

$page->breadcrumbs
    ->add($moduleName)->add('Overview');

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/coursesAndClasses_view.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));

} else {
        //echo "<h2>Hello world from Coursewide Settings!</h2>";
        error_log('[Memory Usage Pre-query] ' . memory_get_usage());

        $connection = $container->get(Connection::class);
        $gateway = new CourseGateway($connection);        
        $criteria = new QueryCriteria();
        $coursesResult = $gateway->queryCoursesByPerson($criteria, $personID);
        //error_log('[Row Count] ' . count($coursesResult->toArray()));
error_log('[Final Check] Gateway returned ' . get_class($courses));
exit('Gateway passed. Rendering disabled for now.');

        $result = $gateway->queryCoursesByPerson($criteria, $personID);
        //error_log('[Row Count] ' . count($courses->toArray()));
        //error_log('Queried row count: ' . count($courses->toArray()));

        $table = new CourseOverviewTable($criteria, 'courseOverview', $courses);

        echo $table->render([
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'cookie' => $_COOKIE,
            'server' => $_SERVER,
        ]);
}