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

    public function selectAll(): array {
        $criteria = new QueryCriteria();
        $criteria->sortBy('timestamp', 'DESC');

        return $this->resourceGateway->queryResources($criteria)->toArray();
    }

    private function tagMatchesCourse(string $tagString, string $courseID): bool {
        $tags = array_map('trim', explode(',', $tagString));
        return in_array($courseID, $tags);
    }

    public function selectByCourseNames(array $courseIDs): array {
        $resources = $this->selectAll(); // or call raw query
        $grouped = [];

        foreach ($resources as $resource) {
            foreach ($courseIDs as $courseID) {
                if ($this->tagMatchesCourse($resource['tags'], $courseID)) {
                    $grouped[$courseID][] = $resource;
                }
            }
        }

        return $grouped;
    }
}
