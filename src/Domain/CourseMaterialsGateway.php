<?php
namespace Gibbon\Module\CoursesAndClasses\Domain;
use Gibbon\Contracts\Database\Connection;
use Gibbon\Domain\Planner\ResourceGateway;
use Gibbon\Domain\QueryCriteria;

class CourseMaterialsGateway
{
    private ResourceGateway $resourceGateway;

    public function __construct(Connection $connection) {
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

    public function insertResource(array $resourceData): ?int {
        if (empty($resourceData['name']) || empty($resourceData['content'])) {
            // Optionally log or throw
            return null;
        }

        return $this->resourceGateway->insert($resourceData);
    }

    public function deleteResource(int $resourceID): bool {
        if ($resourceID <= 0) {
            return false;
        }

        $resource = $this->resourceGateway->getByID($resourceID);
        if ($resource === false) {
            return false;
        }

        // Remove file from uploads directory
        $relativePath = $resource['content'] ?? '';
        if (!empty($relativePath)) {
            $absolutePath = __DIR__ . '/../../uploads/' . $relativePath;

            if (file_exists($absolutePath)) {
                unlink($absolutePath);
            }
        }

        // Delete from DB
        return $this->resourceGateway->delete($resourceID);
    }

    public function getResourceByID(int $resourceID): ?array {
        if ($resourceID <= 0) {
            return null;
        }

        return $this->resourceGateway->getByID($resourceID) ?: null;
    }
}
