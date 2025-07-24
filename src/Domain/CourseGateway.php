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
    parent::__construct($connection);      // 🔁 Call parent constructor
    $this->connection = $connection;       // ✅ Set your local property
}


    public function getTableName(): string
    {
        return 'gibbonCourse';
    }

    public function queryCoursesByPerson(QueryCriteria $criteria, string $gibbonPersonID)
    {
        error_log('[Before runQuery]');

        $query = $this
            ->newQuery()
            ->cols([
                'c.gibbonCourseID',
                'c.name AS courseNameFull',
                'c.nameShort AS courseName',
                'cc.nameShort AS className'
            ])
            ->from('gibbonCourseClassPerson AS p')
            ->innerJoin('gibbonCourseClass AS cc', 'p.gibbonCourseClassID = cc.gibbonCourseClassID')
            ->innerJoin('gibbonCourse AS c', 'cc.gibbonCourseID = c.gibbonCourseID')
            ->where('p.gibbonPersonID = :gibbonPersonID', ['gibbonPersonID' => $gibbonPersonID])
            ->orderBy(['c.name', 'cc.nameShort']);
            //error_log('[Bind Check] gibbonPersonID = ' . $gibbonPersonID);
            //error_log('[SQL] ' . $query->getStatement());
            $query->limit(5);
            return $this->runQuery($query, $criteria);
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
    public function queryAll(QueryCriteria $criteria)
        {
            $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols(['gibbonCourseID', 'name', 'nameShort']) // Add the columns you want
                ->orderBy(['name']);

            return $this->runQuery($query, $criteria);
        }
    public function queryLimited(): array
        {
            $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols(['gibbonCourseID', 'name'])
                ->limit(1); // This is the correct way to apply a limit
                error_log($query->getStatement());
            return $this->runQuery($query,new QueryCriteria());
        }
    public function getCoursesForUser(string $personID): array
        {
        // Build your query
        $sql = '
            SELECT c.gibbonCourseID, c.name AS courseNameFull, c.nameShort AS courseName, cc.nameShort AS className
            FROM gibbonCourseClassPerson AS p
            JOIN gibbonCourseClass AS cc ON cc.gibbonCourseClassID = p.gibbonCourseClassID
            JOIN gibbonCourse AS c ON c.gibbonCourseID = cc.gibbonCourseID
            WHERE p.gibbonPersonID = :personID
            ORDER BY c.nameShort, cc.nameShort
        ';

        $data = ['personID' => $personID];

        // Run the query
        $result = $this->db()->select($sql, $data);

        $rows = [];
        // Preprocess each row
        foreach ($result->fetchAll() as $row) {
            $row['Course'] = $row['courseName'] ?? '';
            $row['Class'] = $row['className'] ?? '';
            $row['Full Name'] = $row['courseNameFull'] ?? '';
            $rows[] = $row;
        }

        return $rows;
    }
    public function queryRawCoursesByPerson(string $gibbonPersonID): array
    {
        $sql = "
            SELECT c.gibbonCourseID, c.name AS courseNameFull, c.nameShort AS courseName, cc.nameShort AS className
            FROM gibbonCourseClassPerson AS p
            INNER JOIN gibbonCourseClass AS cc ON p.gibbonCourseClassID = cc.gibbonCourseClassID
            INNER JOIN gibbonCourse AS c ON cc.gibbonCourseID = c.gibbonCourseID
            WHERE p.gibbonPersonID = :gibbonPersonID
            ORDER BY c.name, cc.nameShort
            LIMIT 50
        ";

        $pdo = $this->connection->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['gibbonPersonID' => $gibbonPersonID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
