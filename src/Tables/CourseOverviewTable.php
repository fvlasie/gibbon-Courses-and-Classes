<?php

namespace Gibbon\Module\CoursesAndClasses\Tables;

use Gibbon\Tables\DataTable;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\DataSet;

//use Gibbon\Module\CoursesAndClasses\Domain\CourseGateway;

require_once __DIR__ . '/../../../../src/Tables/Renderer/PaginatedRenderer.php';
use Gibbon\Tables\Renderer\PaginatedRenderer;

class CourseOverviewTable extends DataTable
{
    public function __construct(QueryCriteria $criteria, string $tableID, DataSet $data )
    {
        $renderer = new PaginatedRenderer($criteria, $tableID);
        parent::__construct($renderer, $data);

   /*      $this->addColumn('Course');
        $this->addColumn('Class');
        $this->addColumn('Full Name'); */

$this->addColumn('courseName')->setTitle('Course');
$this->addColumn('className')->setTitle('Class');
$this->addColumn('courseNameFull')->setTitle('Full Name');

    }
}
