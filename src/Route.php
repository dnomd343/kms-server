<?php

require_once 'Basis.php';
require_once 'Check.php';
require_once 'KmsCli.php';
require_once 'KmsWeb.php';

$kmsHost = getHost(); // kms server address
$kmsPort = getPort(); // kms server port
$url = $_SERVER['DOCUMENT_URI']; // request url
$isCli = (getenv('KMS_CLI') === 'true'); // shell or web browser

if ($url == '/json') { // show keys in json format
    mimeJson();
    echo json_encode(array(
        'win' => getKeys(), // win keys
        'win-server' => getKeys(true), // win-server keys
    ));
    return; // skip following process
}

$isGbk = false; // utf-8 or gbk
$isJson = false; // json output
if ($url == '/win/gbk' or $url == '/win-server/gbk') {
    $url = ($url == '/win/gbk') ? '/win' : '/win-server'; // gbk mode
    $isGbk = true;
}
if ($url == '/win/json' or $url == '/win-server/json') {
    $url = ($url == '/win/json') ? '/win' : '/win-server'; // json mode
    $isJson = true;
}

// start route process
if ($url == '/' or $url == '/help') {
    $isCli ? showHelpCli($kmsHost, $kmsPort) : showHelpHtml($kmsHost, $kmsPort); // show help message
} else if ($url == '/office') {
    $isCli ? showOfficeCli($kmsHost, $kmsPort) : showOfficeHtml($kmsHost, $kmsPort); // show office commands
} else if ($url == '/win' or $url == '/win-server') {
    $kmsKeys = getKeys(($url != '/win'));
    $caption = 'Windows ' . (($url == '/win') ? '' : 'Server ') . 'KMS Keys';
    if ($isJson) {
        mimeJson();
        echo json_encode($kmsKeys); // json format of kms keys
    } else {
        $isCli ? showKeysCli($kmsKeys, $isGbk) : showKeysHtml($kmsKeys, $caption); // kms keys of windows
    }
} else if ($url == '/check' or $url == '/check/') {
    mimeJson();
    echo json_encode(kmsCheckApi()); // check kms server
} else if (str_starts_with($url, '/check/')) {
    kmsCheckCli(substr($url, 7)); // check kms server (split `/check/`)
} else { // unknown request
    if ($isCli) {
        echo "Illegal Request\n";
    } else {
        mimeJson();
        echo '{"success":false,"message":"Illegal Request"}';
    }
}
