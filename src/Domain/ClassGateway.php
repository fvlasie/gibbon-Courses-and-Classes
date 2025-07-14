<?php
namespace Gibbon\Module\CoursesAndClasses\Domain;

use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class ClassGateway extends QueryableGateway
{
    public function getTableName(): string
    {
        return 'class';
    }

    public function queryClassesByCourse(QueryCriteria $criteria, $courseID)
    {
        $query = $this
            ->newQuery()
            ->from('class')
            ->where('courseID', $courseID)
            ->orderBy(['name']);

        return $this->runQuery($query, $criteria);
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'code'];
    }
}
