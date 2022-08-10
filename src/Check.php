<?php

require_once 'Process.php';

function kmsCheck(string $host, int $port = 1688): bool {
    # TODO: ipv6 host add bracket
    $vlmcs = new Process(['vlmcs', $host . ':' . $port], $capture = true);
    while($vlmcs->isAlive()); // wait vlmcs exit
    preg_match_all('/Sending activation request \(KMS V6\)/', $vlmcs->getStdout(), $match);
    return count($match[0]) != 0;
}

$host = 'kms.343.re';
echo "check $host -> ";
echo (kmsCheck($host) ? 'yes' : 'no') . PHP_EOL;
