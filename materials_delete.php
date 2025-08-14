<?php
require_once __DIR__ . '/../../gibbon.php';
require_once 'moduleFunctions.php';

use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Contracts\Database\Connection;

// Access check
if (!isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_delete.php')) {
    echo "<div class='warning'>".__('You do not have access to this action.')."</div>";
    exit;
}

// Validate input
$materialID = $_GET['materialID'] ?? null;
$courseName = $_GET['courseName'] ?? '';

if (empty($materialID)) {
    echo "<div class='warning'>".__('Invalid material ID.')."</div>";
    exit;
}

// Get gateway
$connection = $container->get(Connection::class);
$gateway = new CourseMaterialsGateway($connection);

// Fetch material to confirm existence and get file path
$material = $gateway->getResourceByID($materialID);
error_log(print_r($material, true));

if ($material === false) {
    echo "<div class='warning'>".__('Material not found.')."</div>";
    exit;
}

// Optional: check ownership or scope if needed
// $material['gibbonPersonID'] === $session->get('gibbonPersonID')

// Delete file if it exists
$baseDir = realpath(__DIR__ . '/../../uploads/');
$absolutePath = realpath(__DIR__ . '/../../' . $material['content']);

if (
    $absolutePath &&
    strpos($absolutePath, $baseDir) === 0 &&
    file_exists($absolutePath)
) {
    unlink($absolutePath);
}

// Delete from DB
try {
    $deleted = $gateway->deleteResource($materialID);
} catch (Exception $e) {
    error_log('Delete error: ' . $e->getMessage());
    echo "<div class='error'>".__('Delete exception: ').$e->getMessage()."</div>";
    exit;
}

if (!$deleted) {
    echo "<div class='error'>".__('Database delete failed.')."</div>";
} else {
    include 'materials_edit.php'; // Success!
}
