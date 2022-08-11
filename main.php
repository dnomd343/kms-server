<?php

$version = 'dev';

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

logging::info('Loading kms-server (' . $version . ')');

declare(ticks = 1);
pcntl_signal(SIGCHLD, function() { // receive SIGCHLD signal
    pcntl_wait($status, WNOHANG); // avoid zombie process
});

new Process($nginx['command']);
logging::info('Start nginx server...OK');
new Process($phpFpm['command']);
logging::info('Start php-fpm server...OK');
new Process($vlmcsd['command']);
logging::info('Start vlmcsd server...OK');

logging::info('Enter the daemon process');
while (true) {
    msSleep(5000); // sleep 5s
    daemon($nginx);
    daemon($phpFpm);
    daemon($vlmcsd);
}
