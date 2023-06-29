<?php

$GVLK_ASSET = '../assets/gvlk.json';
$OSPP_ASSET = '../assets/ospp.json';

function isIPv4(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
}

function isIPv6(string $ip): bool {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
}

function getLang(): string { // kms-server working language
    // load from env
    return 'zh-cn';
}

function stringLen(string $str): int { // string length (chinese -> 2)
    return (strlen($str) + mb_strlen($str)) / 2; // chinese character occupy 3-bytes in utf-8
}

function stringGen(int $length, string $fillStr = ' '): string { // generate a string of specified length
    return str_pad('', $length, $fillStr);
}

function loadGvlks(bool $isWinServer = false): array { // load kms client keys from assets
    global $GVLK_ASSET;
    $gvlkData = json_decode(file_get_contents($GVLK_ASSET), true)[getLang()];
    return $isWinServer ? $gvlkData['win-server'] : $gvlkData['win'];
}

function loadOsppData(): array { // load office ospp data
    global $OSPP_ASSET;
    return json_decode(file_get_contents($OSPP_ASSET), true);
}
