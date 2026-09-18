<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = [];
$count = 0;

//v2.2
$sql[$count][0] = '2.2';
$sql[$count][1] = '-- First version with assignments, nothing to update';

//v2.3
++$count;
$sql[$count][0] = '2.3';
$sql[$count][1] = "
UPDATE gibbonAction SET name='My Course Assignments', category='Course Assignments' WHERE name='My Assignments' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
UPDATE gibbonAction SET name='Manage Course Assignments', category='Course Assignments' WHERE name='Manage Assignments' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
";

//v2.4
++$count;
$sql[$count][0] = '2.4';
$sql[$count][1] = "
UPDATE gibbonAction SET defaultPermissionTeacher='Y' WHERE name='Manage Course Catalog' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID)
SELECT gibbonRole.gibbonRoleID, gibbonAction.gibbonActionID
FROM gibbonRole
JOIN gibbonAction ON gibbonAction.name='Manage Course Catalog'
JOIN gibbonModule ON gibbonModule.gibbonModuleID=gibbonAction.gibbonModuleID
WHERE gibbonModule.name='Courses and Classes' AND gibbonRole.name='Teacher';end
";
