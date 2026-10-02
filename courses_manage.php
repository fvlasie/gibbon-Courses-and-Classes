<?php

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\DataSet;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;

require_once __DIR__.'/moduleFunctions.php';
checkAndMigrateCoursesAndClassesSchema($pdo);

$page->breadcrumbs->add(__('Manage All Courses'));

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/courses_manage.php') == false) {
    $page->addError(__('You do not have access to this action.'));
    return;
}

$gibbonSchoolYearID = (int)($_GET['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID'));
$courseGateway = $container->get(CourseGateway::class);
$moduleURL = $session->get('absoluteURL').'/modules/'.$session->get('module');

$form = Form::create('courseYearFilter', $session->get('absoluteURL').'/index.php', 'get');
$form->setFactory(DatabaseFormFactory::create($pdo));
$form->setClass('noIntBorder w-full');
$form->addHiddenValue('q', '/modules/'.$session->get('module').'/courses_manage.php');

$row = $form->addRow();
    $row->addLabel('gibbonSchoolYearID', __('School Year'));
    $row->addSelectSchoolYear('gibbonSchoolYearID')->required()->selected($gibbonSchoolYearID);

$row = $form->addRow();
    $row->addSearchSubmit($session);

echo $form->getOutput();

$saveVals = function (array $row, string $field) use ($session) {
    return htmlspecialchars(json_encode([
        'csrftoken' => $session->get('csrftoken'),
        'gibbonCourseID' => (int)$row['gibbonCourseID'],
        'field' => $field,
    ]), ENT_QUOTES);
};
$saveAttributes = function (array $row, string $field) use ($moduleURL, $saveVals) {
    return ' name="value" hx-post="'.htmlspecialchars($moduleURL.'/courses_manageAjax.php').'" hx-trigger="change" hx-swap="none"'
        .' hx-vals="'.$saveVals($row, $field).'"';
};

$table = DataTable::create('courseCatalog');
$table->setTitle(__('Course Catalog'));
$table->setDescription(__('External codes and credits are stored by course code, so a change applies to that course in every school year, including transcripts and tuition billing. Changes save when you leave the field.'));

$table->addColumn('courseCode', __('Course Code'));
$table->addColumn('courseName', __('Course Name'));
$table->addColumn('classCount', __('Classes'))->addClass('text-right');
$table->addColumn('externalCourseCode', __('External Course Code'))
    ->format(function ($row) use ($saveAttributes) {
        return '<input type="text" maxlength="255" class="w-40" aria-label="'.__('External Course Code').'"'
            .' value="'.htmlspecialchars((string)($row['externalCourseCode'] ?? '')).'"'.$saveAttributes($row, 'externalCourseCode').'>';
    });
$table->addColumn('credits', __('Credits'))
    ->format(function ($row) use ($saveAttributes) {
        $credits = number_format((float)($row['credits'] ?? 3), 2, '.', '');

        return '<input type="number" step="0.01" min="0" max="99.99" class="w-20 text-right" aria-label="'.__('Credits').'"'
            .' value="'.$credits.'"'.$saveAttributes($row, 'credits').'>';
    })
    ->addClass('text-right');
$table->addActionColumn()
    ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
    ->addParam('gibbonCourseID')
    ->format(function ($row, $actions) {
        $actions->addAction('view', __('Open'))
            ->setURL('/modules/Courses and Classes/coursesAndClasses_view.php');
    });

echo '<div class="courseCatalogEditable">';
echo $table->render(new DataSet($courseGateway->selectCourseCatalogBySchoolYear($gibbonSchoolYearID)));
echo '</div>';
?>
<script>
(function () {
    if (window.courseCatalogInlineEdit) return;
    window.courseCatalogInlineEdit = true;

    var flash = function (el, colour) {
        el.style.outline = '2px solid ' + colour;
        setTimeout(function () { el.style.outline = ''; }, 1200);
    };

    document.body.addEventListener('htmx:afterRequest', function (evt) {
        var el = evt.detail.elt;
        if (!el || !el.closest || !el.closest('.courseCatalogEditable')) return;

        if (evt.detail.successful) {
            el.defaultValue = el.value;
            flash(el, '#16a34a');
        } else {
            window.alert(evt.detail.xhr.responseText || 'The change could not be saved.');
            el.value = el.defaultValue;
            flash(el, '#dc2626');
        }
    });
})();
</script>
