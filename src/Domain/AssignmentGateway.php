<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;
use \PDO;

class AssignmentGateway extends QueryableGateway
{
    public function getSearchableColumns(): array
    {
        return ['gibbonAssignment.name', 'gibbonAssignment.description'];
    }

    public function countAll(): int
    {
        $query = $this->newQuery()
            ->from($this->getTableName())
            ->cols(['COUNT(*) AS count']);

        $result = $this->runQuery($query, new QueryCriteria());
        return (int)($result->fetchColumn() ?: 0);
    }

    public function getAssignmentsByCourse(int $courseID): array
    {
        $sql = "
            SELECT a.* 
            FROM gibbonAssignment AS a
            WHERE a.gibbonCourseID = :courseID
            ORDER BY a.dueDate ASC
        ";
        $params = ['courseID' => $courseID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentsByClass(int $classID): array
    {
        $sql = "
            SELECT a.* 
            FROM gibbonAssignment AS a
            WHERE a.gibbonCourseClassID = :classID
            ORDER BY a.dueDate ASC
        ";
        $params = ['classID' => $classID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentsByStaff(int $staffID): array
    {
        $sql = "
            SELECT a.* 
            FROM gibbonAssignment AS a
            WHERE a.gibbonStaffID = :staffID
            ORDER BY a.dueDate ASC
        ";
        $params = ['staffID' => $staffID];
        $result = $this->db()->executeQuery($params, $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentsForPerson(int $personID, int $schoolYearID, bool $studentView = true): array
    {
        $statusFilter = $studentView
            ? "AND a.status IN ('Published', 'Closed')"
            : '';

        $sql = "
            SELECT
                a.gibbonAssignmentID,
                a.name AS assignmentName,
                a.dueDate,
                a.status AS assignmentStatus,
                a.points,
                c.nameShort AS courseName,
                cc.nameShort AS className,
                COALESCE(s.status, 'Not Started') AS status,
                s.grade,
                s.pointsEarned,
                s.feedback,
                s.gibbonAssignmentSubmissionID
            FROM gibbonAssignment AS a
            INNER JOIN gibbonCourseClass AS cc ON a.gibbonCourseClassID = cc.gibbonCourseClassID
            INNER JOIN gibbonCourse AS c ON a.gibbonCourseID = c.gibbonCourseID
            INNER JOIN gibbonCourseClassPerson AS p
                ON p.gibbonCourseClassID = cc.gibbonCourseClassID
                AND p.gibbonPersonID = :personID
            LEFT JOIN gibbonAssignmentSubmission AS s
                ON s.gibbonAssignmentID = a.gibbonAssignmentID
                AND s.gibbonPersonID = :personIDSubmit
            WHERE c.gibbonSchoolYearID = :schoolYearID
            AND p.role NOT LIKE '%Left%'
            {$statusFilter}
            ORDER BY a.dueDate DESC, a.name
        ";

        $result = $this->db()->executeQuery([
            'personID' => $personID,
            'personIDSubmit' => $personID,
            'schoolYearID' => $schoolYearID,
        ], $sql);

        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentsBySchoolYear(int $schoolYearID): array
    {
        $sql = "
            SELECT
                a.gibbonAssignmentID,
                a.name AS assignmentName,
                a.dueDate,
                a.status AS assignmentStatus,
                a.points,
                c.nameShort AS courseName,
                cc.nameShort AS className,
                a.status AS status,
                NULL AS grade,
                NULL AS pointsEarned,
                NULL AS feedback,
                NULL AS gibbonAssignmentSubmissionID
            FROM gibbonAssignment AS a
            INNER JOIN gibbonCourseClass AS cc ON a.gibbonCourseClassID = cc.gibbonCourseClassID
            INNER JOIN gibbonCourse AS c ON a.gibbonCourseID = c.gibbonCourseID
            WHERE c.gibbonSchoolYearID = :schoolYearID
            ORDER BY a.dueDate DESC, a.name
        ";

        $result = $this->db()->executeQuery(['schoolYearID' => $schoolYearID], $sql);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssignmentByID(int $assignmentID): ?array
    {
        $sql = "SELECT * FROM " . $this->getTableName() . " WHERE gibbonAssignmentID = :assignmentID";
        $params = ['assignmentID' => $assignmentID];
        $result = $this->db()->executeQuery($params, $sql);
        $data = $result->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    public function deleteAssignment(int $assignmentID): bool
    {
        $sql = "DELETE FROM gibbonAssignment WHERE gibbonAssignmentID = :assignmentID";
        return (bool)$this->db()->delete($sql, ['assignmentID' => $assignmentID]);
    }

    protected function getTableName(): string
    {
        return 'gibbonAssignment';
    }
}
