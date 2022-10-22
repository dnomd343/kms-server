#!/usr/bin/env php8
<?php

$VERSION = 'v1.2.2';

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

$KMS_PORT = 1688; // kms expose port -> only in message output
if (sizeof(getopt('', ['port:'])) == 1) { // port option
    $KMS_PORT = getopt('', ['port:'])['port'];
    if (is_array($KMS_PORT)) {
        $KMS_PORT = end($KMS_PORT);
    }
}
logging::debug('KMS Server Port -> ' . $KMS_PORT);
$php_env_file = fopen('/etc/nginx/kms_params', 'w');
fwrite($php_env_file, 'fastcgi_param KMS_PORT "' . $KMS_PORT . '";' . PHP_EOL);
fclose($php_env_file);

logging::info('Loading kms-server (' . $VERSION . ')');
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
