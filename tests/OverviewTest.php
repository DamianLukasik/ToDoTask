<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIRAPP__ . 'classes/Entity.php';
require_once __DIRAPP__ . 'classes/Overview.php';

final class OverviewTest extends TestCase
{
    public function testCreateObject(): void
    {
        $task1 = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $overview1 = new Overview($task1);
        $overview2 = new Overview($task1);
        $this->assertTrue($overview1 == $overview2);
        $task2 = array(
            'number' => 7,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '2020-03-02',
            'phone' => '505167301'
        );
        $overview3 = new Overview($task2);
        $this->assertFalse($overview1 == $overview3);
    }

    public function testSetReviewDate(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $overview = new Overview($task);
        $overview->setReviewDate($task['dueDate']);
        $this->assertTrue('2020-03-02' == $overview->getReviewDate());
        $newData = '2020-02-22 15:28:03';
        $overview->setReviewDate($newData);
        $this->assertTrue($newData != $overview->getReviewDate());
    }

    public function testCheckTask(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $overview = new Overview($task);
        $points = $overview->check($task);
        $this->assertTrue($points == 1);
        $task = array(
            'number' => 12,
            'description' => 'Awaria!',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $overview = new Overview($task);
        $points = $overview->check($task);
        $this->assertTrue($points == -1);
    }

    public function testProcessTask(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '2020-03-02 00:00:00',
            'phone' => '+48505167301'
        );
        $newTask = new Overview($task);
        $overview = new Overview($task);
        $bool = $overview->processTask($task);
        $this->assertTrue($bool);
        $newTask->setStatus('zaplanowano');
        $newTask->setWeekOfYear('10');
        $newTask->setReviewDate('2020-03-02');
        $this->assertEquals($task, $newTask);

        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '',
            'phone' => '+48505167301'
        );
        $newTask = new Overview($task);
        $overview = new Overview($task);
        $bool = $overview->processTask($task);
        $this->assertTrue($bool);
        $newTask->setStatus('nowy');
        $newTask->setReviewDate('');
        $this->assertEquals($task, $newTask);
    }

    public function testProcessTaskWithRequiredPhoneNumber(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '',
            'phone' => '+4850516301' //fail phone number
        );
        $overview = new Overview($task);
        $bool = $overview->processTask($task, true);
        $this->assertFalse($bool);
        $this->assertEquals($task['unprocessedReason'], 'incorrect phone number');
    }

    public function testConvertToArray(): void
    {
        $task = array(
            'number' => 12,
            'description' => 'Potrzebny przegląd',
            'dueDate' => '',
            'phone' => '+48505167301'
        );
        $overview = new Overview($task);
        $bool = $overview->processTask($task);
        $this->assertTrue($bool);
        $newTask = $task->convertToArray();
        $this->assertTrue($newTask['phone'] == $task->getPhoneNumber());
        $this->assertTrue($newTask['status'] == $task->getStatus());
        $this->assertTrue($newTask['description'] == $task->getDescription());
        $this->assertTrue($newTask['reviewDate'] == $task->getReviewDate());
        $this->assertTrue($newTask['type'] == $task->getType());
    }
}
