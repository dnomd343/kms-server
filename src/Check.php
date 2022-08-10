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

function kmsCheck(): array {
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
            'message' => 'kms server available'
        );
    } else { // KMS server couldn't reach
        return array(
            'success' => false,
            'message' => 'kms server connect failed'
        );
    }
}
