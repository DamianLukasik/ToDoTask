<?php

define('ArgumentIsNotFilename', 'Argument is not a filename!');
define('FileIsNotExist', 'File is not exist!');
define('GuidelinesAreIncorrect', 'Guidelines are incorrect! %s');
define('FileTypeIsNot', 'File type is not %s!');
define('TaskNoHasNoField', 'task number %s has no field %s!');

require_once __DIRAPP__ . 'classes/Loger.php';

class MessageProcessor
{
    //I use Singleton Pattern
    private static $instance = null;
    private $filenameInput;
    private $guidelines;
    private $tasks;
    private $additionalOptions = null;
    private $summary = null;
    private $duplicates;
    private $unprocessedTasks;
    private $srcOutputResult;
    private $srcLogs;
    protected function __construct()
    {
        $this->additionalOptions = array(
            'show' => false,
            'regenerate' => false,
            'requiredPhoneNumber' => false,
            'ignoreDuplicate' => false,
            'shortSummary' => false
        );
        $this->summary = array(
            'numberDuplicates' => 0,
            'numberProcessedTasks' => 0,
            'numberProcessedTasksByType' => array(),
            'numberUnprocessedTasks' => 0
        );
        $this->duplicates = array();
        $this->unprocessedTasks = array();
        $this->srcOutputResult = __DIRAPP__ . 'results';
        $srcLogs = __DIRAPP__ . 'logs';
        $this->srcLogs = $srcLogs;
        if (!file_exists($srcLogs)) {
            $this->createCatalogOutput($srcLogs);
        }
        $this->log('MessageProcessor object created in ' . get_class($this));
    }
    protected function __clone()
    {
    }
    public static function getInstance(): MessageProcessor
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    //functions
    public function log($str): void
    {
        Loger::log($this->srcLogs, $str);
    }
    public function errorlog($str): void
    {
        Loger::log($this->srcLogs, $str, 'error');
    }
    public function setAdditionalOptions($option, $value): void
    {
        $this->additionalOptions[$option] = $value;
        $this->log("additional option '$option' is set in " . get_class($this));
    }
    public function setFilenameInput($filenameInput): void
    {
        if (!str_contains($filenameInput, '.')) {
            throw new Exception(ArgumentIsNotFilename);
        }
        if (file_exists($filenameInput)) {
            $this->filenameInput = $filenameInput;
        } else {
            throw new Exception(FileIsNotExist);
        }
        $this->log('input file name is set in ' . get_class($this));
    }
    private function loadFiletypeInput(): string
    {
        if (!isset($this->guidelines['input']['filetype'])) {
            throw new Exception(sprintf(GuidelinesAreIncorrect, 'Missing filetype in input'));
        }
        $this->log('loaded input file type in ' . get_class($this));
        return $this->guidelines['input']['filetype'];
    }
    public function checkFiletypeInput($type = null)
    {
        if (empty($type)) {
            $type = $this->loadFiletypeInput();
        }
        if (explode('.', $this->filenameInput)[1] != $type) {
            throw new Exception(sprintf(FileTypeIsNot, $type));
        }
        $this->log('checked input file type in ' . get_class($this));
    }
    public function setGuidelines($guidelines): void
    {
        if (!isset($guidelines['input']) || !isset($guidelines['output'])) {
            throw new Exception(sprintf(GuidelinesAreIncorrect, 'Missing input or output'));
        }
        if (!isset($guidelines['detectDuplicate'])) {
            $this->guidelines['detectDuplicate'] = array('mode' => false, 'by' => null);
        }
        $this->guidelines = $guidelines;
        $this->log('guidelines is set in ' . get_class($this));
    }
    public function loadTasks(): void
    {
        switch ($this->loadFiletypeInput()) {
            case 'json':
                $json = file_get_contents($this->filenameInput);
                $this->tasks = json_decode($json, true);
                $this->log('tasks have been loaded from josn file in ' . get_class($this));
                break;
            default:
                //other ways to read the file
                break;
        }
        if ($this->additionalOptions['show']) {
            print_r($this->tasks);
        }
        if ($this->guidelines['detectDuplicate']['mode']) {
            $this->removeDuplicates($this->guidelines['detectDuplicate']['by']);
        }
    }
    public function removeDuplicates($by): void
    {
        $tasks = $this->tasks;
        foreach ($tasks as $key1 => &$task1) {
            foreach ($tasks as $key2 => &$task2) {
                if ($key1 >= $key2) {
                    continue;
                }
                if (trim($task1[$by]) == trim($task2[$by])) {
                    if (!$this->additionalOptions['ignoreDuplicate']) {
                        $task2['unprocessedReason'] = 'duplicate';
                        array_push($this->unprocessedTasks, $task2);
                        $task2['duplicateNumber'] = $task1['number'];
                        array_push($this->duplicates, $task2);
                        $task2['remove'] = true;
                        $this->summary['numberDuplicates']++;
                        $this->log('deleted duplicate no. ' . $this->formatNumber($task2['number']) . ' in ' . get_class($this));
                    }
                }
            }
        }
        $newTasks = array();
        foreach ($tasks as &$task) {
            if (!isset($task['remove'])) {
                array_push($newTasks, $task);
            }
        }
        $this->tasks = $newTasks;
    }
    public function checkTasks(): void
    {
        if (!isset($this->guidelines['input']['fields'])) {
            throw new Exception(sprintf(GuidelinesAreIncorrect, 'Missing fields in input'));
        }
        $fields = $this->guidelines['input']['fields'];
        $num = 1;
        $tasks = $this->tasks;
        foreach ($tasks as $task) {
            foreach ($fields as $field) {
                if (!array_key_exists($field, $task)) {
                    throw new Exception(sprintf(TaskNoHasNoField, $num, $field));
                }
            }
            $num++;
        }
        $this->log('checked correct of task format in ' . get_class($this));
    }
    private function removeAllFilesOutput(&$filePath): void
    {
        $files = glob($filePath . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $this->log('files have been removed from the catalog ' . $filePath . ' in ' . get_class($this));
        }
    }
    private function createCatalogOutput(&$filePath): void
    {
        mkdir($filePath, 0777, true);
        chmod($filePath, 0777);
        $this->log('catalog ' . $filePath . ' have been created in ' . get_class($this));
    }
    public function processTasks(): void
    {
        if (!isset($this->guidelines['output']['classifier'])) {
            throw new Exception(sprintf(GuidelinesAreIncorrect, 'Missing classifier in output'));
        }
        $classifier = $this->guidelines['output']['classifier'];
        $this->log('classifier have been loaded in ' . get_class($this));
        if (!isset($this->guidelines['output']['results'])) {
            throw new Exception(sprintf(GuidelinesAreIncorrect, 'Missing results in output'));
        }
        $entities = $this->guidelines['output']['results'];
        $tasks = $this->tasks;
        foreach ($tasks as &$task) {
            $classifier->classify($task);
        }
        $results = array();
        foreach ($tasks as &$task) {
            foreach ($entities as $type => $entity) {
                if ($type == $task['type']) {
                    $results[$type][] = $task;
                }
            }
        }
        $this->log('tasks has been classified in ' . get_class($this));
        $filePath = $this->srcOutputResult;
        if (!file_exists($filePath)) {
            $this->createCatalogOutput($filePath);
        } else {
            if ($this->additionalOptions['regenerate']) {
                $this->removeAllFilesOutput($filePath);
            }
        }
        $dataToSave = array();
        $requiredPhoneNumber = $this->additionalOptions['requiredPhoneNumber'];
        foreach ($entities as $type => $entity) {
            $dataToSave = array();
            $this->summary['numberProcessedTasksByType'][$type] = 0;
            foreach ($results[$type] as &$sample) {
                $no = $sample['number'];
                $processed = $entity->processTask($sample, $requiredPhoneNumber);
                if (!$processed) {
                    $this->log('task no. ' . $this->formatNumber($no) . ' hasn\'t been successfully processed in ' . get_class($this));
                    array_push($this->unprocessedTasks, $sample);
                    $this->summary['numberUnprocessedTasks']++;
                    continue;
                }
                $this->log('task no. ' . $this->formatNumber($no) . ' has been successfully processed in ' . get_class($this));
                array_push($dataToSave, $sample->convertToArray());
                $this->summary['numberProcessedTasksByType'][$type]++;
                $this->summary['numberProcessedTasks']++;
            }
            $this->createFile($type, $dataToSave);
            $this->log('tasks \'' . $type . '\' have been saved in ' . get_class($this));
        }
        $dataToSave = array();
        foreach ($this->unprocessedTasks as $unprocessedTask) {
            $unprocessedTaskToSave = $unprocessedTask;
            unset($unprocessedTaskToSave['unprocessedReason']);
            if (isset($unprocessedTaskToSave['type'])) {
                unset($unprocessedTaskToSave['type']);
            }
            array_push($dataToSave, $unprocessedTaskToSave);
        }
        $this->createFile('nieprzetworzone', $dataToSave);
        $this->log('unprocessed tasks have been saved in ' . get_class($this));
    }
    public function createFile($type, $data): void
    {
        $dir = $this->srcOutputResult;
        $typeSavedFile = $this->loadFiletypeInput();
        switch ($typeSavedFile) {
            case 'json':
                file_put_contents($dir . "/$type.json", json_encode($data, JSON_UNESCAPED_UNICODE), FILE_APPEND);
                break;
            default:
                //other ways to save the file
                break;
        }
    }
    private function formatNumber($num): string
    {
        return str_pad($num, 3, ' ', STR_PAD_LEFT);
    }
    public function showResult(): void
    {
        $duplicates = $this->summary['numberDuplicates'];
        $tasks = $this->summary['numberProcessedTasks'];
        $tasksFail = $duplicates + $this->summary['numberUnprocessedTasks'];
        $TasksWithDuplicates = $tasks + $duplicates;
        $allTasks = $tasksFail + $tasks;
        $tasksByType = $this->summary['numberProcessedTasksByType'];
        echo "\n\033[32mSummary:\n";
        if (!$this->additionalOptions['shortSummary']) {
            echo "\033[39mNumber of all messages:$allTasks\n";
        }
        echo "\033[92mNumber of processed messages:$tasks\n";
        if (!$this->additionalOptions['ignoreDuplicate'] && !$this->additionalOptions['shortSummary']) {
            echo "\033[39mNumber of processed messages (with duplicates):$TasksWithDuplicates\n";
        }
        if (!$this->additionalOptions['shortSummary']) {
            echo "\033[39mNumber of unprocessed messages:$tasksFail\n";
        }
        if (!$this->additionalOptions['ignoreDuplicate'] && !$this->additionalOptions['shortSummary']) {
            echo "\033[39mNumber of duplicates:$duplicates\n";
        }
        foreach ($tasksByType as $type => $num) {
            echo "\033[92mNumber of created ($type):$num\n";
        }
        if (!$this->additionalOptions['ignoreDuplicate'] && !$this->additionalOptions['shortSummary']) {
            echo "\n\033[96mDuplicates ($duplicates):\n";
            foreach ($this->duplicates as $duplicate) {
                echo "\033[37mTask no. " . $this->formatNumber($duplicate['number']) . " is a duplicate message no. " . $this->formatNumber($duplicate['duplicateNumber']) . "\n";
            }
        }
        echo "\n\033[31mUnprocessed tasks ($tasksFail):\n";
        foreach ($this->unprocessedTasks as $unprocessedTask) {
            echo "\033[37mTask no. " . $this->formatNumber($unprocessedTask['number']) . " is unprocessed, reason: " . $unprocessedTask['unprocessedReason'] . "\n";
        }
        $this->log('summary has been shown in ' . get_class($this));
    }
}
