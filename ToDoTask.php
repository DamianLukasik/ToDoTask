<?php
/*softgorillas company recruitment task

php ToDoTask recruitment-task-source.json
command use to initialize the program

alias todotask="php ToDoTask.php"
create a bash shell alias named todotask

todotask recruitment-task-source.json
command use to initialize the program

./vendor/bin/phpunit
command use to test the program

The command todotask can be run with options:
-s, --show
additional option, shows the contents of the input file

-r --regenerate
additional option, files are deleted and recreated in catalog src/results

-rpn --requiredPhoneNumber
additional option, validates phone numbers in messages, only processes tasks with a valid phone number

-id --ignoreDuplicate
additional option, does not delete duplicates

-sh --shortSummary
additional option, show a shortened version of the summary
*/
array_shift($argv);
if (isset($argv[0])) {
    //new object
    define('__DIRAPP__', 'src/');
    require_once __DIRAPP__ . 'classes/MessageProcessor.php';
    $tdTask = MessageProcessor::getInstance();
    try {
        //filename from command line
        $tdTask->setFilenameInput(array_shift($argv));
        //optional
        foreach ($argv as $arg) {
            switch ($arg) {
                case '-s':
                case '--show':
                    $tdTask->setAdditionalOptions('show', true);
                    break;
                case '-r':
                case '--regenerate':
                    $tdTask->setAdditionalOptions('regenerate', true);
                    break;
                case '-rpn':
                case '--requiredPhoneNumber':
                    $tdTask->setAdditionalOptions('requiredPhoneNumber', true);
                    break;
                case '-id':
                case '--ignoreDuplicate':
                    $tdTask->setAdditionalOptions('ignoreDuplicate', true);
                    break;
                case '-sh':
                case '--shortSummary':
                    $tdTask->setAdditionalOptions('shortSummary', true);
                    break;
                default:
                    break;
            }
        }
        //set guidelines of recruitment task
        require_once __DIRAPP__ . 'guidelines.php';
        $tdTask->setGuidelines($guidelines);
        $tdTask->checkFiletypeInput();
        $tdTask->loadTasks();
        $tdTask->checkTasks();
        $tdTask->processTasks();
        $tdTask->showResult();
        $tdTask->log('end of operation');
    } catch (Exception $e) {
        $error = "\nThe task was interrupted\nReason: " . $e->getMessage() . "\n";
        $tdTask->errorlog($error);
        echo $error;
    }
}
