<?php

use Gibbon\Data\Validator;
use Gibbon\Session\TokenHandler;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;

require_once '../../gibbon.php';
require_once __DIR__.'/moduleFunctions.php';

$_POST = $container->get(Validator::class)->sanitize($_POST);

$fail = function (string $message, int $status = 422) {
    http_response_code($status);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $fail(__('Your request failed because you do not have access to this action.'), 405);
}

// Not a *Process.php file, so gibbon.php does not check the CSRF token for us.
if (!$container->get(TokenHandler::class)->validateCsrfToken()) {
    $fail(__('Your session has expired. Reload the page and try again.'), 403);
}

if (isActionAccessible($guid, $connection2, '/modules/Courses and Classes/courses_manage.php') == false) {
    $fail(__('Your request failed because you do not have access to this action.'), 403);
}

$field = $_POST['field'] ?? '';
if (!in_array($field, ['externalCourseCode', 'credits'], true)) {
    $fail(__('Unknown field.'));
}

$courseGateway = $container->get(CourseGateway::class);
$gibbonCourseID = (int)($_POST['gibbonCourseID'] ?? 0);
$course = $gibbonCourseID > 0 ? $courseGateway->getByID($gibbonCourseID) : null;
if (empty($course['nameShort'])) {
    $fail(__('The specified record cannot be found.'));
}

try {
    $courseGateway->upsertCourseCatalog($course['nameShort'], [$field => $_POST['value'] ?? ''], $gibbonCourseID);
} catch (\InvalidArgumentException $e) {
    $fail($e->getMessage());
}

http_response_code(204);
