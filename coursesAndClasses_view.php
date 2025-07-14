<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/
use Gibbon\Domain\DataSet;
use Gibbon\Domain\School\FacilityGateway;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\User\UserGateway;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Module\CoursesAndClasses\Domain\renderDebugPanel;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;

//Module includes
require_once __DIR__ . '/moduleFunctions.php';

$moduleName = $session->get('module');
$courseID = $_GET['courseID'] ?? $_POST['courseID'] ?? null;

$page->breadcrumbs
    ->add($moduleName)
    ->add('Courses & Classes');

if (isActionAccessible($guid, $connection2, '/modules/Courses & Classes/coursesAndClasses_view.php') == false) {
    //Acess denied
    $page->addError(__m('You do not have access to this action.'));

} else {

        echo "<h2>Hello world from Coursewide Settings!</h2>";

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
