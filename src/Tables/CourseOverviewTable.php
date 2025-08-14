<?php

namespace Gibbon\Module\CoursesAndClasses\Tables;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\DataSet;
use Gibbon\Tables\Renderer\SimpleRenderer;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use Gibbon\Tables\Renderer\RendererInterface;

require_once __DIR__ . '/../Domain/CourseMaterialsGateway.php';
require_once  __DIR__ . '/../../moduleFunctions.php';

class CourseOverviewTable extends DataTable
{
    private Connection $connection;
    private string $guid;
    private array $resources = [];
    private array $courseIDs = [];
    private array $classMap = [];

    public function __construct(QueryCriteria $criteria, string $tableID, DataSet $data, Connection $connection, string $guid)
    {
        $renderer = new SimpleRenderer($criteria, $tableID);
        parent::__construct($renderer, $data);

        $this->connection = $connection;
        $this->guid = $guid;

        $rawCourses = iterator_to_array($data);
        $this->courseIDs = array_column($rawCourses, 'gibbonCourseID');
        $courseNames = array_column($rawCourses, 'courseName');

        $materialsGateway = new CourseMaterialsGateway($connection);
        $this->resources = $materialsGateway->selectByCourseNames($courseNames);

        $this->setTitle(__('📘 My Courses'));
        $this->addColumn('courseNameFull', __('Course'));
        $this->addColumn('courseName', __('Code'));
        $this->addColumn('materials', __('Materials'));
        $this->addActionColumn('actions')
            ->addAction('view', __('View'))
            ->setURL(function ($row) {
                return "index.php?q=/modules/Planner/materials_view.php&courseName=" . urlencode($row['courseName'] ?? '');
            });

        $this->addColumn('Debug', __('Debug'))->format(function ($row) {
            return $row['__debugEditLog'] ?? '';
        });
        $this->addColumn('classes', __('Classes'))->format(function ($row) {
            $links = array_map(function ($class) {
                $id = $class['classID'];
                $name = htmlspecialchars($class['fullName']);

                return $id
                    ? "<a href='index.php?q=/modules/Departments/department_course_class.php&gibbonCourseClassID=$id'>$name</a>"
                    : $name;
            }, $row['classes']);
            return implode(', ', $links);
        });

    }

    private function collapseByCourse(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $code = $row['courseName'] ?? '[Unknown]';
            $className = $row['className'] ?? '[Unassigned]';

            if (!isset($grouped[$code])) {
                $files = $this->resources[$code] ?? [];
                $uniqueFiles = [];
                foreach ($files as $file) {
                    $id = $file['gibbonResourceID'];
                    if (!isset($uniqueFiles[$id])) {
                        $uniqueFiles[$id] = $file;
                    }
                }

                $materials = empty($uniqueFiles)
                    ? '<em>No materials</em>'
                    : implode('<br>', array_map(function ($file) {
                        $link = getResourceLink(
                            $this->guid,
                            $file['gibbonResourceID'],
                            $file['type'],
                            $file['name'],
                            $file['content']
                        );
                        $staff = htmlspecialchars("{$file['title']} {$file['preferredName']} {$file['surname']}");
                        return $link . "<small>($staff)</small>";
                    }, $uniqueFiles));

                $grouped[$code] = [
                    'courseName' => $code,
                    'gibbonCourseID' => $row['gibbonCourseID'],
                    'courseNameFull' => $row['courseNameFull'] ?? '[Unknown Name]',
                    'materials' => $materials,
                    'classes' => []
                ];
            }

            if (!in_array($className, $grouped[$code]['classes'])) {
                $classInfo = $this->classMap[$row['gibbonCourseID']][$className] ?? [];
                $grouped[$code]['classes'][] = [
                    'name' => $className,
                    'fullName' => $code . '.' . $className ?? $className,
                    'classID' => $classInfo['id'] ?? null,
                ];
            }
        }
        return $grouped;
    }

    public function render($rows, ?RendererInterface $renderer = null): string
    {
        //error_log("📣 render called with ".count($rows)." rows.");
        foreach ($this->courseIDs as $courseID) {
            $this->classMap[$courseID] = getClassInfoByCourse($this->connection, $courseID);
        }

        $groupedCourses = $this->collapseByCourse(iterator_to_array($rows ?? []));
        $this->withData($groupedCourses);

        if ($renderer === null) {
            $renderer = $this->getRenderer();
        }
        return parent::render($groupedCourses, $renderer);
    }
}