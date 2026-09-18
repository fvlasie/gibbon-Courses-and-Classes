<?php
//This file describes the module, including database tables

//Basic variables
$name        = 'Courses and Classes';
$description = 'A Course-centric workflow for Gibbon.';
$entryURL    = 'coursesAndClasses_view.php';
$type        = 'Additional';
$version     = '2.4';
$author      = 'Father Vlasie';
$url = "https://github.com/fvlasie/gibbon-Courses-and-Classes";
$category = 'Learn';

//Action rows
//One array per action
 $actionRows[] = [
    'name'                      => 'Overview', //The name of the action (appears to user in the left side module menu)
    'precedence'                => '1', //If it is a grouped action, the precedence controls which is highest action in group
    'category'                  => 'Learn', //Optional: subgroups for the right hand side module menu
    'description'               => 'Main view', //Text description
    'URLList'                   => 'coursesAndClasses_view.php, assignment_list.php, assignment_view.php, assignment_add.php, assignment_edit.php, assignment_process.php, assignment_grade.php, assignment_grade_process.php',
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

$actionRows[3] = [
    'name' => 'My Course Assignments',
    'precedence' => '2',
    'category' => 'Course Assignments',
    'description' => 'Allows students and staff to view assignments.',
    'URLList' => 'assignment_list.php, assignment_view.php, assignment_submit.php, assignment_submit_process.php',
    'entryURL' => 'assignment_list.php',
    'defaultPermissionAdmin' => 'Y',
    'defaultPermissionTeacher' => 'Y',
    'defaultPermissionStudent' => 'Y',
    'defaultPermissionParent' => 'N',
    'defaultPermissionSupport' => 'Y',
    'categoryPermissionStaff' => 'Y',
    'categoryPermissionStudent' => 'Y',
    'categoryPermissionParent' => 'N',
    'categoryPermissionOther' => 'N',
];

$actionRows[4] = [
    'name' => 'Manage Course Assignments',
    'precedence' => '3',
    'category' => 'Course Assignments',
    'description' => 'Allows teachers and admins to create, edit, and grade assignments.',
    'URLList' => 'coursesAndClasses_view.php, assignment_add.php, assignment_edit.php, assignment_process.php, assignment_view.php, assignment_grade.php, assignment_grade_process.php, assignment_manage.php, assignment_table.php, assignment_delete.php',
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

$actionRows[5] = [
    'name' => 'Manage Course Catalog',
    'precedence' => '4',
    'category' => 'Learn',
    'description' => 'Set external course codes and credit hours that persist across school years.',
    'URLList' => 'externalCourseCode_edit.php',
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

$actionRows[6] = [
    'name' => 'Manage All Courses',
    'precedence' => '5',
    'category' => 'Learn',
    'description' => 'Open one additional course at a time to edit catalog details.',
    'URLList' => 'courses_manage.php, externalCourseCode_edit.php',
    'entryURL' => 'courses_manage.php',
    'defaultPermissionAdmin' => 'Y',
    'defaultPermissionTeacher' => 'N',
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
    `gibbonCourseID` INT(8) UNSIGNED ZEROFILL DEFAULT NULL,
    `courseCode` VARCHAR(60) NOT NULL,
    `externalCourseCode` VARCHAR(255) DEFAULT NULL,
    `credits` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
    `dateModified` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`gibbonCoursesAndClassesID`),
    UNIQUE KEY `courseCode` (`courseCode`),
    KEY `gibbonCourseID` (`gibbonCourseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE IF NOT EXISTS `gibbonAssignment` (
  `gibbonAssignmentID` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `gibbonCourseID` int(8) UNSIGNED ZEROFILL NOT NULL,
  `gibbonCourseClassID` int(8) UNSIGNED ZEROFILL NOT NULL,
  `gibbonStaffID` int(10) UNSIGNED ZEROFILL NOT NULL,
  `gibbonSchoolYearID` int(3) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `dueDate` date NOT NULL,
  `dueTime` time DEFAULT NULL,
  `points` decimal(5,2) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('Draft','Published','Closed') NOT NULL DEFAULT 'Draft',
  `gibbonPersonIDCreator` int(10) UNSIGNED ZEROFILL NOT NULL,
  `timestampCreator` timestamp NULL DEFAULT NULL,
  `gibbonPersonIDLastEdit` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `timestampLastEdit` timestamp NULL DEFAULT NULL,
  `external_doc_id` varchar(255) DEFAULT NULL,
  `storage_provider_type` varchar(50) DEFAULT NULL,
  `fields` text,
  PRIMARY KEY (`gibbonAssignmentID`),
  KEY `gibbonCourseID` (`gibbonCourseID`),
  KEY `gibbonCourseClassID` (`gibbonCourseClassID`),
  KEY `gibbonStaffID` (`gibbonStaffID`),
  KEY `gibbonSchoolYearID` (`gibbonSchoolYearID`),
  KEY `gibbonPersonIDCreator` (`gibbonPersonIDCreator`),
  KEY `gibbonPersonIDLastEdit` (`gibbonPersonIDLastEdit`),
  CONSTRAINT `gibbonAssignment_ibfk_1` FOREIGN KEY (`gibbonCourseID`) REFERENCES `gibbonCourse` (`gibbonCourseID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignment_ibfk_2` FOREIGN KEY (`gibbonCourseClassID`) REFERENCES `gibbonCourseClass` (`gibbonCourseClassID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignment_ibfk_3` FOREIGN KEY (`gibbonStaffID`) REFERENCES `gibbonStaff` (`gibbonStaffID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignment_ibfk_4` FOREIGN KEY (`gibbonSchoolYearID`) REFERENCES `gibbonSchoolYear` (`gibbonSchoolYearID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignment_ibfk_5` FOREIGN KEY (`gibbonPersonIDCreator`) REFERENCES `gibbonPerson` (`gibbonPersonID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignment_ibfk_6` FOREIGN KEY (`gibbonPersonIDLastEdit`) REFERENCES `gibbonPerson` (`gibbonPersonID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

$moduleTables[] = "CREATE TABLE IF NOT EXISTS `gibbonAssignmentSubmission` (
  `gibbonAssignmentSubmissionID` int(10) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
  `gibbonAssignmentID` int(10) UNSIGNED ZEROFILL NOT NULL,
  `gibbonPersonID` int(10) UNSIGNED ZEROFILL NOT NULL,
  `gibbonSchoolYearID` int(3) UNSIGNED ZEROFILL NOT NULL,
  `status` enum('Not Started','In Progress','Submitted','Graded','Returned') NOT NULL DEFAULT 'Not Started',
  `submittedDate` date DEFAULT NULL,
  `submittedTime` time DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `pointsEarned` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `gibbonPersonIDGrader` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `timestampGraded` timestamp NULL DEFAULT NULL,
  `external_submission_id` varchar(255) DEFAULT NULL,
  `external_doc_id` varchar(255) DEFAULT NULL,
  `storage_provider_type` varchar(50) DEFAULT NULL,
  `gibbonPersonIDLastEdit` int(10) UNSIGNED ZEROFILL DEFAULT NULL,
  `timestampLastEdit` timestamp NULL DEFAULT NULL,
  `fields` text,
  PRIMARY KEY (`gibbonAssignmentSubmissionID`),
  KEY `gibbonAssignmentID` (`gibbonAssignmentID`),
  KEY `gibbonPersonID` (`gibbonPersonID`),
  KEY `gibbonSchoolYearID` (`gibbonSchoolYearID`),
  KEY `gibbonPersonIDGrader` (`gibbonPersonIDGrader`),
  KEY `gibbonPersonIDLastEdit` (`gibbonPersonIDLastEdit`),
  CONSTRAINT `gibbonAssignmentSubmission_ibfk_1` FOREIGN KEY (`gibbonAssignmentID`) REFERENCES `gibbonAssignment` (`gibbonAssignmentID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignmentSubmission_ibfk_2` FOREIGN KEY (`gibbonPersonID`) REFERENCES `gibbonPerson` (`gibbonPersonID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignmentSubmission_ibfk_3` FOREIGN KEY (`gibbonSchoolYearID`) REFERENCES `gibbonSchoolYear` (`gibbonSchoolYearID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignmentSubmission_ibfk_4` FOREIGN KEY (`gibbonPersonIDGrader`) REFERENCES `gibbonPerson` (`gibbonPersonID`) ON DELETE CASCADE,
  CONSTRAINT `gibbonAssignmentSubmission_ibfk_5` FOREIGN KEY (`gibbonPersonIDLastEdit`) REFERENCES `gibbonPerson` (`gibbonPersonID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;";

$array = [
    'sourceModuleName'    => $name,
    'sourceModuleAction'  => $actionRows[1]['name'],
    'sourceModuleInclude' => 'hook_lessonPlannerView.php'
];    

$hooks[] = "INSERT INTO gibbonHook (gibbonHookID, name, type, options, gibbonModuleID)
VALUES (NULL, 'Course Materials', 'Lesson Planner', '".serialize($array)."',
(SELECT gibbonModuleID FROM gibbonModule WHERE name='$name'));";