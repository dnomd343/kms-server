<?php

require_once 'Basis.php';
require_once 'KmsCli.php';
require_once 'KmsWeb.php';

$kmsHost = getHost(); // kms server address
$url = $_SERVER['DOCUMENT_URI']; // request url
$isCli = ($_GET['cli'] == 'true'); // shell or web browser

$isGbk = false; // utf-8 or gbk
if ($url == '/win/gbk') {
    $url = '/win';
    $isGbk = true;
}
if ($url == '/win-server/gbk') {
    $url = '/win-server';
    $isGbk = true;
}

$isJson = false; // json output
if ($url == '/win/json') {
    $url = '/win';
    $isJson = true;
}
if ($url == '/win-server/json') {
    $url = '/win-server';
    $isJson = true;
}

if ($url == '/' or $url == '/help') {
    $isCli ? showHelpCli($kmsHost) : showHelpHtml($kmsHost); // show help message
} else if ($url == '/office') {
    $isCli ? showOfficeCli($kmsHost) : showOfficeHtml($kmsHost); // show office commands
} else if ($url == '/win' or $url == '/win-server') {
    $kmsKeys = getKeys(($url != '/win'));
    $caption = 'Windows ' . (($url == '/win') ? '' : 'Server ') . 'KMS Keys';
    if ($isJson) {
        mimeJson();
        echo json_encode($kmsKeys); // json format of kms keys
    } else {
        $isCli ? showKeysCli($kmsKeys, $isGbk) : showKeysHtml($kmsKeys, $caption); // kms keys of windows
    }
} else if ($url == '/check') {
    mimeJson();
    echo "WIP..."; // TODO: kms check
} else { // unknown request
    if ($isCli) {
        echo "Illegal Request\n";
    } else {
        mimeJson();
        echo '{"status":"error","message":"Illegal Request"}';
    }
}
