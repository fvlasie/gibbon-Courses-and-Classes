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
