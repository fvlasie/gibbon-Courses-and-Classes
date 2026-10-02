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

//v2.5
++$count;
$sql[$count][0] = '2.5';
$sql[$count][1] = "
ALTER TABLE `gibbonCoursesAndClasses` MODIFY `credits` DECIMAL(4,2) NOT NULL DEFAULT 3.00;end
UPDATE gibbonCoursesAndClasses SET credits=3.00 WHERE credits=0;end
INSERT IGNORE INTO gibbonCoursesAndClasses (gibbonCourseID, courseCode, credits) SELECT MAX(gibbonCourse.gibbonCourseID), gibbonCourse.nameShort, 3.00 FROM gibbonCourse LEFT JOIN gibbonCoursesAndClasses ON gibbonCoursesAndClasses.courseCode=gibbonCourse.nameShort WHERE gibbonCoursesAndClasses.gibbonCoursesAndClassesID IS NULL AND gibbonCourse.nameShort<>'' GROUP BY gibbonCourse.nameShort;end
";

//v2.6
++$count;
$sql[$count][0] = '2.6';
$sql[$count][1] = "
UPDATE gibbonAction SET URLList='coursesAndClasses_view.php, assignment_list.php, assignment_view.php' WHERE name='Overview' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
";

//v2.7
++$count;
$sql[$count][0] = '2.7';
$sql[$count][1] = "
UPDATE gibbonAction SET URLList='courses_manage.php, courses_manageAjax.php, externalCourseCode_edit.php', description='Edit external course codes and credits for every course in a school year. Administrators only.', defaultPermissionTeacher='N', defaultPermissionSupport='N' WHERE name='Manage All Courses' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
DELETE gibbonPermission FROM gibbonPermission JOIN gibbonAction ON gibbonAction.gibbonActionID=gibbonPermission.gibbonActionID WHERE gibbonAction.name='Manage All Courses' AND gibbonAction.gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes') AND gibbonPermission.gibbonRoleID<>1;end
INSERT IGNORE INTO gibbonPermission (gibbonRoleID, gibbonActionID) SELECT 1, gibbonActionID FROM gibbonAction WHERE name='Manage All Courses' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Courses and Classes');end
";
