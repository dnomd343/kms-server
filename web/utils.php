<?php

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
    $assetPath = '../assets/gvlk/' . getLang() . '.json';
    $gvlkData = json_decode(file_get_contents($assetPath), true);
    return $isWinServer ? $gvlkData['win-server'] : $gvlkData['win'];
}

function loadOsppData(): array {
    $osppPath = '../assets/ospp.json';
    return json_decode(file_get_contents($osppPath), true);
}
