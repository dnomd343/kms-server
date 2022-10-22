<?php

require_once 'Basis.php';
require_once 'Process.php';

function vlmcsCheck(string $host, int $port = 1688): bool {
    $host = str_contains($host, ':') ? "[$host]" : $host; // ipv6 add bracket
    $vlmcs = new Process(['vlmcs', $host . ':' . $port], true);
    while($vlmcs->isAlive()); // wait vlmcs exit
    preg_match_all('/Sending activation request \(KMS V6\)/', $vlmcs->getStdout(), $match);
    return count($match[0]) != 0;
}

function kmsCheckApi(): array {
    if (!isset($_GET['host'])) { // missing host param
        return array(
            'success' => false,
            'message' => 'missing host param'
        );
    }
    $host = $_GET['host'];
    if (!isIPv4($host) and !isIPv6($host) and !isDomain($host)) { // invalid host
        return array(
            'success' => false,
            'message' => 'invalid host'
        );
    }
    $port = $_GET['port'] ?? 1688;
    if ($port > 65535 or $port < 0) { // invalid port
        return array(
            'success' => false,
            'message' => 'invalid port'
        );
    }
    if (vlmcsCheck($host, $port)) { // KMS server available
        return array(
            'success' => true,
            'available' => true,
            'host' => $host,
            'port' => intval($port),
            'message' => 'kms server available'
        );
    } else { // KMS server couldn't reach
        return array(
            'success' => true,
            'available' => false,
            'host' => $host,
            'port' => intval($port),
            'message' => 'kms server connect failed'
        );
    }
}

function kmsCheckCli(string $host): void {
    $port = 1688;
    $host = v6DelBracket($host); // try to remove ipv6 bracket
    if (!isIPv4($host) and !isIPv6($host) and !isDomain($host)) { // invalid host
        preg_match_all('/(\S+):(\d+)$/', $host, $match);
        if (!count($match[1]) or !count($match[2])) { // ${HOST}:${PORT} format not found
            echo "Invalid host\n";
            exit;
        }
        $port = $match[2][0];
        $host = v6DelBracket($match[1][0]); // try to remove ipv6 bracket
        if (!isIPv4($host) and !isIPv6($host) and !isDomain($host)) { // still invalid host
            echo "Invalid host\n";
            exit;
        }
    }
    $port = intval($port);
    if (!isPort($port)) {
        echo "Invalid port\n";
        exit;
    }
    echo "KMS Server: \033[33m$host\033[0m\033[36m:$port\033[0m ->";
    echo (vlmcsCheck($host, $port) ? "\033[32m available\033[0m": "\033[31m connect failed\033[0m") . PHP_EOL;
}
