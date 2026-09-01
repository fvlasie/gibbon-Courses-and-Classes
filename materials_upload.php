<?php
require_once __DIR__ . '/../../gibbon.php';
require_once 'moduleFunctions.php';

use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Contracts\Database\Connection;
use Gibbon\FileUploader;

// Access check
if (!isActionAccessible($guid, $connection2, '/modules/Courses and Classes/materials_upload.php')) {
    echo "<div class='warning'>".__('You do not have access to this action.')."</div>";
    exit;
}

// Validate inputs
$courseName = $_POST['courseName'] ?? '';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$file = $_FILES['file'] ?? null;

if (empty($courseName) || empty($title) || empty($file)) {
    echo "<div class='warning'>".__('Missing required fields.')."</div>";
    exit;
}

// Upload file using Gibbon's FileUploader
$fileUploader = new FileUploader($pdo, $session);

// Optionally restrict allowed file types for course materials (e.g., documents, images, PDFs)
// Uncomment and customize as needed:
// $fileUploader->setFileExtensions(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'rtf']);

// Use courseName as folder/tag, title as display name
$attachmentPath = $fileUploader->uploadFromPost($file, $title);

if (empty($attachmentPath)) {
    $lastError = $fileUploader->getLastError();
    echo "<div class='warning'>".__('File upload failed. ').($lastError ? __($lastError) : __('Please ensure the file is not too large and is in an allowed format.'))."</div>";
    exit;
}

// Insert resource
$connection = $container->get(Connection::class);
$gateway = new CourseMaterialsGateway($connection);
$resourceData = [
    'name' => $title,
    'description' => $description,
    'gibbonYearGroupIDList' => '',
    'type' => 'File',
    'category' => '',
    'purpose' => '',
    'tags' => $courseName,
    'content' => $attachmentPath, // relative path from FileUploader
    'gibbonPersonID' => $session->get('gibbonPersonID'),
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    $insertedId = $gateway->insertResource($resourceData);
} catch (Exception $e) {
    echo "<div class='error'>".__('Insert exception: ').$e->getMessage()."</div>";
    exit;
}

if ($insertedId === null) {
    echo "<div class='error'>".__('Database insert failed.')."</div>";
} else {
    require 'materials_table.php'; // Success!
}