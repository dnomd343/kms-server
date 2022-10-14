#!/usr/bin/env php8
<?php

$version = 'v1.2.1';

require_once './src/Daemon.php';
require_once './src/Logger.php';
require_once './src/Process.php';

$nginx = array(
    'name' => 'nginx',
    'command' => ['/usr/sbin/nginx'],
    'pidFile' => '/run/nginx.pid',
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
pcntl_signal(SIGCHLD, function() { // receive SIGCHLD signal
    pcntl_wait($status, WNOHANG); // avoid zombie process
});
pcntl_signal(SIGTERM, function() { // receive SIGTERM signal
    global $nginx, $phpFpm, $vlmcsd;
    logging::info('Get SIGTERM -> exit');
    subExit($nginx['pidFile'], $phpFpm['pidFile'], $vlmcsd['pidFile']);
});
pcntl_signal(SIGINT, function() { // receive SIGINT signal
    global $nginx, $phpFpm, $vlmcsd;
    logging::info('Get SIGINT -> exit');
    subExit($nginx['pidFile'], $phpFpm['pidFile'], $vlmcsd['pidFile']);
});

if (in_array('--debug', $argv)) { // enter debug mode
    logging::$logLevel = logging::DEBUG;
}

logging::info('Loading kms-server (' . $version . ')');
new Process($nginx['command']);
logging::info('Start nginx server...OK');
new Process($phpFpm['command']);
logging::info('Start php-fpm server...OK');
new Process($vlmcsd['command']);
logging::info('Start vlmcsd server...OK');

logging::info('Enter daemon process');
while (true) { // start daemon
    for ($i = 0; $i < 500; $i++) { // sleep 5s
        msDelay(10); // return main loop every 10ms
    }
    daemon($nginx);
    daemon($phpFpm);
    daemon($vlmcsd);
}
