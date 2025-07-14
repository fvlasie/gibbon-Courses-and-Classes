<?php
require_once __DIR__ . '/../../gibbon.php';

$page->breadcrumbs
    ->add($moduleName)
    ->add('Course Settings');

$page->headTitle = 'Hello World!';
$page->addContent("<h2>Hello world from Coursewide Settings!</h2>");
