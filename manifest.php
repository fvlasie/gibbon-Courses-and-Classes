<?php
//This file describes the module, including database tables

//Basic variables
$name        = 'Courses and Classes';
$description = 'A Course-centric workflow for Gibbon.';
$entryURL    = 'coursesAndClasses_view.php';
$type        = 'Additional';
$version     = '0.1';
$author      = 'Father Vlasie';
$url = "https://github.com/fvlasie/gibbon-Coursewide-Settings";
$category = 'Learn';

//Action rows
//One array per action
$actionRows[] = [
    'name'                      => 'Create Issue', //The name of the action (appears to user in the left side module menu)
    'precedence'                => '0', //If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Issues', //Optional: subgroups for the right hand side module menu
    'description'               => 'Allows the user to submit an issue to be resolved by the help desk staff.', //Text description
    'URLList'                   => 'coursesAndClasses_view.php',
    'entryURL'                  => 'coursesAndClasses_view.php',
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

