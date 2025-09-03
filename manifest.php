<?php
//This file describes the module, including database tables

//Basic variables
$name        = 'Courses and Classes';
$description = 'A Course-centric workflow for Gibbon.';
$entryURL    = 'coursesAndClasses_view.php';
$type        = 'Additional';
$version     = '1.6';
$author      = 'Father Vlasie';
$url = "https://github.com/fvlasie/gibbon-Coursewide-Settings";
$category = 'Learn';

//Action rows
//One array per action
 $actionRows[] = [
    'name'                      => 'Overview', //The name of the action (appears to user in the left side module menu)
    'precedence'                => '1', //If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Learn', //Optional: subgroups for the right hand side module menu
    'description'               => 'Main view', //Text description
    'URLList'                   => 'coursesAndClasses_view.php',
    'entryURL'                   => 'coursesAndClasses_view.php',
    'defaultPermissionAdmin'    => 'Y', //Default permission for built in role Admin
    'defaultPermissionTeacher'  => 'Y', //Default permission for built in role Teacher
    'defaultPermissionStudent'  => 'Y', //Default permission for built in role Student
    'defaultPermissionParent'   => 'N', //Default permission for built in role Parent
    'defaultPermissionSupport'  => 'Y', //Default permission for built in role Support
    'categoryPermissionStaff'   => 'Y', //Should this action be available to user roles in the Staff category?
    'categoryPermissionStudent' => 'Y', //Should this action be available to user roles in the Student category?
    'categoryPermissionParent'  => 'Y', //Should this action be available to user roles in the Parent category?
    'categoryPermissionOther'   => 'Y', //Should this action be available to user roles in the Other category?
]; 

//Hooks
$actionRows[1]['name'] = 'Course Materials';
$actionRows[1]['precedence'] = '0';
$actionRows[1]['category'] = 'Planner';
$actionRows[1]['description'] = 'Allows a user to view Course Materials.';
$actionRows[1]['URLList'] = 'planner_view_full.php';
$actionRows[1]['entryURL'] = 'planner_view_full.php';
$actionRows[1]['entrySidebar'] = 'N';
$actionRows[1]['menuShow'] = 'N';
$actionRows[1]['defaultPermissionAdmin'] = 'Y';
$actionRows[1]['defaultPermissionTeacher'] = 'Y';
$actionRows[1]['defaultPermissionStudent'] = 'Y';
$actionRows[1]['defaultPermissionParent'] = 'Y';
$actionRows[1]['defaultPermissionSupport'] = 'Y';
$actionRows[1]['categoryPermissionStaff'] = 'Y';
$actionRows[1]['categoryPermissionStudent'] = 'Y';
$actionRows[1]['categoryPermissionParent'] = 'Y';
$actionRows[1]['categoryPermissionOther'] = 'Y';

$actionRows[2] = [
    'name' => 'Edit Course Materials',
    'precedence' => '0',
    'category' => 'Course Materials',
    'description' => 'Allows a user to edit course materials.',
    'URLList' => 'coursesAndClasses_view.php, materials_edit.php, materials_delete.php, materials_upload.php',
    'entryURL' => 'coursesAndClasses_view.php',
    'entrySidebar' => 'N',
    'menuShow' => 'N',
    'defaultPermissionAdmin' => 'Y',
    'defaultPermissionTeacher' => 'Y',
    'defaultPermissionStudent' => 'N',
    'defaultPermissionParent' => 'N',
    'defaultPermissionSupport' => 'N',
    'categoryPermissionStaff' => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent' => 'N',
    'categoryPermissionOther' => 'N',
];

$moduleTables[] = "CREATE TABLE `gibbonCoursesAndClasses` (
    `gibbonCoursesAndClassesID` INT(8) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `gibbonCourseID` INT(8) UNSIGNED ZEROFILL NOT NULL,
    `externalCourseCode` VARCHAR(255) DEFAULT NULL,
    `dateModified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`gibbonCoursesAndClassesID`),
    FOREIGN KEY (`gibbonCourseID`) REFERENCES `gibbonCourse`(`gibbonCourseID`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";



$array = [
    'sourceModuleName'    => $name,
    'sourceModuleAction'  => $actionRows[1]['name'],
    'sourceModuleInclude' => 'hook_lessonPlannerView.php'
];

$hooks[] = "INSERT INTO gibbonHook (gibbonHookID, name, type, options, gibbonModuleID)
VALUES (NULL, 'Course Materials', 'Lesson Planner', '".serialize($array)."',
(SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";