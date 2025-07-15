<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class CourseGateway extends QueryableGateway
{
    public function getTableName(): string
    {
        return 'gibbonCourse';
    }

    public function queryCoursesByPerson(QueryCriteria $criteria, $gibbonPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('gibbonCourse')
            ->join('gibbonCourseEnrolment', 'gibbonCourse.gibbonCourseID = gibbonCourseEnrolment.gibbonCourseID')
            ->where('gibbonCourseEnrolment.gibbonPersonID', $gibbonPersonID)
            ->orderBy(['gibbonCourse.name']);

        return $this->runQuery($query, $criteria);
    }

    public function getSearchableColumns(): array
        {
            return ['gibbonCourse.name', 'gibbonCourse.code'];
        }
    public function countAll(): int
        {
            $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols(['gibbonCourseID']); // or any column that exists

            $results = $this->runQuery($query, new QueryCriteria());
            return count($results);
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
    public function queryLeanCourses(): \Gibbon\Database\Result
        {
            $query = $this
                ->newQuery()
                ->from($this->getTableName())
                ->cols(['gibbonCourseID', 'name']) // Keep it lean
                ->orderBy(['name'])
                ->limit(100);

            return $this->db()->select($query->getStatement(), $query->getBindValues());
        }
    public function getCoursesForUser(string $personID): array
        {
            $query = $this
                ->newQuery()
                ->from("gibbonCourseClassPerson AS p")
                ->join('INNER', 'gibbonCourseClass AS cc', 'cc.gibbonCourseClassID = p.gibbonCourseClassID')
                ->join('INNER', 'gibbonCourse AS c', 'c.gibbonCourseID = cc.gibbonCourseID')
                ->cols([
                    "c.gibbonCourseID",
                    "c.nameShort AS courseName",
                    "cc.nameShort AS className"
                ])
                ->where('p.gibbonPersonID = :personID', ['personID' => $personID])
                ->orderBy(['c.nameShort', 'cc.nameShort']);

            $result = $this->db()->select($query->getStatement(), $query->getBindValues());

            return iterator_to_array($result);
        }

}
