<?php
require_once __DIRAPP__ . 'classes/Entity.php';

class CrashReport extends Entity
{
    private $priority;
    private $dueDate;
    private $comments;
    public function __construct($task = null)
    {
        $this->setType('zgłoszenie awarii');
        parent::__construct($task);
    }
    public function setPriority($priority): void
    {
        $this->priority = $priority;
    }
    public function getPriority(): string
    {
        return $this->priority;
    }
    public function setDueDate($date): void
    {
        $this->dueDate = $this->pullDate($date);
    }
    public function getDueDate(): string
    {
        return $this->dueDate;
    }
    public function setComments($comments): void
    {
        $this->comments = empty($comments) ? '' : $comments;
    }
    public function getComments(): string|null
    {
        return $this->comments;
    }
    public function check(&$task): int
    {
        $points = 0;
        return $points;
    }
    public function processTask(&$task, $requiredPhoneNumber = false): bool
    {
        $newTask = new self($task);
        $newTask->setDueDate($task['dueDate']);
        if (empty($task['dueDate'])) {
            $newTask->setStatus('nowy');
        } else {
            $newTask->setStatus('termin');
        }
        if (str_contains(strtolower($task['description']), 'bardzo pilne')) {
            $newTask->setPriority('krytyczny');
        } else {
            if (str_contains(strtolower($task['description']), 'pilne')) {
                $newTask->setPriority('wysoki');
            } else {
                $newTask->setPriority('normalny');
            }
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
            'priority' => $this->getPriority(),
            'dueDate' => $this->getDueDate(),
            'status' => $this->getStatus(),
            'comments' => $this->getComments(),
            'phone' => $this->getPhoneNumber(),
            'creationDate' => $this->getCreationDate()
        );
        return $result;
    }
}
