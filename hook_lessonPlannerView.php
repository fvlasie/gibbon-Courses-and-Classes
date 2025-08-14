<?php
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Domain\System\CustomFieldGateway;
use Gibbon\Contracts\Database\Connection;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;

global $session, $container, $page;

require_once __DIR__ . '/../Courses and Classes/src/Domain/CourseMaterialsGateway.php';

// Debug marker: file was included
//error_log('hook_lessonPlannerView included');

// Get DB connection
$connection = $container->get(Connection::class);

// Get the current lesson data
$lesson = isset($values['course']) ? [
    'courseName' => $values['course'],
    'guid' => $values['gibbonPlannerEntryID'] ?? '',
] : [];

// Safety check
if (empty($lesson['courseName']) || empty($lesson['guid'])) {
    echo "<em>No course context available</em>";
    return;
}

// Load resources
$gateway = new CourseMaterialsGateway($connection);
$resourcesMap = $gateway->selectByCourseNames([$lesson['courseName']]);
$files = $resourcesMap[$lesson['courseName']] ?? [];

// Dedupe by ID
$unique = [];
foreach ($files as $file) {
    $id = $file['gibbonResourceID'];
    if (!isset($unique[$id])) {
        $unique[$id] = $file;
    }
}

// Render materials
echo "<h2>".__('Course Materials').'</h2>';
echo "<table class='smallIntBorder' cellspacing='0' style='width: 100%;'>";
echo '<tr>';
echo "<td class='bg-gray-100' style='text-align: justify; padding-top: 5px; width: 100%; vertical-align: top; max-width: 752px!important; height: 15px;' colspan=3>";
echo "<div class='p-2 -mb-px col-span-3'>";

$cm_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="24" height="24" style="vertical-align: middle; margin-right: 6px; fill: currentColor;" aria-hidden="true">
  <path d="M32 176C32 134.5 63.6 100.4 104 96.4L104 96L384 96C437 96 480 139 480 192L480 368L304 368C264.2 368 232 400.2 232 440L232 500C232 524.3 212.3 544 188 544C163.7 544 144 524.3 144 500L144 272L80 272C53.5 272 32 250.5 32 224L32 176zM268.8 544C275.9 530.9 280 515.9 280 500L280 440C280 426.7 290.7 416 304 416L552 416C565.3 416 576 426.7 576 440L576 464C576 508.2 540.2 544 496 544L268.8 544zM112 144C94.3 144 80 158.3 80 176L80 224L144 224L144 176C144 158.3 129.7 144 112 144z"/>
</svg>';


if (empty($unique)) {
    echo "<em>".__('No materials found for this course.')."</em>";
} else {
    foreach ($unique as $file) {
        $link = getResourceLink(
            $lesson['guid'],
            $file['gibbonResourceID'],
            $file['type'],
            $file['name'],
            $file['content']
        );

        echo "<div class='lesson-resource'>{$cm_icon}$link</div>";
    }
}

echo "</div>";
echo '</td>';
echo '</tr>';
echo '</table>';
