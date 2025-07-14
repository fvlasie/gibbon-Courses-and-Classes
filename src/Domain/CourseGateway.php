<?php
namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class CourseGateway extends QueryableGateway
{
    public function getTableName(): string
    {
        return 'course';
    }

    public function queryCoursesByPerson(QueryCriteria $criteria, $gibbonPersonID)
    {
        $query = $this
            ->newQuery()
            ->from('course')
            ->join('courseEnrolment', 'course.courseID=courseEnrolment.courseID')
            ->where('courseEnrolment.gibbonPersonID', $gibbonPersonID)
            ->orderBy(['course.name']);

        return $this->runQuery($query, $criteria);
    }

    public function getSearchableColumns(): array
    {
        return ['course.name', 'course.code'];
    }
}
