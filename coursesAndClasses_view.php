<?php

use Gibbon\Domain\DataSet;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;
use Gibbon\Module\CoursesAndClasses\Domain\renderDebugPanel;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
//require_once __DIR__.'/Domain/CourseGateway.php';
  
$moduleName = $session->get('module');
$courseID = $_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? null;

$page->breadcrumbs
    ->add($moduleName)->add('Overview');

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/coursesAndClasses_view.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));

} else {
        //echo "<h2>Hello world from Coursewide Settings!</h2>";
        $courseGateway = $container->get(CourseGateway::class);
        $criteria = new QueryCriteria();
        $personID = $session->get('gibbonPersonID');
        $courses = $courseGateway->getCoursesForUser($personID);

        echo '<h3>Your Courses</h3>';
        echo '<ul>';

        foreach ($courses as $course) {
            echo '<li>' . htmlspecialchars($course['courseName'] . ' — ' . $course['className']) . '</li>';
        }

        echo '</ul>';
}

/* renderDebugPanel($session, [
    'courseList' => count($courses),
    'activeCourseID' => $courseID,
]);
 */