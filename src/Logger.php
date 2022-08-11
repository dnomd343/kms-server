<?php

logging::$logLevel = logging::INFO; // default log level

$logColor = array(
    logging::DEBUG    => '37m', // white
    logging::INFO     => '36m', // light blue
    logging::WARNING  => '33m', // yellow
    logging::ERROR    => '31m', // red
    logging::CRITICAL => '35m', // purple
);

class logging {
    public const DEBUG = 0;
    public const INFO = 1;
    public const WARNING = 2;
    public const ERROR = 3;
    public const CRITICAL = 4;
    public static int $logLevel;
    private static array $logName = array(
        logging::DEBUG    => 'DEBUG',
        logging::INFO     => 'INFO',
        logging::WARNING  => 'WARNING',
        logging::ERROR    => 'ERROR',
        logging::CRITICAL => 'CRITICAL',
    );

    public static function debug($message): void {
        logging::output($message, logging::DEBUG); // debug level
    }

    public static function info($message): void {
        logging::output($message, logging::INFO); // info level
    }

    public static function warning($message): void {
        logging::output($message, logging::WARNING); // warning level
    }

    public static function error($message): void {
        logging::output($message, logging::ERROR); // error level
    }

    public static function critical($message): void {
        logging::output($message, logging::CRITICAL); // critical level
    }

    private static function output($message, $logType): void {
        global $logColor;
        if ($logType < logging::$logLevel) {
            return; // skip output
        }
        $timeStr = '[' . date('Y-m-d H:i:s', time()) . ']';
        $message = $timeStr . ' [' . logging::$logName[$logType] . '] ' . $message;
        echo "\033[" . $logColor[$logType] . $message . "\033[0m" . PHP_EOL;
    }
}
