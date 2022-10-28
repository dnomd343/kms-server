<?php

require_once 'Logger.php';
require_once 'Process.php';

function msDelay(int $ms): void { // delay for xxx ms
    for ($i = 0; $i < $ms; $i++) {
        usleep(1000); // split multiple times (avoid SIGCHLD signal)
    }
}

function isPid(int $pid): bool {
    $raw = explode(PHP_EOL, shell_exec('ps -ao pid')); // get pid list
    array_shift($raw); // remove output caption
    foreach ($raw as $row) {
        $row = trim($row);
        if (!$row == '' and intval($row) == $pid) { // target pid exist
            return true;
        }
    }
    return false; // pid not found
}

function getPid(string $pidFile): int { // get pid by given file
    if (!file_exists($pidFile)) { // file not exist
        logging::warning("PID file $pidFile not exist");
        return -1;
    }
    $file = fopen($pidFile, 'r');
    if (!is_resource($file)) { // file open failed
        logging::warning("Couldn't open PID file $pidFile");
        return -1;
    }
    $content = trim(fread($file, filesize($pidFile))); // read pid number
    logging::debug("Get PID from $pidFile -> $content");
    fclose($file);
    return intval($content);
}

function daemon(array $info): void {
    $pid = getPid($info['pidFile']);
    if ($pid == -1 or !isPid($pid)) { // pid not found
        logging::warning('Catch ' . $info['name'] . ' exit');
        new Process($info['command']);
        logging::info('Restart ' . $info['name'] . ' success');
    }
}

function subExit(array $nginx, array $phpFpm, array $vlmcsd): void {

    $nginxName = $nginx['name'];
    $nginxPid = getPid($nginx['pidFile']);
    logging::info("Sending kill signal to $nginxName (PID = $nginxPid)");
    posix_kill($nginxPid, SIGTERM);

    $phpFpmName = $phpFpm['name'];
    $phpFpmPid = getPid($phpFpm['pidFile']);
    logging::info("Sending kill signal to $phpFpmName (PID = $phpFpmPid)");
    posix_kill($phpFpmPid, SIGTERM);

    $vlmcsdName = $vlmcsd['name'];
    $vlmcsdPid = getPid($vlmcsd['pidFile']);
    logging::info("Sending kill signal to $vlmcsdName (PID = $vlmcsdPid)");
    posix_kill($vlmcsdPid, SIGTERM);

    logging::info('Waiting sub process exit...');
    pcntl_wait($status); // wait all process exit
    logging::info('All process exit, Goodbye!');
}
