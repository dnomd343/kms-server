<?php

include 'kms-cli.php';
include 'kms-web.php';
include 'kms-help.php';
include 'kms-office.php';

if (isset($_SERVER['HTTP_HOST'])) { // 获取服务域名
    $webSite = $_SERVER['HTTP_HOST'];
} else {
    $webSite = "{HOST}";
}

$url = $_SERVER['DOCUMENT_URI'];
$gbk = false;

if ($url == '/' || $url == '/help') { // 操作提示
    if ($_GET['cli'] == 'true') {
        showHelp();
    } else {
        webHelp();
    }
    exit;
}

if ($url == '/office') { // office激活帮助
    if ($_GET['cli'] == 'true') {
        showOfficeHelp();
    } else {
        webOfficeHelp();
    }
    exit;
}

if ($url == '/win') { // KMS密钥获取
    if ($_GET['cli'] == 'true') {
        showWinKeys();
    } else {
        webWinKeys();
    }
    exit;
}
if ($url == '/win-server') {
    if ($_GET['cli'] == 'true') {
        showWinServerKeys();
    } else {
        webWinServerKeys();
    }
    exit;
}

if ($url == '/win/gbk') { // KMS密钥获取(GBK兼容)
    if ($_GET['cli'] == 'true') {
        $gbk = true;
        showWinKeys();
    } else {
        header('HTTP/1.1 302 Moved Temporarily');
        header('Location: /win');
    }
    exit;
}
if ($url == '/win-server/gbk') {
    if ($_GET['cli'] == 'true') {
        $gbk = true;
        showWinServerKeys();
    } else {
        header('HTTP/1.1 302 Moved Temporarily');
        header('Location: /win-server');
    }
    exit;
}

if ($url == '/json') { // JSON格式获取KMS密钥
    header('Content-Type: application/json; charset=utf-8');
    $kmsKeys = getKmsKeys('win') + getKmsKeys('win-server');
    echo json_encode($kmsKeys);
    exit;
}
if ($url == '/win/json') {
    header('Content-Type: application/json; charset=utf-8');
    $kmsKeys = getKmsKeys('win');
    echo json_encode($kmsKeys);
    exit;
}
if ($url == '/win-server/json') {
    header('Content-Type: application/json; charset=utf-8');
    $kmsKeys = getKmsKeys('win-server');
    echo json_encode($kmsKeys);
    exit;
}

if ($_GET['cli'] == 'true') { // 无效请求
    echo 'Illegal Request' . PHP_EOL;
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo '{"status":"error","message":"Illegal Request"}';
}

?>