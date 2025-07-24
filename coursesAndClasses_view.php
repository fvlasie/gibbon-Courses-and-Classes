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
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;
use Gibbon\Module\CoursesAndClasses\Tables\CourseOverviewTable;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;

//Module includes
require_once 'moduleFunctions.php';
//require_once __DIR__ . '/../../src/Tables/DataTable.php';

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
        $connection = $container->get(Connection::class);
        $gateway = new CourseGateway($connection);        
        $criteria = new QueryCriteria();
        //$criteria->fromArray($_GET);
        $coursesArray = $gateway->queryRawCoursesByPerson($personID);
        //$courses = new DataSet($coursesArray);
        $courses = new DataSet($coursesArray);
        //error_log('[DataSet Row Count] ' . count($courses->toArray()));
        $table = new CourseOverviewTable($criteria, 'courseOverview', $courses, $connection, $guid);
        if (count($courses) === 0) {
            echo '<p><em>No courses found for this user.</em></p>';
        } else {
            //echo '<pre>'; print_r($courses); echo '</pre>';
            echo '<div style="padding:2em;">' .  $table->render($courses). '</div>'; 
    }
}