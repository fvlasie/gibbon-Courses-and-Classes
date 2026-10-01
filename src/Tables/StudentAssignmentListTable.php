<?php

namespace Gibbon\Module\CoursesAndClasses\Tables;

use Gibbon\Domain\DataSet;
use Gibbon\Tables\DataTable;

class StudentAssignmentListTable
{
    public static function create(DataSet $data): DataTable
    {
        $table = DataTable::create('studentAssignmentTable')->withData($data);
        $table->setTitle(__('My Course Assignments'));

        $table->addColumn('courseName', __('Course'));
        $table->addColumn('assignmentName', __('Assignment'));
        $table->addColumn('dueDate', __('Due Date'));
        $table->addColumn('status', __('Status'));
        $table->addColumn('grade', __('Grade'));
        $table->addColumn('pointsEarned', __('Points'))
            ->format(function ($row) {
                if ($row['pointsEarned'] === null || $row['pointsEarned'] === '') {
                    return '';
                }
                return htmlspecialchars((float)$row['pointsEarned'].' / '.(float)$row['points']);
            });
        $table->addColumn('feedback', __('Feedback'))
            ->format(function ($row) {
                $feedback = trim((string)($row['feedback'] ?? ''));
                if ($feedback === '') {
                    return '';
                }
                $short = mb_strlen($feedback) > 80 ? mb_substr($feedback, 0, 80).'…' : $feedback;
                return '<span title="'.htmlspecialchars($feedback).'">'.htmlspecialchars($short).'</span>';
            });

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
