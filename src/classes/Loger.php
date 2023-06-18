<?php

class Loger
{
    public static function log($dir, $str, $file = 'app'): void
    {
        $logs = fopen("$dir/$file.log", "a") or die("Unable to open file!");
        $str = str_replace("\n", ' ', $str);
        fwrite($logs, "[" . date("Y-m-d H:i:s") . "]: $str\n");
        fclose($logs);
    }
}
