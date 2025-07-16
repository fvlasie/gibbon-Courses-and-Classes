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

$moduleTables[] = "CREATE TABLE `gibbonCourseSetting` (
    `gibbonCourseSettingID` int(8) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `gibbonCourseID` int(8) unsigned zerofill NOT NULL,
    `settingKey` varchar(64) NOT NULL,
    `settingValue` text NOT NULL,
    `dateModified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`gibbonCourseSettingID`),
    FOREIGN KEY (`gibbonCourseID`) REFERENCES `gibbonCourse`(`gibbonCourseID`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";