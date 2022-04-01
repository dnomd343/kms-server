<?php

// 请求方式：showWinKeys() | showWinServerKeys()

require_once 'kms-keys.php';

function show($str) { // GBK兼容
    global $gbk;
    if ($gbk) {
        return iconv('utf-8', 'gb2312', $str);
    } else {
        return $str;
    }
}

function showVersion($title, $content) { // 表格形式显示
    $length = 0;
    foreach ($content as $row) { // 获取最长的字符串长度
        $strLength = strlen(iconv('utf-8', 'gb2312', $row['name']));
        if ($length < $strLength) {
            $length = $strLength;
        }
    }
    $strLength = strlen(iconv('utf-8', 'gb2312', $title)); // 获取标题长度
    echo str_pad('', floor(($length - $strLength + 36) / 2), ' ') . show($title) . PHP_EOL; // 居中输出标题
    echo '┏' . str_pad('', $length + 34, '-') . '┓' . PHP_EOL;
    foreach ($content as $row) { // 显示表格主体
        $strLength = strlen(iconv('utf-8', 'gb2312', $row['name']));
        echo '| ' . show($row['name']) . str_pad('', $length - $strLength, ' ') . ' | ';
        echo $row['key'] . ' |' . PHP_EOL;
    }
    echo '┗' . str_pad('', $length + 34, '-') . '┛' . PHP_EOL;
}

function showKmsKeys($kmsKeys) { // 命令行显示KMS密钥
    foreach ($kmsKeys as $versionName => $versionContent) {
        echo PHP_EOL;
        showVersion($versionName, $versionContent); // 逐个显示表格
    }
}

function showWinKeys() { // 显示Windows的KMS密钥
    $kmsKeys = getKmsKeys('win');
    showKmsKeys($kmsKeys);
}

function showWinServerKeys() { // 显示Windows Server的KMS密钥
    $kmsKeys = array_reverse(getKmsKeys('win-server'));
    showKmsKeys($kmsKeys);
}

?>
