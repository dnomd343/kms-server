<?php

function showHelp() {
    global $webSite;
    $length = strlen($webSite);
    echo PHP_EOL;
    echo str_pad('', floor(($length - 2) / 2), ' ') . 'Activation Command' . PHP_EOL;
    echo '┏' . str_pad('', $length + 14, '-') . '┓' . PHP_EOL;
    echo '| slmgr /upk' . str_pad('', $length + 3, ' ') . '|' . PHP_EOL;
    echo '| slmgr /ipk {KMS_KEY}' . str_pad('', $length - 7, ' ') . '|' . PHP_EOL;
    echo '| slmgr /skms ' . $webSite . ' |' . PHP_EOL;
    echo '| slmgr /ato' . str_pad('', $length + 3, ' ') . '|' . PHP_EOL;
    echo '| slmgr /dlv' . str_pad('', $length + 3, ' ') . '|' . PHP_EOL;
    echo '┗' . str_pad('', $length + 14, '-') . '┛' . PHP_EOL;
    echo PHP_EOL;
    echo 'KMS_KEY -> http://' . $webSite . '/win' . PHP_EOL;
    echo '        -> http://' . $webSite . '/win-server' . PHP_EOL;
    echo PHP_EOL;
    echo 'KMS_KEY(GBK) -> http://' . $webSite . '/win/gbk' . PHP_EOL;
    echo '             -> http://' . $webSite . '/win-server/gbk' . PHP_EOL;
    echo PHP_EOL;
}

function webHelp() {
    global $webSite;
    echo '<!DOCTYPE html><html><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0"><title>';
    echo 'Windows Activation';
    echo '</title><link rel="stylesheet" href="./style.css" /></head>';
    echo '<body><div><h2>Windows KMS Activation</h2><pre>';
    echo '<code> slmgr /upk' . PHP_EOL;
    echo ' slmgr /ipk {KMS_KEY}' . PHP_EOL;
    echo ' slmgr /skms ' . $webSite . PHP_EOL;
    echo ' slmgr /ato' . PHP_EOL;
    echo ' slmgr /dlv</code>';
    echo '</pre><p><a href="';
    echo 'http://' . $webSite . '/win';
    echo '">KMS_KEY (Windows)</a><br><a href="';
    echo 'http://' . $webSite . '/win-server';
    echo '">KMS_KEY (Windows Server)</a></p></div></body></html>';
}

?>