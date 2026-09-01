<?php

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;

require_once __DIR__.'/moduleFunctions.php';
checkAndMigrateCoursesAndClassesSchema($pdo);

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/courses_manage.php') == false) {
    $page->addError(__('You do not have access to this action.'));
} else {
    $gibbonSchoolYearID = $_GET['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID');
    $courseGateway = $container->get(CourseGateway::class);
    $courseOptions = $courseGateway->getCourseSelectBySchoolYear((int)$gibbonSchoolYearID);

    echo '<p>'.__('Choose one course to open on Overview. Catalog codes and credits are stored by course code and apply across years.').'</p>';

    $yearForm = Form::create('otherCourseYear', $session->get('absoluteURL').'/fullscreen.php', 'get');
    $yearForm->setFactory(DatabaseFormFactory::create($pdo));
    $yearForm->addHiddenValue('q', '/modules/'.$session->get('module').'/courses_manage.php');
    $yearForm->addHiddenValue('width', '720');
    $yearForm->addHiddenValue('height', '360');

    $row = $yearForm->addRow();
        $row->addLabel('gibbonSchoolYearID', __('School Year'));
        $row->addSelectSchoolYear('gibbonSchoolYearID')->required()->selected($gibbonSchoolYearID);

    $row = $yearForm->addRow();
        $row->addSearchSubmit($session, __('Clear Filters'));

    echo $yearForm->getOutput();

    $form = Form::create('otherCourse', $session->get('absoluteURL').'/index.php', 'get');
    $form->addHiddenValue('q', '/modules/'.$session->get('module').'/coursesAndClasses_view.php');
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);

    $row = $form->addRow();
        $row->addLabel('gibbonCourseID', __('Course'));
        $row->addSelect('gibbonCourseID')->fromArray($courseOptions)->required()->placeholder();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit(__('Open'));

    echo $form->getOutput();
}
