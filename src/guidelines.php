<?php
//I created guidelines based on the task description from the pdf
//Yes, this task can be done in a minimalist way :)
require_once __DIRAPP__ . 'classes/Overview.php';
require_once __DIRAPP__ . 'classes/CrashReport.php';
require_once __DIRAPP__ . 'classes/Classifier.php';

$overview = new Overview();
$crashReport = new CrashReport();
$classifier = new Classifier();

$overview->setFiletype('json');
$crashReport->setFiletype('json');

$classifier->addEntity($overview);
$classifier->addEntity($crashReport);

$guidelines = array(
    'input' => array(
        'filetype' => 'json',
        'fields' => array('number', 'description', 'dueDate', 'phone')
    ),
    'output' => array(
        'classifier' => $classifier,
        'results' => array(
            $overview->getType() => $overview,
            $crashReport->getType() => $crashReport
        )
    ),
    'detectDuplicate' => array(
        'mode' => true,
        'by' => 'description'
    )
);
