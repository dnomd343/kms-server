<?php

logging::$logLevel = logging::DEBUG;

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

    private static function output($message, $type): void {
        global $logColor;
        if ($type < logging::$logLevel) {
            return; // skip output
        }
        echo "\033[" . $logColor[$type] . $message . "\033[0m" . PHP_EOL;
    }
}
