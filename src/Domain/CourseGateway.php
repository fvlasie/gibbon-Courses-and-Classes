<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\DataSet;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;
use Gibbon\Domain\Traits\TableAware;
use \PDO;

class CourseGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'gibbonCourse';
    private static $primaryKey = 'gibbonCourseID';
    private static $searchableColumns = ['gibbonCourse.name', 'gibbonCourse.nameShort', 'gibbonCoursesAndClasses.externalCourseCode'];

    private Connection $connection;
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);      // Call parent constructor
        $this->connection = $connection;       // Set your local property
    }

    public function getSearchableColumns(): array
    {
        return ['gibbonCourse.name', 'gibbonCourse.nameShort', 'gibbonCoursesAndClasses.externalCourseCode'];
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
            SELECT c.gibbonCourseID, c.gibbonDepartmentID, c.name AS courseNameFull, c.nameShort AS courseName,
                cac.externalCourseCode, cac.credits, cc.gibbonCourseClassID, cc.nameShort AS className
            FROM gibbonCourseClassPerson AS p
            INNER JOIN gibbonCourseClass AS cc ON p.gibbonCourseClassID = cc.gibbonCourseClassID
            INNER JOIN gibbonCourse AS c ON cc.gibbonCourseID = c.gibbonCourseID
            LEFT JOIN gibbonCoursesAndClasses AS cac ON cac.courseCode = c.nameShort
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

    public function queryRawCourseByID(int $gibbonCourseID): array
    {
        $sql = "
            SELECT c.gibbonCourseID, c.gibbonDepartmentID, c.name AS courseNameFull, c.nameShort AS courseName,
                cac.externalCourseCode, cac.credits, cc.gibbonCourseClassID, cc.nameShort AS className
            FROM gibbonCourse AS c
            LEFT JOIN gibbonCourseClass AS cc ON cc.gibbonCourseID = c.gibbonCourseID
            LEFT JOIN gibbonCoursesAndClasses AS cac ON cac.courseCode = c.nameShort
            WHERE c.gibbonCourseID = :gibbonCourseID
            ORDER BY cc.nameShort
        ";
        $pdo = $this->connection->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gibbonCourseID' => $gibbonCourseID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCourseSelectBySchoolYear(int $gibbonSchoolYearID): array
    {
        $sql = "SELECT gibbonCourseID, CONCAT(nameShort, ' — ', name) AS label
                FROM gibbonCourse
                WHERE gibbonSchoolYearID = :gibbonSchoolYearID
                ORDER BY nameShort, name";
        $pdo = $this->connection->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gibbonSchoolYearID' => $gibbonSchoolYearID]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, 'label', 'gibbonCourseID');
    }
}