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

require_once 'Basis.php';

$keys = getKeys();
showKeysWeb($keys, 'Windows KMS Keys');
