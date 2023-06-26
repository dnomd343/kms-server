<?php

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

function showCliTable(string $title, array $content): void { // print cli table with two columns
    $leftLen = 0;
    $rightLen = 0;
    foreach ($content as $col_1 => $col_2) { // found the longest length
        $leftLen = ($leftLen < stringLen($col_1)) ? stringLen($col_1) : $leftLen;
        $rightLen = ($rightLen < stringLen($col_2)) ? stringLen($col_2) : $rightLen;
    }
    $titleOffset = floor(($leftLen + $rightLen + 7 - stringLen($title)) / 2);

    echo stringGen($titleOffset) . $title . PHP_EOL; // show table title
    echo '┏' . stringGen($leftLen + $rightLen + 5, '-') . '┓' . PHP_EOL;
    foreach ($content as $col_1 => $col_2) { // show table body
        echo '| ' . $col_1 . stringGen($leftLen - stringLen($col_1)) . ' | ';
        echo $col_2 . stringGen($rightLen - stringLen($col_2)) . ' |' . PHP_EOL;
    }
    echo '┗' . stringGen($leftLen + $rightLen + 5, '-') . '┛' . PHP_EOL;
}

echo PHP_EOL;
foreach (loadGvlks(false) as $version => $content) {
    showCliTable($version, $content);
    echo PHP_EOL;
}
