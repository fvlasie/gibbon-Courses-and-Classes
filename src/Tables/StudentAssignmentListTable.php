<?php

namespace Gibbon\Module\CoursesAndClasses\Tables;

use Gibbon\Domain\DataSet;
use Gibbon\Tables\DataTable;

class StudentAssignmentListTable
{
    public static function create(DataSet $data): DataTable
    {
        $table = DataTable::create('studentAssignmentTable')->withData($data);
        $table->setTitle(__('My Assignments'));

        $table->addColumn('courseName', __('Course'));
        $table->addColumn('assignmentName', __('Assignment'));
        $table->addColumn('dueDate', __('Due Date'));
        $table->addColumn('status', __('Status'));
        $table->addColumn('grade', __('Grade'));

        $table->addActionColumn()
            ->addParam('gibbonAssignmentID')
            ->format(function ($row, $actions) {
                $actions->addAction('view', __('View'))
                    ->setURL('/modules/Courses and Classes/assignment_view.php');

                global $session;
                if (coursesAndClassesCanSubmitAssignment($session)) {
                    $actions->addAction('submit', __('Submit'))
                        ->setURL('/modules/Courses and Classes/assignment_submit.php');
                }
            });

        return $table;
    }
}
