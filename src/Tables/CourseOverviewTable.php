<?php

namespace Gibbon\Module\CoursesAndClasses\Tables;

use Gibbon\Contracts\Database\Connection;
use Gibbon\Tables\DataTable;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\DataSet;
use Gibbon\Tables\Renderer\SimpleRenderer;
use Gibbon\Tables\Format;
use Gibbon\Module\CoursesAndClasses\Domain\CourseMaterialsGateway;
use PDO;

require_once __DIR__ . '/../Domain/CourseMaterialsGateway.php';

class CourseOverviewTable extends DataTable
{
    private Connection $connection;
    private string $guid;
    private array $resources = [];

    public function __construct(QueryCriteria $criteria, string $tableID, DataSet $data, Connection $connection, string $guid )
    {
        $this->connection = $connection;
        $this->guid = $guid;

        $renderer = new SimpleRenderer($criteria, $tableID);
        parent::__construct($renderer, $data);

        $rawCourses = iterator_to_array($data);
        $courseIDs = array_column($rawCourses, 'gibbonCourseID');

        $materialsGateway = new CourseMaterialsGateway($connection);
        $this->resources = $materialsGateway->selectByCourseIDs($courseIDs);

        // Save for use in formatter
        //$this->resources = $resources;
        
        $this->setTitle(__('📘 My Courses')); // Optional table title

        // Full Name Column
        $this->addColumn('courseNameFull', __('Course Name'))
            ->context('primary') // Makes this visually stand out
            ->width('30%')       // Adjust as needed
            ->sortable('courseNameFull')
            ->format(function ($course) {
                return $course['courseNameFull'] ?? '[Missing]';
            });

        // Course Short Code
        $this->addColumn('courseName', __('Course Code'))
            ->context('secondary')
            ->width('20%')
            ->sortable('courseName')
            ->format(function ($course) {
                return $course['courseName'] ?? '[Missing]';
            });

        // Class Group
        $this->addColumn('className', __('Classes'))
            ->width('10%')
            ->sortable('className')
            ->format(function ($course) {
                return $course['className'] ?? '[Missing]';
            });

        $this->addColumn('materials', __('Course Materials'))
            ->width('20%')
            ->sortable(false)
            ->format(function ($course) {
                $id = $course['gibbonCourseID'] ?? null;
                $files = $this->resources[0] ?? [];

                if (empty($files)) return '<em>No materials</em>';

                $guid = $this->guid;

                return implode('<br>', array_map(function ($file) use ($guid) {
                    return getResourceLink(
                        $guid,
                        $file['gibbonResourceID'],
                        $file['type'],
                        $file['name'],
                        $file['path']
                    ) . Format::small(
                        Format::name($file['title'], $file['preferredName'], $file['surname'], 'Staff')
                    );
                }, $files));
            });
            
        // Debug Column (temporary)
/*         $this->addColumn('debug', __('Debug'))
            ->width('40%')
            ->format(function ($course) {
                error_log('[Format Row] ' . print_r($course, true));
                return json_encode($course, JSON_PRETTY_PRINT);
            });
 */    
    }
}
