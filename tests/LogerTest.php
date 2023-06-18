<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIRAPP__ . 'classes/Loger.php';

final class LogerTest extends TestCase
{
    public function testSaveLog(): void
    {
        if (!defined('__DIRAPP__')) define('__DIRAPP__', __DIRAPP__ . '');
        $log = 'test';
        Loger::log(__DIRAPP__ . 'logs', $log, 'test');
        $log = "[" . date("Y-m-d H:i:s") . "]: $log\n";
        $file = __DIRAPP__ . 'logs/test.log';
        //open file
        $fp = fopen($file, "r");
        while (($line = fgets($fp))) {
            $last_line = $line;
        }
        fclose($fp);
        //remove file
        unlink($file);
        $this->assertSame($last_line, $log);
    }
}
