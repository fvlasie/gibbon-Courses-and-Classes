<?php
namespace Gibbon\Module\CoursesAndClasses\Domain;

class ClassGateway {
    public function __construct($connection) {
        $this->connection = $connection;
    }

    public function getTableName(): string {
        return 'class';
    }

    public function queryClassesByCourse($courseID) {
        $query = "SELECT * FROM class WHERE courseID = :courseID ORDER BY name";
        $stmt = $this->connection->prepare($query);
        $stmt->bindValue(':courseID', $courseID);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'code'];
    }
}
