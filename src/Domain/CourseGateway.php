<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;
use \PDO;

class CourseGateway extends QueryableGateway
{
    private Connection $connection;
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);      // Call parent constructor
        $this->connection = $connection;       // Set your local property
    }

    public function getSearchableColumns(): array
        {
            return ['gibbonCourse.name', 'gibbonCourse.code'];
        }
    public function countAll(): int {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['COUNT(*) AS count']);

        $result = $this->runQuery($query, new QueryCriteria());
        return $result->fetchColumn() ?: 0;
    }
    
    public function queryRawCoursesByPerson(string $gibbonPersonID): array
    {
        $sql = "
            SELECT c.gibbonCourseID, c.name AS courseNameFull, c.nameShort AS courseName, cac.externalCourseCode, cc.nameShort AS className
            FROM gibbonCourseClassPerson AS p
            INNER JOIN gibbonCourseClass AS cc ON p.gibbonCourseClassID = cc.gibbonCourseClassID
            INNER JOIN gibbonCourse AS c ON cc.gibbonCourseID = c.gibbonCourseID
            LEFT JOIN gibbonCoursesAndClasses AS cac ON cac.gibbonCourseID = c.gibbonCourseID
            INNER JOIN gibbonSchoolYear AS sy ON c.gibbonSchoolYearID = sy.gibbonSchoolYearID
            WHERE p.gibbonPersonID = :gibbonPersonID
            AND sy.status = 'Current'
            ORDER BY c.name, cc.nameShort
            LIMIT 50
        ";
        $pdo = $this->connection->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gibbonPersonID' => $gibbonPersonID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}