<?php
require_once __DIR__ . '/../../gibbon.php';
require_once 'moduleFunctions.php';

use Gibbon\Forms\Form;

// Validate and sanitize input
$courseName = $_GET['courseName'] ?? '';
$courseName = htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8');

if (empty($courseName)) {
    echo "<div class='warning'>".__('Course name is missing.')."</div>";
    return;
}

// Form only—no headers, no buttons, no extra divs
$form = Form::create('addMaterial', $session->get('absoluteURL') . '/modules/Courses and Classes/materials_upload.php');
$form->setAttribute('id', 'uploadForm');
$form->setMethod('post');
$form->addHiddenValue('courseName', $courseName);
$form->addHiddenValue('tags[]', $courseName);

$form->setAttribute('hx-post', $session->get('absoluteURL') . '/modules/Courses and Classes/materials_upload.php');
$form->setAttribute('hx-target', '#materialsTable');
$form->setAttribute('hx-swap', 'innerHTML');
$form->setAttribute('enctype', 'multipart/form-data');
$form->setAttribute('hx-on', 'htmx:afterRequest: document.getElementById("uploadForm").reset()');

$row = $form->addRow();
$row->addLabel('title',__('Title'));
$row->addTextField('title')->isRequired();

$row = $form->addRow();
$row->addLabel('description',__('Description'));
$row->addTextField('description');

$row = $form->addRow();
$row->addLabel('upload',__('Upload'));
$row->addFileUpload('file')->isRequired();

$row = $form->addRow();
$row->addSubmit(__('Add Material'));

echo $form->getOutput();