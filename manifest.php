<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2022, Father Vlasie

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

//This file describes the module, including database tables

//Basic variables
$name        = 'Courses and Classes';
$description = 'For setting things that apply to a whole course.';
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
    'URLList'                   => 'issues_create.php',
    'entryURL'                  => 'issues_create.php',
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
