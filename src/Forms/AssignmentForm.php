<?php

namespace Gibbon\Module\CoursesAndClasses\Forms;

use Gibbon\Forms\Form;
use Gibbon\Module\CoursesAndClasses\Domain\Assignment;

/**
 * AssignmentForm handles the creation and editing of assignments.
 * 
 * Note: In this architecture, the Form class is used to build the UI components.
 * The actual processing of the form is handled by the controller/page script.
 */
class AssignmentForm
{
    private Form $form;
    private ?Assignment $assignment;

    public function __construct(Form $form, ?Assignment $assignment = null)
    {
        $this->form = $form;
        $this->assignment = $assignment;
    }

    public function build(): Form
    {
        $this->form->setMethod('post');

        if ($this->assignment) {
            $this->form->addHiddenValue('gibbonAssignmentID', $this->assignment->gibbonAssignmentID);
            $this->form->addHiddenValue('action', 'edit');
        } else {
            $this->form->addHiddenValue('action', 'create');
        }

        // Basic Info
        $row = $this->form->addRow();
        $row->addLabel('name', __('Assignment Name'));
        $row->addTextField('name')->required()
            ->setValue($this->assignment?->name ?? '');

        $row = $this->form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextArea('description')
            ->setValue($this->assignment?->description ?? '');

        // Scheduling
        $row = $this->form->addRow();
        $row->addLabel('dueDate', __('Due Date'));
        $row->addDate('dueDate')->required()
            ->setValue($this->assignment?->dueDate ?? '');

        $row = $this->form->addRow();
        $row->addLabel('dueTime', __('Due Time'));
        $row->addTime('dueTime')
            ->setValue($this->assignment?->dueTime ?? '');

        // Grading & Metadata
        $row = $this->form->addRow();
        $row->addLabel('points', __('Points'));
        $row->addNumber('points')->required()
            ->setValue($this->assignment?->points ?? 10);

        $row = $this->form->addRow();
        $row->addLabel('type', __('Type'));
        $row->addSelect('type')
            ->placeholder(__('Select Type...'))
            ->fromArray([
                'written' => __('Written'),
                'quiz'    => __('Quiz'),
                'project' => __('Project'),
                'other'   => __('Other'),
            ])
            ->selected($this->assignment?->type ?? 'written');

        $row = $this->form->addRow();
        $row->addLabel('category', __('Category'));
        $row->addTextField('category')
            ->setValue($this->assignment?->category ?? '');

        $row = $this->form->addRow();
        $row->addLabel('status', __('Status'));
        $row->addSelect('status')
            ->fromArray([
                'Draft' => __('Draft'),
                'Published' => __('Published'),
                'Closed' => __('Closed'),
            ])
            ->selected($this->assignment?->status ?? 'Published');

        $row = $this->form->addRow();
        $row->addSubmit(__('Save Assignment'));

        return $this->form;
    }
}
