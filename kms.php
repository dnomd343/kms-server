#!/usr/bin/env php8
<?php

$VERSION = 'v1.2.2';

require_once './src/Daemon.php';
require_once './src/Logger.php';
require_once './src/Process.php';

$KMS_PORT = 1688; // kms expose port
$HTTP_PORT = 1689; // http server port

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

function get_param(string $field, string $default): string {
    if (sizeof(getopt('', [$field . ':'])) != 1) { // without target option
        return $default;
    }
    $param = getopt('', [$field . ':'])[$field]; // split target option
    if (is_array($param)) { // with multi-params
        $param = end($param); // get last one
    }
    return $param;
}

function load_params(array $args): void {
    if (in_array('--debug', $args)) { // enter debug mode
        logging::$logLevel = logging::DEBUG;
    }

    global $KMS_PORT;
    $KMS_PORT = intval(get_param('kms-port', '1688'));
    logging::warning('KMS Server Port -> ' . $KMS_PORT);

    # TODO: load http port
}

declare(ticks = 1);
pcntl_signal(SIGCHLD, function() { // receive SIGCHLD signal
    pcntl_wait($status, WNOHANG); // avoid zombie process
});
pcntl_signal(SIGTERM, function() { // receive SIGTERM signal
    global $NGINX, $PHP_FPM, $VLMCSD;
    logging::info('Get SIGTERM -> exit');
    subExit($NGINX['pidFile'], $PHP_FPM['pidFile'], $VLMCSD['pidFile']);
});
pcntl_signal(SIGINT, function() { // receive SIGINT signal
    global $NGINX, $PHP_FPM, $VLMCSD;
    logging::info('Get SIGINT -> exit');
    subExit($NGINX['pidFile'], $PHP_FPM['pidFile'], $VLMCSD['pidFile']);
});

load_params($argv);

# TODO: check port between 1 to 65535
//if ($KMS_PORT != 1688) {
//    array_push($vlmcsd['command'], '-P', strval($KMS_PORT));
//}

load_nginx_config($KMS_PORT, $HTTP_PORT);

logging::info('Loading kms-server (' . $VERSION . ')');
new Process($NGINX['command']);
logging::info('Start nginx server...OK');
new Process($PHP_FPM['command']);
logging::info('Start php-fpm server...OK');
new Process($VLMCSD['command']);
logging::info('Start vlmcsd server...OK');

logging::info('Enter daemon process');
while (true) { // start daemon
    for ($i = 0; $i < 500; $i++) { // sleep 5s
        msDelay(10); // return main loop every 10ms
    }
    daemon($NGINX);
    daemon($PHP_FPM);
    daemon($VLMCSD);
}
