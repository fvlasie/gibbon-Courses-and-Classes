<?php

namespace Gibbon\Module\CoursesAndClasses\Domain;

class Assignment
{
    public int $gibbonAssignmentID;
    public int $gibbonCourseID;
    public int $gibbonCourseClassID;
    public int $gibbonStaffID;
    public int $gibbonSchoolYearID;
    public string $name;
    public string $description;
    public string $dueDate;
    public ?string $dueTime;
    public ?float $points;
    public string $type;
    public ?string $category;
    public string $status;
    public int $gibbonPersonIDCreator;
    public ?string $timestampCreator;
    public ?int $gibbonPersonIDLastEdit;
    public ?string $timestampLastEdit;
    public ?string $external_doc_id;
    public ?string $storage_provider_type;
    public ?string $fields;

    public function __construct(array $data = [])
    {
        $this->gibbonAssignmentID = (int)($data['gibbonAssignmentID'] ?? 0);
        $this->gibbonCourseID = (int)($data['gibbonCourseID'] ?? 0);
        $this->gibbonCourseClassID = (int)($data['gibbonCourseClassID'] ?? 0);
        $this->gibbonStaffID = (int)($data['gibbonStaffID'] ?? 0);
        $this->gibbonSchoolYearID = (int)($data['gibbonSchoolYearID'] ?? 0);
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->dueDate = $data['dueDate'] ?? '';
        $this->dueTime = $data['dueTime'] ?? null;
        $this->points = isset($data['points']) ? (float)$data['points'] : null;
        $this->type = $data['type'] ?? '';
        $this->category = $data['category'] ?? null;
        $this->status = $data['status'] ?? 'Published';
        $this->gibbonPersonIDCreator = (int)($data['gibbonPersonIDCreator'] ?? 0);
        $this->timestampCreator = $data['timestampCreator'] ?? null;
        $this->gibbonPersonIDLastEdit = isset($data['gibbonPersonIDLastEdit']) ? (int)$data['gibbonPersonIDLastEdit'] : null;
        $this->timestampLastEdit = $data['timestampLastEdit'] ?? null;
        $this->external_doc_id = $data['external_doc_id'] ?? null;
        $this->storage_provider_type = $data['storage_provider_type'] ?? null;
        $this->fields = $data['fields'] ?? null;
    }

    public function validate(): array
    {
        $errors = [];
        if (empty($this->name)) {
            $errors[] = 'Assignment name is required.';
        }
        if (empty($this->dueDate)) {
            $errors[] = 'Due date is required.';
        }
        if (empty($this->gibbonCourseID)) {
            $errors[] = 'Course ID is required.';
        }
        if (empty($this->gibbonCourseClassID)) {
            $errors[] = 'Course Class ID is required.';
        }
        return $errors;
    }
}
