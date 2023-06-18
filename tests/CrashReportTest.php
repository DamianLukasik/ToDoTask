<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIRAPP__ . 'classes/Entity.php';
require_once __DIRAPP__ . 'classes/CrashReport.php';

final class CrashReportTest extends TestCase
{
    public function testCreateObject(): void
    {
        $task1 = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $crashReport1 = new CrashReport($task1);
        $crashReport2 = new CrashReport($task1);
        $this->assertTrue($crashReport1 == $crashReport2);
        $task2 = array(
            'number' => 7,
            'description' => 'Awaria!',
            'dueDate' => '2020-03-02',
            'phone' => '505167301'
        );
        $crashReport3 = new CrashReport($task2);
        $this->assertFalse($crashReport1 == $crashReport3);
    }

    public function testSetReviewDate(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $crashReport = new CrashReport($task);
        $crashReport->setDueDate($task['dueDate']);
        $this->assertTrue('2020-03-02' == $crashReport->getDueDate());
        $newData = '2020-02-22 15:28:03';
        $crashReport->setDueDate($newData);
        $this->assertTrue($newData != $crashReport->getDueDate());
    }

    public function testCheckTask(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $crashReport = new CrashReport($task);
        $points = $crashReport->check($task);
        $this->assertTrue($points == 0);
    }

    public function testProcessTask(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Awaria! pilne!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $newTask = new CrashReport($task);
        $crashReport = new CrashReport($task);
        $bool = $crashReport->processTask($task);
        $this->assertTrue($bool);
        $newTask->setStatus('termin');
        $newTask->setPriority('wysoki');
        $newTask->setDueDate('2020-03-02');
        $this->assertEquals($task, $newTask);

        $task = array(
            'number' => 12,
            'description' => 'Awaria! pilne!',
            'dueDate' => '',
            'phone' => '+48505167301'
        );
        $newTask = new CrashReport($task);
        $crashReport = new CrashReport($task);
        $bool = $crashReport->processTask($task);
        $this->assertTrue($bool);
        $newTask->setStatus('nowy');
        $newTask->setPriority('wysoki');
        $newTask->setDueDate('');
        $this->assertEquals($task, $newTask);
    }

    public function testProcessTaskWithRequiredPhoneNumber(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '',
            'phone' => '+4850516301' //fail phone number
        );
        $crashReport = new CrashReport($task);
        $bool = $crashReport->processTask($task, true);
        $this->assertFalse($bool);
        $this->assertEquals($task['unprocessedReason'], 'incorrect phone number');
    }

    public function testConvertToArray(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '',
            'phone' => '+48505167301'
        );
        $crashReport = new CrashReport($task);
        $bool = $crashReport->processTask($task);
        $this->assertTrue($bool);
        $newTask = $task->convertToArray();
        $this->assertTrue($newTask['phone'] == $task->getPhoneNumber());
        $this->assertTrue($newTask['status'] == $task->getStatus());
        $this->assertTrue($newTask['description'] == $task->getDescription());
        $this->assertTrue($newTask['dueDate'] == $task->getDueDate());
        $this->assertTrue($newTask['type'] == $task->getType());
    }
}
