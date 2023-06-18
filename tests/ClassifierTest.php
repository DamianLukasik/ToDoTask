<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (!defined('__DIRAPP__')) define('__DIRAPP__', 'src/');
require_once __DIRAPP__ . 'classes/Classifier.php';

final class ClassifierTest extends TestCase
{
    public function testaddEntity(): void
    {
        require_once __DIRAPP__ . 'classes/Overview.php';
        $overview = new Overview();

        $clr1 = new Classifier();
        $clr2 = new Classifier();
        $this->assertTrue($clr1 == $clr2);

        $clr2->addEntity($overview);
        $this->assertFalse($clr1 == $clr2);

        $clr1->addEntity($overview);
        $this->assertTrue($clr1 == $clr2);
    }

    public function testCheckTask(): void
    {
        require_once __DIRAPP__ . 'classes/Overview.php';
        require_once __DIRAPP__ . 'classes/CrashReport.php';
        $clr = new Classifier();
        $overview = new Overview();
        $crashReport = new CrashReport();
        $clr->addEntity($overview);
        $clr->addEntity($crashReport);
        $task1 = array('description' => 'Potrzebny przegląd');
        $clr->classify($task1);
        $this->assertTrue($task1['type'] == $overview->getType());
        $this->assertFalse($task1['type'] == $crashReport->getType());
        $task2 = array('description' => 'Pilna naprawa!');
        $clr->classify($task2);
        $this->assertFalse($task2['type'] == $overview->getType());
        $this->assertTrue($task2['type'] == $crashReport->getType());
    }
}
