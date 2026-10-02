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
     * One row per reportable class student. Students with no submission have a null submission id so
     * the roster can be graded without a file upload. Non-reportable students are omitted.
     */
    public function getClassRoster(int $assignmentID): array
    {
        $sql = "
            SELECT p.gibbonPersonID, p.preferredName AS studentFirstName, p.surname AS studentSurname,
                   s.gibbonAssignmentSubmissionID, s.status, s.grade, s.pointsEarned, s.feedback,
                   s.submittedDate, s.submittedTime, s.external_doc_id, s.external_submission_id
            FROM gibbonAssignment AS a
            JOIN gibbonCourseClass AS cc ON cc.gibbonCourseClassID = a.gibbonCourseClassID AND cc.reportable = 'Y'
            JOIN gibbonCourseClassPerson AS ccp ON ccp.gibbonCourseClassID = cc.gibbonCourseClassID
                AND ccp.role = 'Student' AND ccp.reportable = 'Y'
            JOIN gibbonPerson AS p ON p.gibbonPersonID = ccp.gibbonPersonID
            LEFT JOIN gibbonAssignmentSubmission AS s ON s.gibbonAssignmentSubmissionID = (
                SELECT s2.gibbonAssignmentSubmissionID
                FROM gibbonAssignmentSubmission AS s2
                WHERE s2.gibbonAssignmentID = a.gibbonAssignmentID AND s2.gibbonPersonID = p.gibbonPersonID
                ORDER BY s2.gibbonAssignmentSubmissionID DESC
                LIMIT 1
            )
            WHERE a.gibbonAssignmentID = :assignmentID
            ORDER BY p.surname ASC, p.preferredName ASC
        ";

        $result = $this->db()->executeQuery([
            'assignmentID' => $assignmentID,
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
