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

return [
    ClassGateway::class => fn($container) => new ClassGateway($container->get('pdo')),
    CourseGateway::class => fn($container) => new CourseGateway($container->get('pdo')),
];

//Module includes
require_once __DIR__ . '/moduleFunctions.php';
  
$moduleName = $session->get('module');

$page->breadcrumbs
    ->add($moduleName)->add('Overview');

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/coursesAndClasses_view.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));

} else {
        echo "<h2>Hello world from Coursewide Settings!</h2>";
        $courseID = $_GET['gibbonCourseID'] ?? $_POST['gibbonCourseID'] ?? null;
        $courseGateway = $container->get(CourseGateway::class);
        $criteria = new QueryCriteria();
        $courses = $courseGateway->queryLimited();

        $classGateway = $container->get(ClassGateway::class);

        $criteria = $classGateway->newQueryCriteria()
            ->searchBy($classGateway->getSearchableColumns(), $_POST['search'] ?? '')
            ->sortBy(['name'])
            ->fromPOST();

        $classes = $classGateway->queryClassesByCourse($criteria, $courseID ?? 0);

        $table = DataTable::createPaginated('classList', $criteria);
        $table->addColumn('name', __('Class Name'))->sortable(['name']);
        $table->addColumn('code', __('Code'))->sortable(['code']);
        $table->addColumn('description', __('Description'));

        echo $table->render($classes);
}

renderDebugPanel($session, [
    'courseList' => $courses,
    'activeCourseID' => $courseID ?? null,
]);
