#!/usr/bin/env php8
<?php

$VERSION = 'v1.2.3';

require_once './src/Daemon.php';
require_once './src/Logger.php';
require_once './src/Process.php';

$KMS_PORT = 1688; // kms expose port
$HTTP_PORT = 1689; // http server port
$ENABLE_HTTP = true; // http interface

$NGINX = array( // nginx process
    'name' => 'nginx',
    'command' => ['/usr/sbin/nginx'],
    'pidFile' => '/run/nginx.pid',
);

$PHP_FPM = array( // php-fpm process
    'name' => 'php-fpm8',
    'command' => ['/usr/sbin/php-fpm8'],
    'pidFile' => '/run/php-fpm8.pid',
);

$VLMCSD = array( // vlmcsd process
    'name' => 'vlmcsd',
    'command' => ['/usr/bin/vlmcsd', '-e', '-p', '/run/vlmcsd.pid'],
    'pidFile' => '/run/vlmcsd.pid',
);

function load_nginx_config(int $kms_port, int $http_port): void {
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

function load_vlmcsd_command(int $kms_port): void { // add port option for vlmcsd
    global $VLMCSD;
    if ($kms_port != 1688) { // not default kms port
        array_push($VLMCSD['command'], '-P', strval($kms_port));
    }
}

function get_param(string $field, string $default): string {
    global $argv;
    if (!in_array($field, $argv)) { // field not exist
        return $default;
    }
    $index = array_search($field, $argv) + 1;
    if ($index == sizeof($argv)) { // reach arguments end
        return $default;
    }
    return $argv[$index]; // return next argument
}

function load_params(): void {
    global $argv;
    if (strtolower(getenv('DEBUG')) === 'true' || in_array('--debug', $argv)) {
        logging::$logLevel = logging::DEBUG; // enter debug mode
    }

    global $ENABLE_HTTP;
    if (strtolower(getenv('DISABLE_HTTP')) === 'true' || in_array('--disable-http', $argv)) {
        logging::warning('Disable http service');
        $ENABLE_HTTP = false; // disable http service
    }

    global $KMS_PORT;
    if (getenv('KMS_PORT')) {
        $KMS_PORT = intval(getenv('KMS_PORT'));
        logging::debug('Get KMS_PORT from env -> ' . $KMS_PORT);
    }
    $KMS_PORT = intval(get_param('--kms-port', strval($KMS_PORT)));
    if ($KMS_PORT < 1 || $KMS_PORT > 65535) { // 1 ~ 65535
        logging::critical('Illegal KMS Port -> ' . $KMS_PORT);
        exit;
    }
    if ($KMS_PORT != 1688) { // not default kms port
        logging::warning('KMS Server Port -> ' . $KMS_PORT);
    } else {
        logging::debug('KMS Server Port -> ' . $KMS_PORT);
    }

    global $HTTP_PORT;
    if (getenv('HTTP_PORT')) {
        $HTTP_PORT = intval(getenv('HTTP_PORT'));
        logging::debug('Get HTTP_PORT from env -> ' . $HTTP_PORT);
    }
    $HTTP_PORT = intval(get_param('--http-port', strval($HTTP_PORT)));
    if ($HTTP_PORT < 1 || $HTTP_PORT > 65535) { // 1 ~ 65535
        logging::critical('Illegal HTTP Port -> ' . $HTTP_PORT);
        exit;
    }
    if ($HTTP_PORT != 1689) { // not default http port
        logging::warning('HTTP Server Port -> ' . $HTTP_PORT);
    } else {
        logging::debug('HTTP Server Port -> ' . $HTTP_PORT);
    }
}

function start_process(): void { // start sub processes
    global $ENABLE_HTTP;
    global $NGINX, $PHP_FPM, $VLMCSD;
    if ($ENABLE_HTTP) { // http server process
        new Process($NGINX['command']);
        logging::info('Start nginx server...OK');
        new Process($PHP_FPM['command']);
        logging::info('Start php-fpm server...OK');
    }
    new Process($VLMCSD['command']);
    logging::info('Start vlmcsd server...OK');
}

function exit_process(): void { // kill sub processes
    global $ENABLE_HTTP;
    global $NGINX, $PHP_FPM, $VLMCSD;
    if ($ENABLE_HTTP) {
        subExit($NGINX, $PHP_FPM, $VLMCSD); // with http service
    } else {
        subExit($VLMCSD);
    }
}

declare(ticks = 1);
pcntl_signal(SIGCHLD, function() { // receive SIGCHLD signal
    pcntl_wait($status, WNOHANG); // avoid zombie process
});

pcntl_signal(SIGTERM, function() { // receive SIGTERM signal
    logging::info('Get SIGTERM -> exit');
    exit_process();
    exit;
});

pcntl_signal(SIGINT, function() { // receive SIGINT signal
    logging::info('Get SIGINT -> exit');
    exit_process();
    exit;
});

pcntl_signal(SIGQUIT, function() { // receive SIGQUIT signal
    logging::info('Get SIGQUIT -> exit');
    exit_process();
    exit;
});

logging::info('Loading kms-server (' . $VERSION . ')');
load_params();
load_vlmcsd_command($KMS_PORT);
load_nginx_config($KMS_PORT, $HTTP_PORT);

start_process();
logging::info('Enter daemon process');
while (true) { // start daemon
    for ($i = 0; $i < 500; $i++) { // sleep 5s
        msDelay(10); // return main loop every 10ms
    }
    if ($ENABLE_HTTP) { // with http service
        daemon($NGINX);
        daemon($PHP_FPM);
    }
    daemon($VLMCSD);
}
