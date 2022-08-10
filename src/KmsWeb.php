<?php

function showKeysHtml(array $kmsKeys, string $header): void { // show kms keys in html
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="./assets/style.css" />';
    echo "<title>$header</title></head><body><div>";
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


function showHelpHtml(string $host): void { // show help message in html
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="./assets/style.css" />';
    echo "<title>Windows Activation</title></head>\n";
    echo '<body><div><h2>Windows KMS Activation</h2><pre>';
    echo "<code> slmgr /upk\n slmgr /ipk KMS_KEY\n slmgr /skms $host\n slmgr /ato\n slmgr /dlv </code>";
    echo '</pre><p><a href="./office">KMS (Office)</a><br>';
    echo '<a href="./win">KMS_KEY (Windows)</a><br>';
    echo '<a href="./win-server">KMS_KEY (Windows Server)</a></p></div></body></html>';
}

function showOfficeHtml(string $host): void { // show office commands in html
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<link rel="stylesheet" href="./assets/style.css" />';
    echo "<title>Office KMS Server</title></head>\n<body><div>";
    foreach (officeInfo() as $version => $officeInfo) {
        echo "<h2>Office Professional Plus $version VL</h2>\n";
        echo "<pre><code>" . officeCommand($officeInfo[0], $officeInfo[1], $host) . "</code></pre>\n";
    }
    echo "<h2>常用激活命令</h2>\n";
    echo "<table><thead><tr><th>命令</th><th>说明</th></tr></thead><tbody>";
    foreach (osppCommand($host) as $cmd => $desc) {
        echo "<tr><td>cscript ospp.vbs $cmd</td>";
        echo "<td>$desc[1]</td></tr>";
    }
    echo "</tbody></table><br>\n";
    echo "<p>以上命令仅用于激活VL版本的Office，如果当前为Retail版本，请先转化为批量授权版本。</p>\n";
    echo "</div></body></html>";
}
