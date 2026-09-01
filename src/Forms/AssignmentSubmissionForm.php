<?php

namespace Gibbon\Module\CoursesAndClasses\Forms;

use Gibbon\Forms\Form;

/**
 * AssignmentSubmissionForm handles the student submission process.
 */
class AssignmentSubmissionForm
{
    private Form $form;
    private int $gibbonAssignmentID;
    private int $gibbonPersonID;

    public function __construct(Form $form, int $gibbonAssignmentID, int $gibbonPersonID)
    {
        $this->form = $form;
        $this->gibbonAssignmentID = $gibbonAssignmentID;
        $this->gibbonPersonID = $gibbonPersonID;
    }

    public function build(): Form
    {
        $this->form->setMethod('post');
        $this->form->setAttribute('enctype', 'multipart/form-data');

        $this->form->addHiddenValue('gibbonAssignmentID', $this->gibbonAssignmentID);
        $this->form->addHiddenValue('gibbonPersonID', $this->gibbonPersonID);

        $row = $this->form->addRow();
        $row->addLabel('file', __('Upload File'));
        $row->addFileUpload('file')->required();

        $row = $this->form->addRow();
        $row->addLabel('comments', __('Comments/Notes'));
        $row->addTextArea('comments');

        $row = $this->form->addRow();
        $row->addSubmit(__('Submit Assignment'));

        return $this->form;
    }
}
