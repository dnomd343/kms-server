<?php

// 请求方式：webWinKeys() | webWinServerKeys()

require_once 'kms-keys.php';

function webKmsKeys($kmsKeys, $title) { // 网页显示KMS密钥
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>';
    echo $title;
    echo '</title><link rel="stylesheet" href="./style.css" /></head><body><div>';
    foreach ($kmsKeys as $versionName => $versionContent) {
        echo '<h2>' . $versionName . '</h2>';
        echo '<table><thead><tr><th>操作系统</th><th>KMS密钥</th></tr></thead><tbody>';
        foreach ($versionContent as $row) {
            echo '<tr><td>' . $row['name'] . '</td>';
            echo '<td>' . $row['key'] . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '</div></body></html>';
}

function webWinKeys() { // 网页显示Windows的KMS密钥
    $kmsKeys = getKmsKeys('win');
    webKmsKeys($kmsKeys, 'Windows KMS Keys');
}

function webWinServerKeys() { // 网页显示Windows Server的KMS密钥
    $kmsKeys = array_reverse(getKmsKeys('win-server'));
    webKmsKeys($kmsKeys, 'Windows Server KMS Keys');
}

?>