<?php
require_once __DIRAPP__ . 'classes/Entity.php';

class Overview extends Entity
{
    private $reviewDate;
    private $weekOfYear;
    private $recommendations;
    public function __construct($task = null)
    {
        $this->setType('przegląd');
        parent::__construct($task);
    }
    public function setReviewDate($date): void
    {
        $this->reviewDate = $this->pullDate($date);
    }
    public function getReviewDate(): string
    {
        return $this->reviewDate;
    }
    private function calculateWeekOfYear(): string
    {
        $date = $this->getReviewDate();
        if (empty($date)) {
            return '';
        }
        $date = explode('-', $date);
        return date('W', mktime(0, 0, 0, $date[1], $date[2], $date[0]));
    }
    public function setWeekOfYear($weekOfYear): void
    {
        $this->weekOfYear = $weekOfYear;
    }
    public function getWeekOfYear(): string|null
    {
        return $this->weekOfYear;
    }
    public function setRecommendations($recommendations): void
    {
        $this->recommendations = $recommendations;
    }
    public function getRecommendations(): string|null
    {
        return $this->recommendations;
    }
    public function check(&$task): int
    {
        $points = 0;
        if (str_contains(strtolower($task['description']), $this->getType())) {
            $points++;
        } else {
            $points--;
        }
        return $points;
    }
    public function processTask(&$task, $requiredPhoneNumber = false): bool
    {
        $newTask = new self($task);
        $newTask->setReviewDate($task['dueDate']);
        if (empty($task['dueDate'])) {
            $newTask->setStatus('nowy');
        } else {
            $newTask->setWeekOfYear($newTask->calculateWeekOfYear());
            $newTask->setStatus('zaplanowano');
        }
        if ($requiredPhoneNumber) {
            if ($newTask->validatePhoneNumber()) {
                $task = $newTask;
            } else {
                $task['unprocessedReason'] = 'incorrect phone number';
                return false;
            }
        } else {
            $task = $newTask;
        }
        return true;
    }
    public function convertToArray(): array
    {
        $result = array(
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'reviewDate' => $this->getReviewDate(),
            'weekOfYear' => $this->getWeekOfYear(),
            'status' => $this->getStatus(),
            'recommendations' => $this->getRecommendations(),
            'phone' => $this->getPhoneNumber(),
            'creationDate' => $this->getCreationDate()
        );
        return $result;
    }
}
