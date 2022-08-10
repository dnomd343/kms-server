<?php

//require_once 'Logger.php';
//
//logging::debug('debug');
//logging::info('info');
//logging::warning('warning');
//logging::error('error');
//logging::critical('critical');

require_once 'Process.php';

//$vlmcsd = new Process(['/usr/bin/vlmcsd', '-De'], $capture = false);
//var_dump($vlmcsd);
//echo $vlmcsd->pid . PHP_EOL;
//
//while (true) {
//    echo "Check vlmcsd...";
//    if ($vlmcsd->isAlive()) {
//        echo "Alive\n";
//    } else {
//        echo "Death\n";
//        echo "try to restart\n";
//    }
//    sleep(1);
//}

$nginx = array(
    'command' => ['/usr/sbin/nginx'],
    'pidFile' => '/run/nginx/nginx.pid',
);

function getPid(string $pidFile): int { // get pid by given file
    if (!file_exists($pidFile)) {
        return -1; // file not exist
    }
    $file = fopen($pidFile, 'r');
    if (!is_resource($file)) {
        return -1; // file open failed
    }
    $content = fread($file, filesize($pidFile)); // read pid number
    fclose($file);
    return intval($content);
}

$p = new Process($nginx['command']);
sleep(1);
while (True) {
    if (getPid($nginx['pidFile']) == -1) {
        echo 'nginx exit' . PHP_EOL;
        $p = new Process($nginx['command']);
    }
    $p->status();
    sleep(1);
}
