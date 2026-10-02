<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;
use \PDO;

class AssignmentSubmissionGateway extends QueryableGateway
{
    public function getSearchableColumns(): array
    {
        return ['gibbonAssignmentSubmission.status', 'gibbonAssignmentSubmission.grade'];
    }

    public function countAll(): int
    {
        $query = $this->newQuery()
            ->from($this->getTableName())
            ->cols(['COUNT(*) AS count']);

        $result = $this->runQuery($query, new QueryCriteria());
        return (int)($result->fetchColumn() ?: 0);
    }


    /**
     * One row per class student, plus anyone who already has a submission. Students with no submission
     * have a null submission id so the roster can be graded without a file upload.
     */
    public function getClassRoster(int $assignmentID): array
    {
        $sql = "
            SELECT p.gibbonPersonID, p.preferredName AS studentFirstName, p.surname AS studentSurname,
                   s.gibbonAssignmentSubmissionID, s.status, s.grade, s.pointsEarned, s.feedback,
                   s.submittedDate, s.submittedTime, s.external_doc_id, s.external_submission_id
            FROM gibbonPerson AS p
            JOIN (
                SELECT ccp.gibbonPersonID
                FROM gibbonAssignment AS a
                JOIN gibbonCourseClassPerson AS ccp ON ccp.gibbonCourseClassID = a.gibbonCourseClassID
                    AND ccp.role = 'Student'
                WHERE a.gibbonAssignmentID = :assignmentID
                UNION
                SELECT sub.gibbonPersonID
                FROM gibbonAssignmentSubmission AS sub
                WHERE sub.gibbonAssignmentID = :assignmentIDSubmitted
            ) AS roster ON roster.gibbonPersonID = p.gibbonPersonID
            LEFT JOIN gibbonAssignmentSubmission AS s ON s.gibbonAssignmentSubmissionID = (
                SELECT s2.gibbonAssignmentSubmissionID
                FROM gibbonAssignmentSubmission AS s2
                WHERE s2.gibbonAssignmentID = :assignmentIDLatest AND s2.gibbonPersonID = p.gibbonPersonID
                ORDER BY s2.gibbonAssignmentSubmissionID DESC
                LIMIT 1
            )
            ORDER BY p.surname ASC, p.preferredName ASC
        ";

        $result = $this->db()->executeQuery([
            'assignmentID' => $assignmentID,
            'assignmentIDSubmitted' => $assignmentID,
            'assignmentIDLatest' => $assignmentID,
        ], $sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubmissionsByAssignment(int $assignmentID): array
    {
        $sql = "
            SELECT s.*, p.preferredName AS studentFirstName, p.surname AS studentSurname, p.email AS studentEmail
            FROM gibbonAssignmentSubmission AS s
            INNER JOIN gibbonPerson AS p ON s.gibbonPersonID = p.gibbonPersonID
            WHERE s.gibbonAssignmentID = :assignmentID
            ORDER BY p.surname ASC, p.preferredName ASC
        ";
        $params = ['assignmentID' => $assignmentID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubmissionsByAssignmentAndPerson(int $assignmentID, int $personID): array
    {
        $sql = "
            SELECT s.*
            FROM gibbonAssignmentSubmission AS s
            WHERE s.gibbonAssignmentID = :assignmentID
            AND s.gibbonPersonID = :personID
            ORDER BY s.submittedDate DESC, s.submittedTime DESC
        ";
        $params = ['assignmentID' => $assignmentID, 'personID' => $personID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSubmissionsByStudent(int $personID): array
    {
        $sql = "
            SELECT s.*, a.name AS assignmentName, a.dueDate
            FROM gibbonAssignmentSubmission AS s
            INNER JOIN gibbonAssignment AS a ON s.gibbonAssignmentID = a.gibbonAssignmentID
            WHERE s.gibbonPersonID = :personID
            ORDER BY a.dueDate DESC
        ";
        $params = ['personID' => $personID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSubmissionStatus(int $submissionID, string $status): bool
    {
        $sql = "UPDATE gibbonAssignmentSubmission SET status = :status WHERE gibbonAssignmentSubmissionID = :submissionID";
        $params = ['status' => $status, 'submissionID' => $submissionID];
        $result = $this->db()->executeQuery($params, $sql);
        return (bool)$result->rowCount();
    }

    protected function getTableName(): string
    {
        return 'gibbonAssignmentSubmission';
    }
}
