<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

class AssignmentSubmission
{
    public int $gibbonAssignmentSubmissionID;
    public int $gibbonAssignmentID;
    public int $gibbonPersonID;
    public int $gibbonSchoolYearID;
    public string $status;
    public ?string $submittedDate;
    public ?string $submittedTime;
    public ?string $grade;
    public ?float $pointsEarned;
    public ?string $feedback;
    public ?int $gibbonPersonIDGrader;
    public ?string $timestampGraded;
    public ?string $external_submission_id;
    public ?string $external_doc_id;
    public ?string $storage_provider_type;
    public ?int $gibbonPersonIDLastEdit;
    public ?string $timestampLastEdit;
    public ?string $fields;

    public function __construct(array $data = [])
    {
        $this->gibbonAssignmentSubmissionID = (int)($data['gibbonAssignmentSubmissionID'] ?? 0);
        $this->gibbonAssignmentID = (int)($data['gibbonAssignmentID'] ?? 0);
        $this->gibbonPersonID = (int)($data['gibbonPersonID'] ?? 0);
        $this->gibbonSchoolYearID = (int)($data['gibbonSchoolYearID'] ?? 0);
        $this->status = $data['status'] ?? 'Not Started';
        $this->submittedDate = $data['submittedDate'] ?? null;
        $this->submittedTime = $data['submittedTime'] ?? null;
        $this->grade = $data['grade'] ?? null;
        $this->pointsEarned = isset($data['pointsEarned']) ? (float)$data['pointsEarned'] : null;
        $this->feedback = $data['feedback'] ?? null;
        $this->gibbonPersonIDGrader = isset($data['gibbonPersonIDGrader']) ? (int)$data['gibbonPersonIDGrader'] : null;
        $this->timestampGraded = $data['timestampGraded'] ?? null;
        $this->external_submission_id = $data['external_submission_id'] ?? null;
        $this->external_doc_id = $data['external_doc_id'] ?? null;
        $this->storage_provider_type = $data['storage_provider_type'] ?? null;
        $this->gibbonPersonIDLastEdit = isset($data['gibbonPersonIDLastEdit']) ? (int)$data['gibbonPersonIDLastEdit'] : null;
        $this->timestampLastEdit = $data['timestampLastEdit'] ?? null;
        $this->fields = $data['fields'] ?? null;
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->gibbonAssignmentID)) {
            $errors[] = 'Assignment ID is required.';
        }
        if (empty($this->gibbonPersonID)) {
            $errors[] = 'Student ID is required.';
        }
        return $errors;
    }
}
