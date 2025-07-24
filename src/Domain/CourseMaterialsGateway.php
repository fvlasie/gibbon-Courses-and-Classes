<?php
namespace Gibbon\Module\CoursesAndClasses\Domain;
use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\Planner\ResourceGateway;
use Gibbon\Domain\QueryCriteria;

class CourseMaterialsGateway
{
    private ResourceGateway $resourceGateway;

    public function __construct(Connection $connection)
    {
        $this->resourceGateway = new ResourceGateway($connection);
    }

    public function selectByCourseIDs(array $courseIDs): array
    {
        $criteria = new QueryCriteria();
        $criteria->filterBy('tags', 'ict'); // or whatever tag you're targeting

        $resources = $this->resourceGateway->queryResources($criteria);

        // Optional: group by courseID
        $grouped = [];
        foreach ($resources->toArray() as $resource) {
            $id = $resource['gibbonCourseID'] ?? null;
            if ($id) $grouped[$id][] = $resource;
        }

        return $grouped;
    }
}
