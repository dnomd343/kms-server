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
$gbk = false;
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

if ($url == '/win/gbk') {
    if ($_GET['cli'] == 'true') {
        $gbk = true;
        showWinKeys();
    } else {
        header('HTTP/1.1 302 Moved Temporarily');
        header('Location: /win');
    }
}
if ($url == '/win-server/gbk') {
    if ($_GET['cli'] == 'true') {
        $gbk = true;
        showWinServerKeys();
    } else {
        header('HTTP/1.1 302 Moved Temporarily');
        header('Location: /win-server');
    }
}

?>