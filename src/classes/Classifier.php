<?php

class Classifier
{
    private $entities = array();
    public function addEntity(&$entity): void
    {
        array_push($this->entities, $entity);
    }
    public function classify(&$task): void
    {
        $tests = array();
        $entities = $this->entities;
        foreach ($entities as $entity) {
            $tests[] = $entity->check($task);
        }
        $entity_win = null;
        $test_max = -1;
        foreach ($tests as $key => $test_value) {
            if ($test_max <= $test_value) {
                $test_max = $test_value;
                $entity_win = $key;
            }
        }
        $task['type'] = $entities[$entity_win]->getType();
    }
}
