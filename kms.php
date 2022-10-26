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

function load_nginx_config(int $kms_port = 1688, int $http_port = 1689): void {
    $nginx_config = "server {
    listen $http_port;
    listen [::]:$http_port ipv6only=on;

    location /assets {
        root /kms-server;
    }

    location / {
        include fastcgi_params;
        fastcgi_pass unix:/run/php-fpm.sock;
        if (\$http_user_agent ~* (curl|wget)) {
            set \$cli_mode true;
        }
        fastcgi_param KMS_PORT $kms_port;
        fastcgi_param KMS_CLI \$cli_mode;
        fastcgi_param SCRIPT_FILENAME /kms-server/src/Route.php;
    }\n}\n";
    logging::debug("Nginx configure ->\n" . $nginx_config);
    $nginx_file = fopen('/etc/nginx/kms.conf', 'w');
    fwrite($nginx_file, $nginx_config); // save nginx configure file
    fclose($nginx_file);
}

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


# TODO: params load function
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
if ($KMS_PORT != 1688) {
    array_push($vlmcsd['command'], '-P', strval($KMS_PORT));
}

load_nginx_config();

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
