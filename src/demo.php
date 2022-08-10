<?php

require_once 'Daemon.php';
require_once 'Logger.php';
require_once 'Process.php';

$nginx = array(
    'name' => 'nginx',
    'command' => ['/usr/sbin/nginx'],
    'pidFile' => '/run/nginx/nginx.pid',
);

$phpFpm = array(
    'name' => 'php-fpm8',
    'command' => ['/usr/sbin/php-fpm8'],
    'pidFile' => '/run/php-fpm8.pid',
);

$vlmcsd = array(
    'name' => 'vlmcsd',
    'command' => ['/usr/bin/vlmcsd', '-e', '-p', '/run/vlmcsd.pid'],
    'pidFile' => '/run/vlmcsd.pid',
);

declare(ticks = 1);
pcntl_signal(SIGCHLD, 'subExit'); // receive SIGCHLD signal

new Process($nginx['command']);
new Process($phpFpm['command']);
new Process($vlmcsd['command']);
while (true) {
    msSleep(3000); // sleep 3s
    daemon($nginx);
    daemon($phpFpm);
    daemon($vlmcsd);
}
