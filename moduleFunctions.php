<?php

use Gibbon\Module;
use Gibbon\Tables\DataGateway;
use Gibbon\Module\CoursesAndClasses\Domain\ClassGateway;
use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;

//echo "ModuleFunctions loaded!";

require_once __DIR__ .'/Domain/CourseGateway.php';

function renderDebugPanel($session, $variables = [])
{
    if (!$session->get('gibbonPersonID') || $session->get('mode') !== 'Development') {
        return;
    }

    echo '<div style="background:#f9f9f9;border:1px solid #ccc;padding:1em;margin:1em 0;font-family:monospace;">';
    echo '<h3 style="margin-top:0;">Debug Panel</h3>';

    echo '<strong>Session Info:</strong><br>';
    echo 'User ID: ' . $session->get('gibbonPersonID') . '<br>';
    echo 'Role: ' . $session->get('gibbonRoleIDCurrent') . '<br>';
    echo 'Module: ' . $session->get('module') . '<br>';
    echo 'Page: ' . $session->get('page') . '<br>';
    echo 'Course ID: ' . ($session->get('courseID') ?? 'N/A') . '<br>';
    echo 'Class ID: ' . ($session->get('classID') ?? 'N/A') . '<br>';

    if (!empty($variables)) {
        echo '<br><strong>Local Variables:</strong><br>';
        foreach ($variables as $key => $value) {
            $debug = print_r($value, true);
            if (strlen($debug) > 5000) {
                $debug = substr($debug, 0, 5000) . '... [truncated]';
            }
            echo $key . ': ' . htmlspecialchars($debug) . '<br>';
        }
    }


    echo '</div>';
}
