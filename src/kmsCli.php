<?php

require_once 'Basis.php';

function showKeysCli(array $kmsKeys, bool $isGbk = false): void { // show kms keys in shell
    $ret = PHP_EOL;
    foreach ($kmsKeys as $title => $keys) {
        $length = 0;
        foreach ($keys as $caption => $key) { // found the longest caption
            $length = ($length < lenUtf8($caption)) ? lenUtf8($caption) : $length;
        }
        $ret .= genStr(floor(($length - lenUtf8($title) + 36) / 2)) . $title . PHP_EOL; // add title
        $ret .= '┏' . genStr($length + 34, '-') . '┓' . PHP_EOL;
        foreach ($keys as $caption => $key) { // add all rows
            $ret .= '| ' . $caption . genStr($length - lenUtf8($caption)) . ' | ' . $key . ' |' . PHP_EOL;
        }
        $ret .= '┗' . genStr($length + 34, '-') . '┛' . PHP_EOL . PHP_EOL;
    }
    echo $isGbk ? iconv('utf-8', 'gb2312', $ret) : $ret; // utf-8 or gbk
}

function showHelpCli(string $site): void {
    $length = strlen($site);
    echo "\n" . genStr(floor(($length - 2) / 2)) . "Activation Command\n";
    echo "┏" . genStr($length + 14, '-') . "┓\n";
    echo "| slmgr /upk" . genStr($length + 3) . "|\n";
    echo "| slmgr /ipk KMS_KEY" . genStr($length - 5) . "|\n";
    echo "| slmgr /skms $site |\n";
    echo "| slmgr /ato" . genStr($length + 3) . "|\n";
    echo "| slmgr /dlv" . genStr($length + 3) . "|\n";
    echo "┗" . genStr($length + 14, '-') . "┛\n\n";
    echo "Office -> http://$site/office\n\n";
    echo "KMS_KEY -> http://$site/win\n";
    echo "        -> http://$site/win-server\n\n";
    echo "KMS_KEY(GBK) -> http://$site/win/gbk\n";
    echo "             -> http://$site/win-server/gbk\n\n";
}
