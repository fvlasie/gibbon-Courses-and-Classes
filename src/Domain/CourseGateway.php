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

    public function getCourseCatalog(string $courseCode): ?array
    {
        $row = $this->db()->selectOne(
            "SELECT gibbonCoursesAndClassesID, gibbonCourseID, courseCode, externalCourseCode, credits
             FROM gibbonCoursesAndClasses
             WHERE courseCode = :courseCode",
            ['courseCode' => trim($courseCode)]
        );

        return !empty($row) && is_array($row) ? $row : null;
    }

    /**
     * The single write path for catalog fields, keyed by course code so a value applies to every
     * year of the course (transcripts and tuition billing included). Only the keys present in
     * $values are changed. Returns true when a row was written, false when nothing changed.
     *
     * @throws \InvalidArgumentException when a value is invalid
     */
    public function upsertCourseCatalog(string $courseCode, array $values, ?int $gibbonCourseID = null): bool
    {
        $courseCode = trim($courseCode);
        if ($courseCode === '') {
            throw new \InvalidArgumentException(__('A course code is required.'));
        }

        $current = $this->getCourseCatalog($courseCode);
        $currentCode = trim((string)($current['externalCourseCode'] ?? ''));
        $currentCode = $currentCode !== '' ? $currentCode : null;
        $currentCredits = isset($current['credits']) ? round((float)$current['credits'], 2) : 3.00;

        $externalCourseCode = $currentCode;
        if (array_key_exists('externalCourseCode', $values)) {
            $externalCourseCode = trim((string)$values['externalCourseCode']);
            if (mb_strlen($externalCourseCode) > 255) {
                throw new \InvalidArgumentException(__('The external course code must be 255 characters or fewer.'));
            }
            $externalCourseCode = $externalCourseCode !== '' ? $externalCourseCode : null;
        }

        $credits = $currentCredits;
        if (array_key_exists('credits', $values)) {
            $raw = trim((string)$values['credits']);
            if ($raw === '' || !is_numeric($raw)) {
                throw new \InvalidArgumentException(__('Credits must be a number.'));
            }
            $credits = round((float)$raw, 2);
            if ($credits < 0 || $credits > 99.99) {
                throw new \InvalidArgumentException(__('Credits must be between 0 and 99.99.'));
            }
        }

        if (!empty($current) && $externalCourseCode === $currentCode && abs($credits - $currentCredits) < 0.001) {
            return false;
        }

        $this->db()->statement(
            "INSERT INTO gibbonCoursesAndClasses (gibbonCourseID, courseCode, externalCourseCode, credits)
             VALUES (:gibbonCourseID, :courseCode, :externalCourseCode, :credits)
             ON DUPLICATE KEY UPDATE
                gibbonCourseID = COALESCE(VALUES(gibbonCourseID), gibbonCourseID),
                externalCourseCode = VALUES(externalCourseCode),
                credits = VALUES(credits)",
            [
                'gibbonCourseID' => $gibbonCourseID ?: null,
                'courseCode' => $courseCode,
                'externalCourseCode' => $externalCourseCode,
                'credits' => number_format($credits, 2, '.', ''),
            ]
        );

        return true;
    }
}