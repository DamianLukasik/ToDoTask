<?php

abstract class Entity
{
    private $description;
    private $type;
    private $status;
    private $phoneNumber;
    private $creationDate;
    private $filetype;
    protected function __construct($task)
    {
        if (!empty($task)) {
            $this->setDescription($task['description']);
            $this->setPhoneNumber($task['phone']);
            $this->setCreationDate(date("Y-m-d H:i:s"));
        }
    }
    public function setDescription($description): void
    {
        $this->description = $description;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function setType($type): void
    {
        $this->type = $type;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function setStatus($status): void
    {
        $this->status = $status;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    protected function validatePhoneNumber(): bool
    {
        $phoneNumber = trim((string)$this->phoneNumber);
        if (empty($phoneNumber)) {
            return false;
        }
        $phoneNumber = str_replace("-", "", $phoneNumber);
        $phoneNumber = str_replace(" ", "", $phoneNumber);
        $length = 9;
        $firstChar = substr($phoneNumber, 0, 1);
        if ($firstChar == '+') {
            $phoneNumber = substr($phoneNumber, 1);
            $length = 11;
        }
        if (strlen($phoneNumber) != $length) {
            return false;
        }
        if (!is_numeric($phoneNumber)) {
            return false;
        }
        return true;
    }
    public function setPhoneNumber($phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
    public function getPhoneNumber(): mixed
    {
        return $this->phoneNumber;
    }
    public function setCreationDate($creationDate): void
    {
        $this->creationDate = $creationDate;
    }
    public function getCreationDate(): string
    {
        return $this->creationDate;
    }
    public function setFiletype($filetype): void
    {
        $this->filetype = $filetype;
    }
    public function getFiletype(): string
    {
        return $this->filetype;
    }
    protected function pullDate($date): string
    {
        if (empty($date)) {
            $result = '';
        } else {
            $date = explode('-', substr($date, 0, 10));
            $d = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
            $result = date('Y-m-d', $d);
        }
        return $result;
    }
    abstract protected function check(&$task);
    abstract protected function processTask(&$task, $requiredPhoneNumber);
    abstract protected function convertToArray();
}
