<?php

function showKeysWeb(array $kmsKeys, string $header): void { // show kms keys in html
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="./assets/style.css" /></head>';
    echo "<title>$header</title><body><div>";
    foreach ($kmsKeys as $title => $keys) {
        echo "<h2>$title</h2>";
        echo '<table><thead><tr><th>操作系统</th><th>KMS密钥</th></tr></thead><tbody>';
        foreach ($keys as $caption => $key) {
            echo "<tr><td>$caption</td><td>$key</td></tr>";
        }
        echo '</tbody></table>';
    }
    echo '</div></body></html>';
}


function showHelpWeb(string $site) {
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="./assets/style.css" /></head>';
    echo '<title>Windows Activation</title>';
    echo '<body><div><h2>Windows KMS Activation</h2><pre>';
    echo '<code> slmgr /upk\n slmgr /ipk KMS_KEY\n slmgr /skms ' . $site . '\n slmgr /ato\n slmgr /dlv </code>';
    echo '</pre><p><a href="http://' . $site . '/office">KMS (Office)</a><br>';
    echo '<a href="http://' . $site . '/win">KMS_KEY (Windows)</a><br>';
    echo '<a href="http://' . $site . '/win-server">KMS_KEY (Windows Server)</a></p></div></body></html>';
}

require_once 'Basis.php';

//$keys = getKeys();
//showKeysWeb($keys, 'Windows KMS Keys');
showHelpWeb('kms.343.re');
