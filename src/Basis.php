<?php

function genStr(int $length, string $fillStr = ' '): string { // generate a string of specified length
    return str_pad('', $length, $fillStr);
}

function lenUtf8(string $str): int { // get string length (Chinese -> 2)
    return strlen(iconv('utf-8', 'gb2312', $str));
}

function getKeys(bool $isWinServer = false): array { // get kms keys asset
    $keysAsset = json_decode(file_get_contents('../assets/kms-keys.json'), true);
    return $isWinServer ? array_reverse($keysAsset['win-server']) : $keysAsset['win'];
}
