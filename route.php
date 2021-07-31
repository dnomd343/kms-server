<?php

include 'kms-cli.php';
include 'kms-web.php';
include 'kms-help.php';

if (isset($_SERVER['HTTP_HOST'])) {
    $webSite = $_SERVER['HTTP_HOST'];
} else {
    $webSite = "{HOST}";
}

$url = $_SERVER['DOCUMENT_URI'];

if ($url == '/') {
    if ($_GET['cli'] == 'true') {
        showHelp();
    } else {
        webHelp();
    }
}
if ($url == '/win') {
    if ($_GET['cli'] == 'true') {
        showWinKeys();
    } else {
        webWinKeys();
    }
}
if ($url == '/win-server') {
    if ($_GET['cli'] == 'true') {
        showWinServerKeys();
    } else {
        webWinServerKeys();
    }
}


?>