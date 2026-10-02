<?php

namespace Gibbon\Module\CoursesAndClasses\Services;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Module\CoursesAndClasses\Domain\Assignment;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentSubmission;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentGateway;
use Gibbon\Module\CoursesAndClasses\Domain\AssignmentSubmissionGateway;
use \PDO;

class AssignmentService
{
    private Connection $connection;
    private AssignmentGateway $assignmentGateway;
    private AssignmentSubmissionGateway $submissionGateway;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->assignmentGateway = new AssignmentGateway($connection);
        $this->submissionGateway = new AssignmentSubmissionGateway($connection);
    }

    /**
     * Creates a new assignment.
     * 
     * @param Assignment $assignment
     * @return int The new assignment ID
     * @throws \Exception If validation fails
     */
    public function createAssignment(Assignment $assignment): int
    {
        $errors = $assignment->validate();
        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        $sql = "
            INSERT INTO gibbonAssignment (
                gibbonCourseID, gibbonCourseClassID, gibbonStaffID, gibbonSchoolYearID,
                name, description, dueDate, dueTime, points, type, category, status,
                gibbonPersonIDCreator, timestampCreator, external_doc_id, storage_provider_type, fields
            ) VALUES (
                :gibbonCourseID, :gibbonCourseClassID, :gibbonStaffID, :gibbonSchoolYearID,
                :name, :description, :dueDate, :dueTime, :points, :type, :category, :status,
                :gibbonPersonIDCreator, NOW(), :external_doc_id, :storage_provider_type, :fields
            )
        ";

        $params = [
            'gibbonCourseID' => $assignment->gibbonCourseID,
            'gibbonCourseClassID' => $assignment->gibbonCourseClassID,
            'gibbonStaffID' => $assignment->gibbonStaffID,
            'gibbonSchoolYearID' => $assignment->gibbonSchoolYearID,
            'name' => $assignment->name,
            'description' => $assignment->description,
            'dueDate' => $assignment->dueDate,
            'dueTime' => $assignment->dueTime,
            'points' => $assignment->points,
            'type' => $assignment->type,
            'category' => $assignment->category,
            'status' => $assignment->status,
            'gibbonPersonIDCreator' => $assignment->gibbonPersonIDCreator,
            'external_doc_id' => $assignment->external_doc_id,
            'storage_provider_type' => $assignment->storage_provider_type,
            'fields' => is_array($assignment->fields) ? json_encode($assignment->fields) : $assignment->fields
        ];

        return (int)$this->connection->insert($sql, $params);
    }

    /**
     * Updates an existing assignment.
     * 
     * @param Assignment $assignment
     * @return bool
     * @throws \Exception If validation fails
     */
    public function updateAssignment(Assignment $assignment): bool
    {
        $errors = $assignment->validate();
        if (!empty($errors)) {
            throw new \Exception('Validation failed: ' . implode(', ', $errors));
        }

        $sql = "
            UPDATE gibbonAssignment SET
                gibbonCourseID = :gibbonCourseID,
                gibbonCourseClassID = :gibbonCourseClassID,
                gibbonStaffID = :gibbonStaffID,
                gibbonSchoolYearID = :gibbonSchoolYearID,
                name = :name,
                description = :description,
                dueDate = :dueDate,
                dueTime = :dueTime,
                points = :points,
                type = :type,
                category = :category,
                status = :status,
                external_doc_id = :external_doc_id,
                storage_provider_type = :storage_provider_type,
                fields = :fields,
                timestampLastEdit = NOW(),
                gibbonPersonIDLastEdit = :gibbonPersonIDLastEdit
            WHERE gibbonAssignmentID = :gibbonAssignmentID
        ";

        $params = [
            'gibbonAssignmentID' => $assignment->gibbonAssignmentID,
            'gibbonCourseID' => $assignment->gibbonCourseID,
            'gibbonCourseClassID' => $assignment->gibbonCourseClassID,
            'gibbonStaffID' => $assignment->gibbonStaffID,
            'gibbonSchoolYearID' => $assignment->gibbonSchoolYearID,
            'name' => $assignment->name,
            'description' => $assignment->description,
            'dueDate' => $assignment->dueDate,
            'dueTime' => $assignment->dueTime,
            'points' => $assignment->points,
            'type' => $assignment->type,
            'category' => $assignment->category,
            'status' => $assignment->status,
            'external_doc_id' => $assignment->external_doc_id,
            'storage_provider_type' => $assignment->storage_provider_type,
            'fields' => is_array($assignment->fields) ? json_encode($assignment->fields) : $assignment->fields,
            'gibbonPersonIDLastEdit' => $assignment->gibbonPersonIDLastEdit
        ];

        $result = $this->connection->executeQuery($params, $sql);
        return (bool)$result->rowCount();
    }

    /**
     * Submits an assignment for a student.
     * 
     * @param int $studentID
     * @param int $assignmentID
     * @param array $submissionData
     * @return int The new submission ID
     */
    public function submitAssignment(int $studentID, int $assignmentID, array $submissionData): int
    {
        $sql = "
            INSERT INTO gibbonAssignmentSubmission (
                gibbonAssignmentID, gibbonPersonID, gibbonSchoolYearID,
                status, submittedDate, submittedTime, external_submission_id, 
                external_doc_id, storage_provider_type, gibbonPersonIDLastEdit, timestampLastEdit, fields
            ) VALUES (
                :gibbonAssignmentID, :gibbonPersonID, :gibbonSchoolYearID,
                'Submitted', CURDATE(), CURTIME(), :external_submission_id,
                :external_doc_id, :storage_provider_type, :gibbonPersonIDLastEdit, NOW(), :fields
            )
        ";

        $params = [
            'gibbonAssignmentID' => $assignmentID,
            'gibbonPersonID' => $studentID,
            'gibbonSchoolYearID' => $submissionData['gibbonSchoolYearID'],
            'external_submission_id' => $submissionData['external_submission_id'] ?? null,
            'external_doc_id' => $submissionData['external_doc_id'] ?? null,
            'storage_provider_type' => $submissionData['storage_provider_type'] ?? null,
            'gibbonPersonIDLastEdit' => $studentID,
            'fields' => is_array($submissionData['fields'] ?? null) ? json_encode($submissionData['fields']) : ($submissionData['fields'] ?? null)
        ];

        return (int)$this->connection->insert($sql, $params);
    }

    /**
     * Grades a student's assignment submission.
     * 
     * @param int $staffID
     * @param int $submissionID
     * @param string $grade
     * @param float|null $pointsEarned Null leaves the score blank instead of storing 0
     * @param string $feedback
     * @return bool
     */
    public function gradeAssignment(int $staffID, int $submissionID, string $grade, ?float $pointsEarned, string $feedback): bool
    {
        $graded = $grade !== '' || $pointsEarned !== null || $feedback !== '';
        if (!$graded) {
            $sql = "
                UPDATE gibbonAssignmentSubmission SET
                    status = IF(submittedDate IS NULL, 'Not Started', 'Submitted'),
                    grade = NULL,
                    pointsEarned = NULL,
                    feedback = NULL,
                    gibbonPersonIDGrader = NULL,
                    timestampGraded = NULL,
                    gibbonPersonIDLastEdit = :gibbonPersonIDLastEdit,
                    timestampLastEdit = NOW()
                WHERE gibbonAssignmentSubmissionID = :submissionID
            ";
            $this->connection->executeQuery([
                'gibbonPersonIDLastEdit' => $staffID,
                'submissionID' => $submissionID,
            ], $sql);

            return $this->connection->getQuerySuccess();
        }

        $sql = "
            UPDATE gibbonAssignmentSubmission SET
                status = 'Graded',
                grade = :grade,
                pointsEarned = :pointsEarned,
                feedback = :feedback,
                gibbonPersonIDGrader = :gibbonPersonIDGrader,
                timestampGraded = NOW(),
                gibbonPersonIDLastEdit = :gibbonPersonIDLastEdit,
                timestampLastEdit = NOW()
            WHERE gibbonAssignmentSubmissionID = :submissionID
        ";

        $this->connection->executeQuery([
            'grade' => $grade !== '' ? $grade : null,
            'pointsEarned' => $pointsEarned,
            'feedback' => $feedback !== '' ? $feedback : null,
            'gibbonPersonIDGrader' => $staffID,
            'gibbonPersonIDLastEdit' => $staffID,
            'submissionID' => $submissionID,
        ], $sql);

        return $this->connection->getQuerySuccess();
    }

    /**
     * Save one roster row. Blank points stay null. A student with no submission and nothing entered is skipped.
     * Returns saved, skipped, or invalid.
     */
    public function saveRosterGrade(int $staffID, array $assignment, array $student, string $grade, ?float $pointsEarned, string $feedback): string
    {
        $grade = mb_substr(trim($grade), 0, 50);
        $feedback = trim($feedback);
        $max = $assignment['points'] ?? null;
        if ($pointsEarned !== null && ($pointsEarned < 0 || ($max !== null && $max !== '' && $pointsEarned > (float) $max))) {
            return 'invalid';
        }

        $hasGrade = $grade !== '' || $pointsEarned !== null || $feedback !== '';
        $submissionID = (int) ($student['gibbonAssignmentSubmissionID'] ?? 0);
        $storedPoints = ($student['pointsEarned'] ?? '') === '' || $student['pointsEarned'] === null
            ? null
            : round((float) $student['pointsEarned'], 2);
        $unchanged = $grade === trim((string) ($student['grade'] ?? ''))
            && $pointsEarned === $storedPoints
            && $feedback === trim((string) ($student['feedback'] ?? ''));
        if ($unchanged || ($submissionID <= 0 && !$hasGrade)) {
            return 'skipped';
        }

        if ($submissionID > 0) {
            return $this->gradeAssignment($staffID, $submissionID, $grade, $pointsEarned, $feedback) ? 'saved' : 'invalid';
        }

        $sql = "
            INSERT INTO gibbonAssignmentSubmission (
                gibbonAssignmentID, gibbonPersonID, gibbonSchoolYearID, status,
                grade, pointsEarned, feedback, gibbonPersonIDGrader, timestampGraded,
                gibbonPersonIDLastEdit, timestampLastEdit
            ) VALUES (
                :gibbonAssignmentID, :gibbonPersonID, :gibbonSchoolYearID, 'Graded',
                :grade, :pointsEarned, :feedback, :gibbonPersonIDGrader, NOW(),
                :gibbonPersonIDLastEdit, NOW()
            )
        ";
        $inserted = $this->connection->insert($sql, [
            'gibbonAssignmentID' => (int) $assignment['gibbonAssignmentID'],
            'gibbonPersonID' => (int) $student['gibbonPersonID'],
            'gibbonSchoolYearID' => (int) $assignment['gibbonSchoolYearID'],
            'grade' => $grade !== '' ? $grade : null,
            'pointsEarned' => $pointsEarned,
            'feedback' => $feedback !== '' ? $feedback : null,
            'gibbonPersonIDGrader' => $staffID,
            'gibbonPersonIDLastEdit' => $staffID,
        ]);

        return $inserted ? 'saved' : 'invalid';
    }
}
