<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIRAPP__ . 'classes/Loger.php';

final class MessageProcessorTest extends TestCase
{
    private $command = 'php ./ToDoTask.php';
    private $fileInput = 'recruitment-task-source.json';

    private function formatCompareString(&$rowDesire, &$rowOutput): void
    {
        $rowDesire = trim($rowDesire);
        $rowOutput = trim($rowOutput);
        //remove terminal codes from string
        $rowOutput = preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z0-9:]#', '', $rowOutput);
    }

    private function compare(&$desiredResult, &$rows)
    {
        foreach ($desiredResult as $i => $rowDesire) {
            $row = $rows[$i];
            if (empty($row)) {
                continue;
            }
            $this->formatCompareString($rowDesire, $row);
            //compare rows from desired result with rows from output
            //echo "\033[32mCompare:\n\033[37m" . $rowDesire . "\n" . $row . "\n";
            $this->assertSame($rowDesire, $row);
        }
    }

    public function testSuccessApp(): void
    {
        $desiredResult = array(
            '',
            'Summary:',
            'Number of all messages:20',
            'Number of processed messages:18',
            'Number of processed messages (with duplicates):20',
            'Number of unprocessed messages:2',
            'Number of duplicates:2',
            'Number of created (przegląd):6',
            'Number of created (zgłoszenie awarii):12',
            '',
            'Duplicates (2):',
            'Task no.  11 is a duplicate message no.   2',
            'Task no.  16 is a duplicate message no.  14',
            '',
            'Unprocessed tasks (2):',
            'Task no.  11 is unprocessed, reason: duplicate',
            'Task no.  16 is unprocessed, reason: duplicate',
            ''
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testSuccessAppShow(): void
    {
        $desiredResult = array(
            'Array',
            '(',
            '[0] => Array',
            '(',
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r -s');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testSuccessAppRequiredPhoneNumber(): void
    {
        $desiredResult = array(
            '',
            'Summary:',
            'Number of all messages:20',
            'Number of processed messages:12',
            'Number of processed messages (with duplicates):14',
            'Number of unprocessed messages:8',
            'Number of duplicates:2',
            'Number of created (przegląd):6',
            'Number of created (zgłoszenie awarii):6',
            '',
            'Duplicates (2):',
            'Task no.  11 is a duplicate message no.   2',
            'Task no.  16 is a duplicate message no.  14',
            '',
            'Unprocessed tasks (8):',
            'Task no.  11 is unprocessed, reason: duplicate',
            'Task no.  16 is unprocessed, reason: duplicate',
            'Task no.   1 is unprocessed, reason: incorrect phone number',
            'Task no.   2 is unprocessed, reason: incorrect phone number',
            'Task no.   3 is unprocessed, reason: incorrect phone number',
            'Task no.   9 is unprocessed, reason: incorrect phone number',
            'Task no.  14 is unprocessed, reason: incorrect phone number',
            'Task no.  19 is unprocessed, reason: incorrect phone number'
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r -rpn');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testSuccessAppIgnoreDuplicate(): void
    {
        $desiredResult = array(
            '',
            'Summary:',
            'Number of all messages:20',
            'Number of processed messages:20',
            'Number of unprocessed messages:0',
            'Number of created (przegląd):6',
            'Number of created (zgłoszenie awarii):14',
            '',
            'Unprocessed tasks (0):'
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r -id');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testSuccessAppIgnoreDuplicateAndRequiredPhoneNumber(): void
    {
        $desiredResult = array(
            '',
            'Summary:',
            'Number of all messages:20',
            'Number of processed messages:12',
            'Number of unprocessed messages:8',
            'Number of created (przegląd):6',
            'Number of created (zgłoszenie awarii):6',
            '',
            'Unprocessed tasks (8):',
            'Task no.   1 is unprocessed, reason: incorrect phone number',
            'Task no.   2 is unprocessed, reason: incorrect phone number',
            'Task no.   3 is unprocessed, reason: incorrect phone number',
            'Task no.   9 is unprocessed, reason: incorrect phone number',
            'Task no.  11 is unprocessed, reason: incorrect phone number',
            'Task no.  14 is unprocessed, reason: incorrect phone number',
            'Task no.  16 is unprocessed, reason: incorrect phone number',
            'Task no.  19 is unprocessed, reason: incorrect phone number'
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r -id -rpn');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testSuccessAppShortSummary(): void
    {
        $desiredResult = array(
            '',
            'Summary:',
            'Number of processed messages:18',
            'Number of created (przegląd):6',
            'Number of created (zgłoszenie awarii):12',
            '',
            'Unprocessed tasks (2):',
            'Task no.  11 is unprocessed, reason: duplicate',
            'Task no.  16 is unprocessed, reason: duplicate'
        );
        $output = shell_exec($this->command . ' ' . $this->fileInput . ' -r -sh');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testFailAppNoTypefile(): void
    {
        $desiredResult = array(
            '',
            'The task was interrupted',
            'Reason: File type is not json!'
        );
        $output = shell_exec($this->command . ' empty_file.txt -r');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testFailAppNoFilename(): void
    {
        $desiredResult = array(
            '',
            'The task was interrupted',
            'Reason: Argument is not a filename!'
        );
        $output = shell_exec($this->command . ' noexistfile -r');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }

    public function testFailAppNoExistsFile(): void
    {
        $desiredResult = array(
            '',
            'The task was interrupted',
            'Reason: File is not exist!'
        );
        $output = shell_exec($this->command . ' test.json -r');
        $rows = explode("\n", $output);
        $this->compare($desiredResult, $rows);
    }
}
