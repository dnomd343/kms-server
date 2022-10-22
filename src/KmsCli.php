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

function showHelpCli(string $host, int $port): void { // show help message in shell
    $kmsServer = $host;
    if (isIPv6($host)) { // host without ipv6 bracket
        $kmsServer = '[' . $host . ']';
    }
    if ($port != 1688) {
        $kmsServer = $kmsServer . ':' . $port; // add kms server port
    }
    $length = strlen($kmsServer);
    echo "\n" . genStr(floor(($length - 2) / 2)) . "Activation Command\n";
    echo "┏" . genStr($length + 14, '-') . "┓\n";
    echo "| slmgr /upk" . genStr($length + 3) . "|\n";
    echo "| slmgr /ipk KMS_KEY" . genStr($length - 5) . "|\n";
    echo "| slmgr /skms $kmsServer |\n";
    echo "| slmgr /ato" . genStr($length + 3) . "|\n";
    echo "| slmgr /dlv" . genStr($length + 3) . "|\n";
    echo "┗" . genStr($length + 14, '-') . "┛\n\n";
    echo "Office -> http://$host/office\n\n";
    echo "KMS_KEY -> http://$host/win\n";
    echo "        -> http://$host/win-server\n\n";
    echo "KMS_KEY(GBK) -> http://$host/win/gbk\n";
    echo "             -> http://$host/win-server/gbk\n\n";
}

function showOfficeCli(string $host, int $port): void { // show office commands in shell
    if (isIPv6($host)) { // host without ipv6 bracket
        $host = '[' . $host . ']';
    }
    $lenLeft = $lenRight = 0;
    $ospp = osppCommand($host, $port);
    foreach (officeInfo() as $version => $officeInfo) {
        echo "\n" . genStr(34) . "Office Professional Plus $version VL Activation Command\n";
        echo genStr(120, '-') . "\n";
        echo officeCommand($officeInfo[0], $officeInfo[1], $host, $port);
        echo genStr(120, '-') . "\n";
    }
    foreach ($ospp as $cmd => $desc) {
        $lenLeft = ($lenLeft < strlen($cmd)) ? strlen($cmd) : $lenLeft;
        $lenRight = ($lenRight < strlen($desc[0])) ? strlen($desc[0]) : $lenRight;
    }
    $header = 'Common activation commands';
    echo "\n" . genStr(floor(($lenLeft + $lenRight - strlen($header) + 24) / 2)) . $header;
    echo "\n┏" . genStr($lenLeft + $lenRight + 22, '-') . "┓\n";
    foreach ($ospp as $cmd => $desc) {
        echo "| cscript ospp.vbs $cmd" . genStr($lenLeft - strlen($cmd)) . ' | ';
        echo $desc[0] . genStr($lenRight - strlen($desc[0])) . " |\n";
    }
    echo '┗' . genStr($lenLeft + $lenRight + 22, '-') . "┛\n\n";
    echo "These commands are only applicable to the VL version of Office.\n";
    echo "If it is a Retail version, please convert it to Volume first.\n\n";
}
